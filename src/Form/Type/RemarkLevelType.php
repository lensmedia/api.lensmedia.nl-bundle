<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\RemarkLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemarkLevelType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $levels = array_column(RemarkLevel::cases(), 'value');

        $resolver->setDefaults([
            'choices' => array_combine($levels, $levels),
            'choice_label' => static fn (string $level) => 'remark.level.'.$level,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
