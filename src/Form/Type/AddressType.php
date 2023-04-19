<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Coords;
use Lens\Bundle\LensApiBundle\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', AddressTypeType::class);

        $builder->add('streetName', TextType::class);

        $builder->add('streetNumber', NumberType::class);

        $builder->add('addition', TextType::class, [
            'required' => false,
        ]);

        $builder->add('zipCode', TextType::class);

        $builder->add('city', TextType::class);

        $builder->add('country', CountryType::class);

        $builder->add('longitude', TextType::class, [
            'required' => false,
            'attr' => [
                'inputmode' => 'numeric',
                'step' => '0.000001',
                'min' => Coords::LONGITUDE_MIN,
                'max' => Coords::LONGITUDE_MAX,
            ],
        ]);

        $builder->add('latitude', TextType::class, [
            'required' => false,
            'attr' => [
                'inputmode' => 'numeric',
                'step' => '0.000001',
                'min' => Coords::LONGITUDE_MIN,
                'max' => Coords::LONGITUDE_MAX,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'root' => Address::class,
            'parent' => null,
        ]);
    }
}
