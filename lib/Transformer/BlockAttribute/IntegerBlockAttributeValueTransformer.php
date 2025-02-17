<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute;

use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class IntegerBlockAttributeValueTransformer implements BlockAttributeValueTransformerInterface
{
    public function transformAttributeValue(
        BlockValue $blockValue,
        string $attributeIdentifier,
        BlockDefinition $blockDefinition,
        array $attributeConfiguration
    ) {
        $attributeValue = $blockValue->getAttribute($attributeIdentifier)
            ->getValue();
        return intval($attributeValue);
    }
}
