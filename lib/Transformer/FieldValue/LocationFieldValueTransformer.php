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
use ErdnaxelaWeb\StaticFakeDesign\Value\Coordinates;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;

class LocationFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function support(?string $ibexaFieldTypeIdentifier): bool
    {
        return $ibexaFieldTypeIdentifier === 'ezgmaplocation';
    }

    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        ?FieldDefinition       $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): ?Coordinates {
        /** @var \Ibexa\Core\FieldType\MapLocation\Value $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        if ($fieldValue->latitude === null || $fieldValue->longitude === null) {
            return null;
        }
        return new Coordinates($fieldValue->latitude, $fieldValue->longitude, $fieldValue->address);
    }
}
