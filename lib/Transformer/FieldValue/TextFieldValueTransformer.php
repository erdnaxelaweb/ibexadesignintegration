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

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use ErdnaxelaWeb\StaticFakeDesign\Value\TextFieldValue;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class TextFieldValueTransformer implements FieldValueTransformerInterface
{
    public function transformFieldValue(
        AbstractContent $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): ?TextFieldValue {
        /** @var \Ibexa\Core\FieldType\TextBlock\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);

        return $fieldValue->text !== "" ? new TextFieldValue($fieldValue->text) : null;
    }
}
