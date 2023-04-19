<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\PaymentMethod\PaymentMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => PaymentMethod::METHODS,
            'choice_label' => static fn (string $method) => 'payment_method.method.'.$method,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
