<?php

declare(strict_types=1);

namespace Akki\SyliusSlimpayPlugin\Form\Type;

use Sylius\Bundle\PaymentBundle\Attribute\AsGatewayConfigurationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Memes cles de configuration que le greffon Payum, et meme nom de passerelle : les passerelles
 * deja en base restent lisibles telles quelles. La bascule d'un rail a l'autre se fait par
 * `use_payum', pas par un renommage de factory.
 *
 * Priorite a 1 parce que le greffon Payum declare lui aussi le type `slimpay' tant qu'il est
 * installe : sans elle, lequel des deux formulaires alimente le registre depend de l'ordre de
 * decouverte des services. Les deux declarent les memes champs, mais autant que ce soit decide.
 */
#[AsGatewayConfigurationType(type: 'slimpay', label: 'akki.slimpay.gateway_label', priority: 1)]
final class SlimpayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('app_id', TextType::class, [
                'label' => 'akki.slimpay.app_id',
                'constraints' => [
                    new NotBlank(['message' => 'akki.slimpay.app_id.not_blank', 'groups' => ['sylius']]),
                ],
            ])
            ->add('app_secret', TextType::class, [
                'label' => 'akki.slimpay.app_secret',
                'constraints' => [
                    new NotBlank(['message' => 'akki.slimpay.app_secret.not_blank', 'groups' => ['sylius']]),
                ],
            ])
            ->add('creditor_reference', TextType::class, [
                'label' => 'akki.slimpay.creditor_reference',
                'constraints' => [
                    new NotBlank(['message' => 'akki.slimpay.creditor_reference.not_blank', 'groups' => ['sylius']]),
                ],
            ])
            ->add('sandbox', ChoiceType::class, [
                'label' => 'akki.slimpay.sandbox',
                'choices' => [
                    'akki.slimpay.no' => false,
                    'akki.slimpay.yes' => true,
                ],
            ])
            ->add('default_checkout_mode', TextType::class, [
                'label' => 'akki.slimpay.default_checkout_mode',
                'constraints' => [
                    new NotBlank(['message' => 'akki.slimpay.default_checkout_mode.not_blank', 'groups' => ['sylius']]),
                ],
            ])
        ;
    }
}
