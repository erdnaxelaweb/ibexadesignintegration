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

class SelectionFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function support(?string $ibexaFieldTypeIdentifier): bool
    {
        return in_array($ibexaFieldTypeIdentifier, ['ezselection'], true);
    }

    /**
     * @return array<string, string>
     */
    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        ?FieldDefinition       $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): array {
        /** @var \Ibexa\Core\FieldType\Selection\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        return array_intersect_key($ibexaFieldDefinition->fieldSettings['options'], array_flip($fieldValue->selection));
    }
}
