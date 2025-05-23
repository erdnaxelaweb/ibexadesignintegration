<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute;

use DOMDocument;
use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockAttributeDefinition;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;
use Ibexa\Contracts\FieldTypeRichText\RichText\Converter as RichTextConverterInterface;
use Ibexa\FieldTypeRichText\FieldType\RichText\Value as RichtextValue;

class RichtextBlockAttributeValueTransformer extends AbstractBlockAttributeValueTransformer
{
    public function __construct(
        protected RichTextConverterInterface $richTextOutputConverter,
    ) {
    }

    public function support(string $ibexaBlockAttributeTypeIdentifier): bool
    {
        return $ibexaBlockAttributeTypeIdentifier === 'richtext';
    }

    protected function transformAttributeValue(
        BlockValue               $blockValue,
        string                   $attributeIdentifier,
        BlockDefinition          $ibexaBlockDefinition,
        BlockAttributeDefinition $attributeDefinition
    ): bool|string {
        $attributeValue = $blockValue->getAttribute($attributeIdentifier)
            ->getValue();

        $xml = new DOMDocument();
        $xml->loadXML($attributeValue === null ? RichtextValue::EMPTY_VALUE : $attributeValue);

        return $this->richTextOutputConverter->convert($xml)
            ->saveHTML();
    }
}
