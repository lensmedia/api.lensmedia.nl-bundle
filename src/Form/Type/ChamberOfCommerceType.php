<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Validator\ChamberOfCommerce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChamberOfCommerceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'pattern' => '\d{8}',
                'inputmode' => 'numeric',
            ],
            'constraints' => [
                new ChamberOfCommerce(),
            ],
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
