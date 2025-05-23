<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute\BlockAttributeValueTransformerInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockAttributeDefinition;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;
use InvalidArgumentException;

class BlockAttributeValueTransformer
{
    /**
     * @var array<string, BlockAttributeValueTransformerInterface[]>
     */
    protected array $blockAttributeValueTransformers = [];

    public function registerTransformer(
        string $type,
        BlockAttributeValueTransformerInterface $blockAttributeValueTransformer
    ): void {
        if (!array_key_exists($type, $this->blockAttributeValueTransformers)) {
            $this->blockAttributeValueTransformers[$type] = [];
        }
        $this->blockAttributeValueTransformers[$type][] = $blockAttributeValueTransformer;
    }

    public function getTransformer(string $blockAttributeTypeIdentifier, string $ibexaBlockAttributeTypeIdentifier): BlockAttributeValueTransformerInterface
    {
        if (!array_key_exists($blockAttributeTypeIdentifier, $this->blockAttributeValueTransformers)) {
            throw new InvalidArgumentException(sprintf('No transformer found for attribute type "%s".', $blockAttributeTypeIdentifier));
        }

        $transformers = $this->blockAttributeValueTransformers[$blockAttributeTypeIdentifier];
        foreach ($transformers as $transformer) {
            if ($transformer->support($ibexaBlockAttributeTypeIdentifier)) {
                return $transformer;
            }
        }

        throw new InvalidArgumentException(sprintf('No transformer found for ibexa attribute type "%s".', $ibexaBlockAttributeTypeIdentifier));
    }

    public function transform(
        BlockValue               $blockValue,
        BlockDefinition          $ibexaBlockDefinition,
        string                   $attributeIdentifier,
        BlockAttributeDefinition $attributeDefinition,
    ): mixed {
        $attribute = $ibexaBlockDefinition->getAttribute($attributeIdentifier);

        if ($attribute) {
            $attributeValueTransformer = $this->getTransformer(
                $attributeDefinition->getType(),
                $attribute->getType()
            );
            return ($attributeValueTransformer)(
                $blockValue,
                $attributeIdentifier,
                $ibexaBlockDefinition,
                $attributeDefinition
            );
        }
        return null;
    }
}
