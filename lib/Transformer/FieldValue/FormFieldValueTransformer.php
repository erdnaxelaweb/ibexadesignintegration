<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use Closure;
use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Symfony\Component\Form\FormView;

class FormFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function support(?string $ibexaFieldTypeIdentifier): bool
    {
        return $ibexaFieldTypeIdentifier === 'ezform';
    }

    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        ?FieldDefinition       $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): Closure {
        return function ($modelData = null) use ($content, $fieldIdentifier): FormView {
            /** @var \Ibexa\FormBuilder\FieldType\Value $fieldValue */
            $fieldValue = $content->getFieldValue($fieldIdentifier);

            $form = $fieldValue->getForm();
            if ($modelData) {
                $form->get('fields')
                    ->setData($modelData);
            }
            return $form->createView();
        };
    }
}
