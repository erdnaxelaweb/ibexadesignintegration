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

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use ErdnaxelaWeb\StaticFakeDesign\Value\TextFieldValue;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class TextFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function support(string $ibexaFieldTypeIdentifier): bool
    {
        return $ibexaFieldTypeIdentifier === 'eztext';
    }

    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        FieldDefinition        $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): ?TextFieldValue {
        /** @var \Ibexa\Core\FieldType\TextBlock\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);

        return $fieldValue->text !== "" ? new TextFieldValue($fieldValue->text) : null;
    }
}
