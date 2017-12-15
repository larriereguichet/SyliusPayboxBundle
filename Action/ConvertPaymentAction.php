<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Sil\Bundle\SyliusPayboxBundle\Action;

use Sil\Bundle\SyliusPayboxBundle\PayboxParams;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Sylius\Component\Core\Model\PaymentInterface;

class ConvertPaymentAction implements ActionInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $order = $payment->getOrder();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details[PayboxParams::PBX_TOTAL] = $order->getTotal();
        $details[PayboxParams::PBX_DEVISE] = PayboxParams::PBX_DEVISE_EURO;
        $details[PayboxParams::PBX_CMD] = $order->getNumber();
        $details[PayboxParams::PBX_PORTEUR] = $order->getCustomer()->getEmail();
        $token = $request->getToken();
        $details[PayboxParams::PBX_EFFECTUE] = $token->getTargetUrl();
        $details[PayboxParams::PBX_ANNULE] = $token->getTargetUrl();
        $details[PayboxParams::PBX_REFUSE] = $token->getTargetUrl();
        $details[PayboxParams::PBX_TYPECARTE] = 'CB';

        if (false == isset($details[PayboxParams::PBX_REPONDRE_A]) && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken($token->getGatewayName(), $payment);
            $details[PayboxParams::PBX_REPONDRE_A] = $notifyToken->getTargetUrl();
        }

        $request->setResult((array) $details);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
