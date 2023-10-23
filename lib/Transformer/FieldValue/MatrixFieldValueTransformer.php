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

class MatrixFieldValueTransformer implements FieldValueTransformerInterface
{
    public function transformFieldValue(
        Content         $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array $fieldConfiguration
    ) {
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
