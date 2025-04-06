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
use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockAttributeDefinition;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class LocationListBlockAttributeValueTransformer extends AbstractBlockAttributeValueTransformer
{
    public function __construct(
        protected ContentTransformer $contentTransformer
    ) {
    }

    public function support(string $ibexaBlockAttributeTypeIdentifier): bool
    {
        return $ibexaBlockAttributeTypeIdentifier === 'locationlist';
    }

    /**
     * @return Content|Content[]|null
     */
    protected function transformAttributeValue(
        BlockValue               $blockValue,
        string                   $attributeIdentifier,
        BlockDefinition          $ibexaBlockDefinition,
        BlockAttributeDefinition $attributeDefinition
    ): Content|array|null {
        $max = $attributeDefinition['options']['max'];
        $attributeValue = $blockValue->getAttribute($attributeIdentifier)
            ->getValue();
        if (empty($attributeValue)) {
            return [];
        }
        $locationIds = array_slice(array_map(function ($locationId) {
            return intval($locationId);
        }, explode(',', $attributeValue)), 0, $max);

        if ($max === 1) {
            if (!empty($locationIds)) {
                return $this->contentTransformer->lazyTransformContentFromLocationId(reset($locationIds));
            }
            return null;
        }
        return array_map(function (int $locationId) {
            return $this->contentTransformer->lazyTransformContentFromLocationId($locationId);
        }, $locationIds);
    }
}
