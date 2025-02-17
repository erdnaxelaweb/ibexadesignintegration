<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class EmbedBlockAttributeValueTransformer implements BlockAttributeValueTransformerInterface
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
        $attributeValue = $blockValue->getAttribute($attributeIdentifier)
            ->getValue();
        if (empty($attributeValue)) {
            return null;
        }
        return $this->contentTransformer->lazyTransformContentFromContentId($attributeValue);
    }
}
