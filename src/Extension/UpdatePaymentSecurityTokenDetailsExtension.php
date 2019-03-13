<?php

declare(strict_types=1);

namespace Triotech\SyliusPayboxBundle\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Notify;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityTokenInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class UpdatePaymentSecurityTokenDetailsExtension implements ExtensionInterface
{
    /** @var RepositoryInterface */
    private $paymentRepository;
    /** @var RepositoryInterface */
    private $paymentSecurityTokenRepository;

    public function __construct(RepositoryInterface $paymentRepository, RepositoryInterface $paymentSecurityTokenRepository)
    {
        $this->paymentRepository = $paymentRepository;
        $this->paymentSecurityTokenRepository = $paymentSecurityTokenRepository;
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

        $paymentSecurityTokens = $this->paymentSecurityTokenRepository->findBy(['details' => $token->getDetails()]);
        $details = new Identity($newPayment->getId(), $token->getDetails()->getClass());

        foreach ($paymentSecurityTokens as $paymentSecurityToken) {
            $paymentSecurityToken->setDetails($details);
            $this->paymentSecurityTokenRepository->add($paymentSecurityToken);
        }

        $this->paymentRepository->add($newPayment);
    }
}
