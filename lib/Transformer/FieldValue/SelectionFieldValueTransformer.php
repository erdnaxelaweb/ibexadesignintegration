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

use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class SelectionFieldValueTransformer implements FieldValueTransformerInterface
{
    public function transformFieldValue(
        AbstractContent $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array $fieldConfiguration
    ) {
        /** @var \Ibexa\Core\FieldType\Selection\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        return array_intersect_key($fieldDefinition->fieldSettings['options'], array_flip($fieldValue->selection));
    }
}
