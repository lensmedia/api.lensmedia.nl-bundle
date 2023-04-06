<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonalType extends AbstractType
{
    use OriginTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('initials', TextType::class);

        $builder->add('nickname', TextType::class);

        $builder->add('surnameAffix', TextType::class);

        $builder->add('surname', TextType::class);

        $builder->add('user', UserType::class, [
            'root' => $options['root'],
            'parent' => Personal::class,
        ]);

        $builder->add('contactMethods', CollectionType::class, [
            'entry_type' => ContactMethodType::class,
            'entry_options' => [
                'root' => $options['root'],
                'parent' => Personal::class,
            ],
        ]);

        $builder->add('addresses', CollectionType::class, [
            'entry_type' => AddressType::class,
            'entry_options' => [
                'root' => $options['root'],
                'parent' => Personal::class,
            ],
        ]);

        if (!$this->isParent($options, Company::class)) {
            $builder->add('companies', CollectionType::class, [
                'entry_type' => CompanyType::class,
                'entry_options' => [
                    'root' => $options['root'],
                    'parent' => Personal::class,
                ],
            ]);
        }

        $builder->add('advertisements', AdvertisementChoiceType::class);

        $builder->add('remarks', CollectionType::class, [
            'entry_type' => RemarkType::class,
            'entry_options' => [
                'root' => $options['root'],
                'parent' => Personal::class,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personal::class,
            'root' => Personal::class,
            'parent' => null,
        ]);
    }
}
