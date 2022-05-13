<?php

namespace Lens\Bundle\LensApiBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExclusionExtension extends AbstractTypeExtension
{
    public function __construct(
        private bool $enabled = false,
    ) {
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (!$this->enabled || !$form->isRoot()) {
            return;
        }

        $exclusions = $form
            ->getRoot()
            ->getConfig()
            ->getOption('exclude');

        if (empty($exclusions)) {
            return;
        }

        $exclusions = (array)$exclusions;
        foreach ($exclusions as $exclusion) {
            $exclusion = explode('.', $exclusion);

            $this->recursiveExclusion($view, $form, $exclusion);
        }
    }

    private function recursiveExclusion(
        FormView $view,
        FormInterface $form,
        array $exclusion
    ): void {
        if ($this->isCollection($form)) {
            foreach ($view as $index => $childView) {
                $this->recursiveExclusion(
                    $childView,
                    $form[$index],
                    $exclusion
                );
            }

            return;
        }

        $current = array_shift($exclusion);

        if (!isset($view[$current])) {
            return;
        }

        if (empty($exclusion)) {
            unset($view[$current]);

            return;
        }

        $this->recursiveExclusion($view[$current], $form[$current], $exclusion);
    }

    private function isCollection(FormInterface $form): bool
    {
        return $form
            ->getConfig()
            ->getType()
            ->getInnerType() instanceof CollectionType;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        if (!$this->enabled) {
            return;
        }

        $resolver->setDefault('exclude', []);
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
