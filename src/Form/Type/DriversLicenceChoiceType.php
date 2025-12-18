<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\Company\DrivingSchool\DriversLicence;
use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DriversLicenceChoiceType extends AbstractType
{
    private static array $driversLicences;

    public function __construct(
        private readonly LensApi $lensApi,
    ) {
    }

    private function driversLicences(): array
    {
        if (empty(self::$driversLicences)) {
            self::$driversLicences = $this->lensApi->driversLicences->findAll();
        }

        return self::$driversLicences;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => $this->driversLicences(),
            'choice_label' => static fn (DriversLicence $driversLicence) => 'drivers_licence.'.$driversLicence->label,
            'choice_value' => static fn (DriversLicence $driversLicence) => $driversLicence->id,
            'multiple' => true,
            'expanded' => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
