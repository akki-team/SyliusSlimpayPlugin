# Slimpay pour Sylius

Passerelle de paiement Slimpay pour Sylius 2, bâtie sur **PaymentRequest**.

Elle remplace `akki-team/sylius-payum-slimpay2-plugin`, qui reposait sur Payum. Les deux paquets
peuvent cohabiter : la bascule se fait passerelle par passerelle, en changeant `factory_name`.

## Installation

```bash
composer require akki-team/sylius-slimpay-plugin
```

Puis dans `config/bundles.php` :

```php
Akki\SyliusSlimpayPlugin\AkkiSyliusSlimpayPlugin::class => ['all' => true],
```

## Configuration de la passerelle

Les clés sont identiques à celles du greffon Payum, afin que les passerelles déjà enregistrées
restent lisibles : `app_id`, `app_secret`, `creditor_reference`, `sandbox`, `default_checkout_mode`.

## Ce qui est pris en charge

| Action | État |
|---|---|
| Capture — signature de mandat SEPA, alias de carte | oui, mode `redirect` |
| Statut au retour du client | oui |
| Notification | à venir |
| Remboursement | à venir |
| Mise à jour du moyen de paiement | à venir |
| Cadre intégré (`iframepopin`, `iframeembedded`) | non porté |

Le mode `iframe` n'est pas porté : le greffon Payum le proposait, mais aucune passerelle ne
l'emprunte. Le gestionnaire de capture lève une exception explicite si `default_checkout_mode`
vaut autre chose que `redirect`.

L'annulation n'existait pas non plus dans le greffon Payum, où elle levait
`LogicException('Not implemented')` : elle n'a pas été réintroduite.

## Licence

MIT.
