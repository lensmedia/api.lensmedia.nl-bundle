<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Data\Remark;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemarkLevelType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => Remark::LEVELS,
            'choice_label' => static fn (string $level) => 'remark.level.'.$level,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
