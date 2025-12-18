<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\Personal\Advertisement;
use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvertisementChoiceType extends AbstractType
{
    private static array $advertisements;

    public function __construct(
        private readonly LensApi $lensApi,
    ) {
    }

    private function advertisements(): array
    {
        if (empty(self::$advertisements)) {
            self::$advertisements = $this->lensApi->advertisements->findAll();
        }

        return self::$advertisements;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->advertisements(),
            'choice_label' => static fn (Advertisement $advertisement) => 'advertisement.'.$advertisement->type,
            'choice_value' => static fn (Advertisement $advertisement) => $advertisement->id,
            'multiple' => true,
            'expanded' => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
