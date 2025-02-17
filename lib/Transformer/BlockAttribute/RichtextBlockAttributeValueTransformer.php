<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute;

use DOMDocument;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;
use Ibexa\Contracts\FieldTypeRichText\RichText\Converter as RichTextConverterInterface;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichtextValue;

class RichtextBlockAttributeValueTransformer implements BlockAttributeValueTransformerInterface
{
    public function __construct(
        protected RichTextConverterInterface $richTextOutputConverter,
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

        $xml = new DOMDocument();
        $xml->loadXML($attributeValue === null ? RichtextValue::EMPTY_VALUE : $attributeValue);

        return $this->richTextOutputConverter->convert($xml)
            ->saveHTML();
    }
}
