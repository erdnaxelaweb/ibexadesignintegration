<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class FormFieldValueTransformer implements FieldValueTransformerInterface
{
    public function transformFieldValue(
        Content         $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array           $fieldConfiguration
    ) {
        return function ($modelData = null) use ($content, $fieldIdentifier, $fieldDefinition) {
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
