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

use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockAttributeDefinition;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class GenericBlockAttributeValueTransformer extends AbstractBlockAttributeValueTransformer
{
    /**
     * @param string[] $supportedTypes
     */
    public function __construct(
        protected array $supportedTypes = [],
    ) {
    }

    public function support(string $ibexaBlockAttributeTypeIdentifier): bool
    {
        return in_array($ibexaBlockAttributeTypeIdentifier, $this->supportedTypes, true);
    }

    protected function transformAttributeValue(
        BlockValue               $blockValue,
        string                   $attributeIdentifier,
        BlockDefinition          $ibexaBlockDefinition,
        BlockAttributeDefinition $attributeDefinition
    ) {
        return $blockValue->getAttribute($attributeIdentifier)
            ->getValue();
    }
}
