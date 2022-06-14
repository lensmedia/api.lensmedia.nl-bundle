<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Validator\Initials;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InitialsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', function (OptionsResolver $resolver) {
            $resolver->setDefaults([
                'class' => null,
                'placeholder' => null,
                'pattern' => Initials::PATTERN_HTML,
            ]);
        });
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
