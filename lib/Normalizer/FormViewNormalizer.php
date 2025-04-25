<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Normalizer;

use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class FormViewNormalizer
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(FormInterface $form): array
    {
        $formView = $form->createView();
        return $this->normalizeFormView($formView);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeFormView(FormView $formView): array
    {
        $result = [
            'id' => $formView->vars['id'] ?? null,
            'name' => $formView->vars['full_name'] ?? null,
            'label' => $formView->vars['label'] ?? null,
            'block_prefixes' => $formView->vars['block_prefixes'],
            'value' => $formView->vars['value'] ?? null,
        ];

        // Add form type specific options
        $this->addFormTypeSpecificOptions($formView, $result);

        foreach ($formView->children as $childName => $childView) {
            $result['children'][$childName] = $this->normalizeFormView($childView);
        }

        return $result;
    }

    /**
     * @param array<string, mixed>                            $result
     */
    private function addFormTypeSpecificOptions(FormView $formView, array &$result): void
    {
        // Check if the form is a ChoiceType
        if (in_array('choice', $formView->vars['block_prefixes'], true)) {
            $result['multiple'] = $formView->vars['multiple'] ?? false;
            $result['expanded'] = $formView->vars['expanded'] ?? false;

            // Add choices
            if (isset($formView->vars['choices'])) {
                $result['choices'] = $this->normalizeChoices($formView->vars['choices']);
            }

            // Add preferred choices
            if (isset($formView->vars['preferred_choices'])) {
                $result['preferred_choices'] = $this->normalizeChoices($formView->vars['preferred_choices']);
            }
        }
    }

    /**
     * @param ChoiceView[]|array<ChoiceView[]> $choices
     * @return array<string, mixed>
     */
    private function normalizeChoices(array $choices): array
    {
        $normalizedChoices = [];

        foreach ($choices as $choiceGroup => $choiceOrChoices) {
            if (is_array($choiceOrChoices)) {
                // This is a choice group
                $normalizedChoices[$choiceGroup] = $this->normalizeChoices($choiceOrChoices);
            } else {
                // This is a choice
                $normalizedChoices[] = [
                    'label' => $choiceOrChoices->label,
                    'value' => $choiceOrChoices->value,
                    'attr' => $choiceOrChoices->attr,
                ];
            }
        }

        return $normalizedChoices;
    }
}
