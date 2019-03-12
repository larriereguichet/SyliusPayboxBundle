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
    const RESPONSE_FAILED_MIN = '00100';
    const RESPONSE_FAILED_MAX = '00199';
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

        if (isset($model['notify'])) {
            $request->setModel($request->getFirstModel());

            if (self::RESPONSE_SUCCESS === $model['error_code']) {
                $request->markCaptured();
            } elseif (self::RESPONSE_FAILED_MIN <= $model['error_code'] && self::RESPONSE_FAILED_MAX >= $model['error_code']) {
                $request->markFailed();
            } else {
                $request->markCanceled();
            }
        } else {
            $paymentState = $request->getFirstModel()->getState();

            switch ($paymentState) {
                case PaymentInterface::STATE_NEW:
                    $request->markNew();
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
}
