<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\ContactMethodMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactMethodMethodType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $methods = array_column(ContactMethodMethod::cases(), 'value');

        $resolver->setDefaults([
            'choices' => array_combine($methods, $methods),
            'choice_label' => static fn (string $method) => 'contact_method.method.'.$method,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
