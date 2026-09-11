<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;

/**
 * Compose les `details' du paiement, a l'identique de ce que `SyliusConvertAction' produisait.
 *
 * Sous Payum, le resultat de la conversion devenait les `details' : c'est de la que les
 * consommateurs cote projet tirent leur matiere, et ils lisent ces cles nommement --
 * `SepaMandate::makeFromPayment()' pour `first_name', `last_name', `reference' et `bank_account',
 * `SlimpayOrderPaymentIdentifierExtractor' pour `bank_account'. Sans elles, l'export Magellan ne
 * cree pas la reference bancaire.
 *
 * `amount' vaut 0 et non le total de la commande : c'est un mandat, pas un prelevement. Le
 * montant est appele plus tard par Magellan.
 */
final readonly class PaymentDetailsProvider implements PaymentDetailsProviderInterface
{
    public function provide(PaymentInterface $payment, string $scheme, string $returnUrl): array
    {
        $order = $payment->getOrder();
        $customer = $order?->getCustomer();
        $address = $order?->getBillingAddress();

        $comment = sprintf('Order: %s', (string) $order?->getNumber());
        if (null !== $customer) {
            $comment .= sprintf(', Customer: %s', (string) $customer->getId());
        }

        return [
            'type_paiement' => 'mandat',
            'payment_reference' => $payment->getId(),
            'comment' => $comment,
            'label' => $comment,
            'payment_scheme' => $scheme,
            'amount' => 0,
            'currency' => $payment->getCurrencyCode(),
            'subscriber_reference' => $customer?->getId(),
            'email' => $customer?->getEmail(),
            'first_name' => $address?->getFirstName(),
            'last_name' => $address?->getLastName(),
            'address1' => $address?->getStreet(),
            'address2' => $address?->getCompany() ?? '',
            'city' => $address?->getCity(),
            'zip' => $address?->getPostcode(),
            'country' => $address?->getCountryCode(),
            'return_url' => $returnUrl,
        ];
    }
}
