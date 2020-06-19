<?php

/*
 * This file is part of the Blast Project package.
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Triotech\SyliusPayboxBundle\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

class StatusAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    const RESPONSE_SUCCESS = '00000';
    const RESPONSE_CANCELED = '00001';
    const RESPONSE_FAILED_CVV = '00004';
    const RESPONSE_FAILED_VALIDITY = '00008';
    const RESPONSE_FAILED_CARD_UNAUTHORIZED = '00021';
    const RESPONSE_FAILED_MIN = '00100';
    const RESPONSE_FAILED_MAX = '00199';
    const RESPONSE_PENDING_VALIDATION = '99999';
    //TODO: handle other response codes

    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $model['error_code']) {
            $request->markNew();

            return;
        }

        // Rely only on NotifyAction to update payment
        if (isset($model['notify'])) {
            $request->setModel($request->getFirstModel());

            if (self::RESPONSE_SUCCESS === $model['error_code']) {
                $request->markCaptured();
            } elseif (self::isFailureErrorCode($model['error_code'])) {
                $request->markFailed();
            } elseif (self::RESPONSE_PENDING_VALIDATION === $model['error_code']) {
                $request->markPending();
            } else {
                $request->markCanceled();
            }
        } else {
            // To make Sylius display a correct message (PayumController:afterCaptureAction)
            // And because request is in state unknown
            // Let's mark the request with the state of the payment
            // Because IPN notification will always be handled by the server before user action
            $paymentState = $request->getFirstModel()->getState();

            switch ($paymentState) {
                case PaymentInterface::STATE_NEW:
                    // Request is marked pending in case of success whereas the payment is still marked as new,
                    // meaning the IPN didn't reach the capture endpoint before the user return to the shop.
                    // (when testing locally the IPN typically won't reach the endpoint)
                    if (self::RESPONSE_SUCCESS === $model['error_code'] || self::RESPONSE_PENDING_VALIDATION === $model['error_code']) {
                        $request->markPending();
                    } elseif (self::isFailureErrorCode($model['error_code'])) {
                        $request->markFailed();
                    } elseif (self::RESPONSE_CANCELED === $model['error_code']) {
                        $request->markCanceled();
                    } else {
                        $request->markNew();
                    }
                    break;

                case PaymentInterface::STATE_COMPLETED:
                    $request->markCaptured();
                    break;

                case PaymentInterface::STATE_FAILED:
                    $request->markFailed();
                    break;

                default:
                    $request->markCanceled();
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    protected static function isFailureErrorCode($errorCode)
    {
        if (
            self::RESPONSE_FAILED_MIN <= $errorCode && self::RESPONSE_FAILED_MAX >= $errorCode ||
            $errorCode === self::RESPONSE_FAILED_CVV ||
            $errorCode === self::RESPONSE_FAILED_VALIDITY ||
            $errorCode === self::RESPONSE_FAILED_CARD_UNAUTHORIZED
        ) {
            return true;
        }

        return false;
    }
}
