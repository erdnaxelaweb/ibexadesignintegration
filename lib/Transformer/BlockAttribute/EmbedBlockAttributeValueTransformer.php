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

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockAttributeDefinition;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class EmbedBlockAttributeValueTransformer extends AbstractBlockAttributeValueTransformer
{
    public function __construct(
        protected ContentTransformer $contentTransformer
    ) {
    }

    public function support(string $ibexaBlockAttributeTypeIdentifier): bool
    {
        return $ibexaBlockAttributeTypeIdentifier === 'embed';
    }

    protected function transformAttributeValue(
        BlockValue               $blockValue,
        string                   $attributeIdentifier,
        BlockDefinition          $ibexaBlockDefinition,
        BlockAttributeDefinition $attributeDefinition
    ): ?\ErdnaxelaWeb\IbexaDesignIntegration\Value\Content {
        $attributeValue = $blockValue->getAttribute($attributeIdentifier)
            ->getValue();
        if (empty($attributeValue)) {
            return null;
        }
        return $this->contentTransformer->lazyTransformContentFromContentId($attributeValue);
    }
}
