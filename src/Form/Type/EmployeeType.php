<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Entity\Company\Company;
use Lens\Bundle\LensApiBundle\Entity\Company\Employee;
use Lens\Bundle\LensApiBundle\Entity\Personal\Personal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    use OriginTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('function', TextType::class);

        if (!$this->isParent($options, Personal::class)) {
            $builder->add('personal', PersonalType::class, [
                'root' => $options['root'],
                'parent' => Employee::class,
            ]);
        }

        if (!$this->isParent($options, Company::class)) {
            $builder->add('company', CompanyType::class, [
                'root' => $options['root'],
                'parent' => Employee::class,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
            'root' => Employee::class,
            'parent' => null,
        ]);
    }
}
