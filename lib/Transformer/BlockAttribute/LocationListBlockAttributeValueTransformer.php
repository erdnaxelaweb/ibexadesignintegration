<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class LocationListBlockAttributeValueTransformer implements BlockAttributeValueTransformerInterface
{
    public function __construct(
        protected ContentTransformer $contentTransformer
    ) {
    }

    public function transformAttributeValue(
        BlockValue $blockValue,
        string $attributeIdentifier,
        BlockDefinition $blockDefinition,
        array $attributeConfiguration
    ) {
        $max = $attributeConfiguration['options']['max'];
        $attributeValue = $blockValue->getAttribute($attributeIdentifier)
            ->getValue();
        if (empty($attributeValue)) {
            return [];
        }
        $locationIds = array_map(function ($locationId) {
            return intval($locationId);
        }, explode(',', $attributeValue));

        if ($max === 1) {
            if (! empty($locationIds)) {
                return $this->contentTransformer->lazyTransformContentFromLocationId(reset($locationIds));
            }
            return null;
        }
        return array_map(function (int $locationId) {
            return $this->contentTransformer->lazyTransformContentFromLocationId($locationId);
        }, $locationIds);
    }
}
