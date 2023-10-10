<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
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
        FieldDefinition $fieldDefinition
    ) {
        /** @var \Ibexa\Core\FieldType\MapLocation\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        return new Coordinates($fieldValue->latitude, $fieldValue->longitude, $fieldValue->address);
    }
}
