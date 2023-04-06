<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\Company\Dealer;
use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DealerChoiceType extends AbstractType
{
    private static array $dealers;

    public function __construct(
        private LensApi $lensApi,
    ) {
    }

    private function dealers(): array
    {
        if (empty(self::$dealers)) {
            self::$dealers = $this->lensApi->dealers->list();
        }

        return self::$dealers;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->dealers(),
            'choice_label' => static fn(Dealer $dealer) =>
                'dealer.'.$dealer->name,
            'choice_value' => static fn(Dealer $dealer) =>
                $dealer->id,
            'multiple' => true,
            'expanded' => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
