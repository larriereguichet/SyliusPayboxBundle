<?php

declare(strict_types=1);

namespace Triotech\SyliusPayboxBundle\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Notify;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

final class UpdatePaymentSecurityTokenDetailsExtension implements ExtensionInterface
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function onPreExecute(Context $context): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onExecute(Context $context): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onPostExecute(Context $context): void
    {
        $previousStack = $context->getPrevious();
        $previousStackSize = count($previousStack);

        if ($previousStackSize > 1) {
            return;
        }

        if ($previousStackSize === 1) {
            $previousActionClassName = get_class($previousStack[0]->getAction());
            if (false === stripos($previousActionClassName, 'NotifyNullAction')) {
                return;
            }
        }

        /** @var Generic $request */
        $request = $context->getRequest();

        if (false === $request instanceof Generic) {
            return;
        }

        if (false === $request instanceof GetStatusInterface && false === $request instanceof Notify) {
            return;
        }

        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        if (false === $payment instanceof PaymentInterface) {
            return;
        }

        if (PaymentInterface::STATE_CANCELLED === $payment->getState()) {
            $this->updatePaymentSecurityTokenDetails($request->getToken(), $payment);
        }
    }

    /**
     * @param PaymentSecurityTokenInterface $token
     * @param PaymentInterface $payment
     */
    private function updatePaymentSecurityTokenDetails(PaymentSecurityTokenInterface $token, PaymentInterface $payment): void
    {
        $newPayment = $payment->getOrder()->getPayments()->last();
        $newPayment->setDetails(array_filter($payment->getDetails(), function ($key) {
            return strpos($key, 'PBX_') === 0;
        }, ARRAY_FILTER_USE_KEY));
        $details = new Identity($newPayment->getId(), $token->getDetails()->getClass());
        $token->setDetails($details);

        $this->em->persist($newPayment);
        $this->em->persist($token);
        $this->em->flush();
    }
}
