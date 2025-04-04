<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute;

use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockAttributeDefinition;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class CheckboxBlockAttributeValueTransformer implements BlockAttributeValueTransformerInterface
{
    public function transformAttributeValue(
        BlockValue $blockValue,
        string $attributeIdentifier,
        BlockDefinition $blockDefinition,
        BlockAttributeDefinition $attributeDefinition
    ): bool {
        $attributeValue = $blockValue->getAttribute($attributeIdentifier)
            ->getValue();
        return $attributeValue === '1';
    }
}
