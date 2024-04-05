<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute;

use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class LocationListBlockAttributeValueTransformer implements BlockAttributeValueTransformerInterface
{
    public function transformAttributeValue(
        BlockValue $blockValue,
        string $attributeIdentifier,
        BlockDefinition $blockDefinition,
        array $attributeConfiguration
    ) {
        $attributeValue = $blockValue->getAttribute($attributeIdentifier)
            ->getValue();
        if (empty($attributeValue)) {
            return [];
        }
        $locationIds = explode(',', $attributeValue);
        return array_map(function (int $locationId) {
            return [
                'locationId' => $locationId,
            ];
        }, $locationIds);
    }
}
