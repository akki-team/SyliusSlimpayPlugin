<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;

interface PaymentDetailsProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function provide(PaymentInterface $payment, string $scheme, string $returnUrl): array;
}
