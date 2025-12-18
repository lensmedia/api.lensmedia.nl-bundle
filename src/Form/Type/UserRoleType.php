<?php

declare(strict_types=1);

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRoleType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $roles = array_column(Role::cases(), 'value');

        $resolver->setDefaults([
            'choices' => array_combine($roles, $roles),
            'choice_label' => static fn (string $role) => 'user.role.'.mb_strtolower(mb_substr($role, mb_strrpos($role, '_') + 1)),
            'multiple' => true,
            'expanded' => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
