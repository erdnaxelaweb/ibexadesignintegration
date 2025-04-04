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
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class SelectionFieldValueTransformer implements FieldValueTransformerInterface
{
    /**
     * @return array<string, string>
     */
    public function transformFieldValue(
        AbstractContent $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): array {
        /** @var \Ibexa\Core\FieldType\Selection\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        return array_intersect_key($fieldDefinition->fieldSettings['options'], array_flip($fieldValue->selection));
    }
}
