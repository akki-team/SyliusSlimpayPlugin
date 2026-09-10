<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Processor;

use Sylius\Component\Payment\Model\PaymentRequestInterface;

interface PaymentTransitionProcessorInterface
{
    public function process(PaymentRequestInterface $paymentRequest): void;
}
