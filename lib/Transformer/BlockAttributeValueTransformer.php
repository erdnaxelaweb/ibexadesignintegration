<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute\BlockAttributeValueTransformerInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockAttributeDefinition;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\Contracts\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinition;

class BlockAttributeValueTransformer
{
    /**
     * @var \ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute\BlockAttributeValueTransformerInterface[]
     */
    protected array $blockAttributeValueTransformers = [];

    public function __construct(
        iterable $transformers
    ) {
        foreach ($transformers as $type => $blockAttributeValueTransformer) {
            $this->blockAttributeValueTransformers[$type] = $blockAttributeValueTransformer;
        }
    }

    public function transform(
        BlockValue      $blockValue,
        BlockDefinition $blockDefinition,
        string $attributeIdentifier,
        BlockAttributeDefinition $attributeDefinition,
    ): mixed {
        $attribute = $blockDefinition->getAttribute($attributeIdentifier);

        if ($attribute) {
            $transformer = $this->blockAttributeValueTransformers[$attribute->getType()];
            return $transformer->transformAttributeValue(
                $blockValue,
                $attributeIdentifier,
                $blockDefinition,
                $attributeDefinition
            );
        }
        return null;
    }
}
