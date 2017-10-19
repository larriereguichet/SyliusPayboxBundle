<?php

namespace Librinfo\SyliusPayboxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PayboxGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site', TextType::class, [
                'label' => 'sylius.form.gateway_configuration.paybox.site',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('rang', TextType::class, [
                'label' => 'sylius.form.gateway_configuration.paybox.rank',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('identifiant', TextType::class, [
                'label' => 'sylius.form.gateway_configuration.paybox.identifier',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('hmac', TextType::class, [
                'label' => 'sylius.form.gateway_configuration.paybox.hmac',
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('sandbox', CheckboxType::class, [
                'label' => 'sylius.form.gateway_configuration.paybox.sandbox',
                'required' => false,
            ])
        ;
    }
}
