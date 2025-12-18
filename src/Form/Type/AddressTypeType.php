<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $types = array_column(AddressType::cases(), 'value');

        $resolver->setDefaults([
            'choices' => array_combine($types, $types),
            'choice_label' => static fn (string $type) => 'address.type.'.$type,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
