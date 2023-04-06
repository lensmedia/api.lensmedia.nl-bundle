<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\ContactMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactMethodMethodType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => ContactMethod::METHODS,
            'choice_label' => static fn (string $method) =>
                'contact_method.method.'.$method,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
