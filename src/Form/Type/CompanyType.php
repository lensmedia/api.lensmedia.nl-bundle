<?php

namespace Lens\Bundle\LensApiBundle\Form\Type;

use Lens\Bundle\LensApiBundle\Data\Company;
use Lens\Bundle\LensApiBundle\LensApi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    public function __construct(
        private LensApi $lensApi,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', CompanyTypeType::class);

        $builder->add('chamberOfCommerce', ChamberOfCommerceType::class, [
            'required' => false,
        ]);

        $builder->add('name', TextType::class, [
        ]);

        $builder->add('cbr', CbrType::class, [
            'required' => false,
        ]);

        $builder->add('disable', TextType::class, [
            'required' => false,
            'mapped' => false,
        ]);

        $builder->add('publishedAt', DateTimeCheckboxType::class, [
            'required' => false,
        ]);

        $builder->add('addresses', CollectionType::class, [
            'entry_type' => AddressType::class,
            'entry_options' => [
                'root' => $options['root'],
                'parent' => Company::class,
            ],
            'allow_add' => true,
            'allow_delete' => true,
        ]);

        $builder->add('contactMethods', CollectionType::class, [
            'entry_type' => ContactMethodType::class,
            'entry_options' => [
                'root' => $options['root'],
                'parent' => Company::class,
            ],
            'allow_add' => true,
            'allow_delete' => true,
        ]);

        $builder->add('dealers', DealerChoiceType::class);

        $builder->add('paymentMethods', CollectionType::class, [
            'entry_type' => PaymentMethodType::class,
            'entry_options' => [
                'root' => $options['root'],
                'parent' => Company::class,
            ],
            'allow_add' => true,
            'allow_delete' => true,
        ]);

        $builder->add('driversLicences', DriversLicenceChoiceType::class);

        $builder->add('remarks', CollectionType::class, [
            'entry_type' => RemarkType::class,
            'entry_options' => [
                'root' => $options['root'],
                'parent' => Company::class,
            ],
            'allow_add' => true,
            'allow_delete' => true,
        ]);

        $builder->add('employees', CollectionType::class, [
            'entry_type' => EmployeeType::class,
            'entry_options' => [
                'root' => $options['root'],
                'parent' => Company::class,
            ],
            'allow_add' => true,
            'allow_delete' => true,
        ]);

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (PostSubmitEvent $event) {
                $form = $event->getForm();

                /** @var Company $data */
                $data = $event->getData();

                if (null !== ($reason = $form->get('disable')->getData())) {
                    $data->disable($reason, $this->lensApi->users->auth()->id);
                } else {
                    $data->enable();
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'root' => Company::class,
            'parent' => null,
            'allow_extra_fields' => true,
        ]);
    }
}
