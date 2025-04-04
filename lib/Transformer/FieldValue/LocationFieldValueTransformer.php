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
use ErdnaxelaWeb\StaticFakeDesign\Value\Coordinates;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class LocationFieldValueTransformer implements FieldValueTransformerInterface
{
    public function transformFieldValue(
        AbstractContent $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): Coordinates {
        /** @var \Ibexa\Core\FieldType\MapLocation\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        return new Coordinates($fieldValue->latitude, $fieldValue->longitude, $fieldValue->address);
    }
}
