<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;

interface MandateFieldsProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function provide(PaymentInterface $payment): array;

    public function provideSubscriberReference(PaymentInterface $payment): ?string;
}
