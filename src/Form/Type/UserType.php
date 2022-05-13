<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Data\Personal;
use Lens\Bundle\LensApiBundle\Data\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    use OriginTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('username', TextType::class);

        $builder->add('roles', UserRoleType::class);

        $builder->add('disabledAt', DateTimeCheckboxType::class);

        if (!$this->isParent($options, Personal::class)) {
            $builder->add('personal', PersonalType::class, [
                'required' => false,
                'root' => $options['root'],
                'parent' => User::class,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'root' => User::class,
            'parent' => null,
        ]);
    }
}
