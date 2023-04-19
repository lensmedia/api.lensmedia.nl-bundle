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
        $resolver->setDefault('attr', function (OptionsResolver $resolver): void {
            $resolver->setDefaults([
                'class' => null,
                'placeholder' => null,
                'pattern' => '\d{8}',
                'inputmode' => 'numeric',
            ]);
        });

        $resolver->setDefault('constraints', [
            new ChamberOfCommerce(),
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
