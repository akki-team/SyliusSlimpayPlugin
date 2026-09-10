<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\CommandProvider;

use Akki\SyliusSlimpayPlugin\Command\StatusPaymentRequest;
use Sylius\Bundle\PaymentBundle\CommandProvider\PaymentRequestCommandProviderInterface;
use Sylius\Component\Payment\Model\PaymentRequestInterface;

final class StatusPaymentRequestCommandProvider implements PaymentRequestCommandProviderInterface
{
    public function supports(PaymentRequestInterface $paymentRequest): bool
    {
        return PaymentRequestInterface::ACTION_STATUS === $paymentRequest->getAction();
    }

    public function provide(PaymentRequestInterface $paymentRequest): object
    {
        return new StatusPaymentRequest($paymentRequest->getId());
    }
}
