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

use ErdnaxelaWeb\StaticFakeDesign\Value\Coordinates;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class LocationFieldValueTransformer implements FieldValueTransformerInterface
{
    public function transformFieldValue(
        Content         $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array $fieldConfiguration
    ) {
        /** @var \Ibexa\Core\FieldType\MapLocation\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);

        if ($fieldValue->latitude === null || $fieldValue->longitude === null) {
            return null;
        }

        return new Coordinates($fieldValue->latitude, $fieldValue->longitude, $fieldValue->address);
    }
}
