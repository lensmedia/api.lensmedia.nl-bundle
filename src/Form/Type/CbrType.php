<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Validator\Cbr;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CbrType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            fn ($value) => strtoupper($value),
            fn ($value) => strtoupper($value),
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', function (OptionsResolver $resolver) {
            $resolver->setDefaults([
                'class' => null,
                'placeholder' => null,
                'pattern' => '\d{4}[a-zA-Z]\d',
            ]);
        });

        $resolver->setDefault('constraints', [
            new Cbr(),
        ]);
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
