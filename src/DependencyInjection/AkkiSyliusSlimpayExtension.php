<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class AkkiSyliusSlimpayExtension extends Extension implements PrependExtensionInterface
{
    /**
     * L'ecran de methode de paiement de Sylius 2 ne rend que ce qui est declare : sa section
     * « Configuration de la passerelle » appelle `{% hook 'gateway_configuration.<passerelle>' %}`.
     * Sans ce volet, les champs restent dans le formulaire sans etre affiches, et l'enregistrement
     * de la methode de paiement les remet a vide.
     */
    private const VOLET_CONFIGURATION = [
        'slimpay' => [
            'template' => '@AkkiSyliusSlimpayPlugin/admin/payment_method/form/sections/gateway_configuration/slimpay.html.twig',
            'priority' => 0,
        ],
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (false === $container->hasExtension('sylius_twig_hooks')) {
            return;
        }

        $container->prependExtensionConfig('sylius_twig_hooks', [
            'hooks' => [
                'sylius_admin.payment_method.create.content.form.sections.gateway_configuration.slimpay' => self::VOLET_CONFIGURATION,
                'sylius_admin.payment_method.update.content.form.sections.gateway_configuration.slimpay' => self::VOLET_CONFIGURATION,
            ],
        ]);
    }
}
