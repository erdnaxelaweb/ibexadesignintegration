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

class MatrixFieldValueTransformer implements FieldValueTransformerInterface
{
    /**
     * @return array<array<mixed>>
     */
    public function transformFieldValue(
        AbstractContent $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): array {
        /** @var \Ibexa\FieldTypeMatrix\FieldType\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        $rows = [];
        /** @var \Ibexa\FieldTypeMatrix\FieldType\Value\Row $row */
        foreach ($fieldValue->getRows() as $row) {
            $rows[] = $row->getCells();
        }

        return $rows;
    }
}
