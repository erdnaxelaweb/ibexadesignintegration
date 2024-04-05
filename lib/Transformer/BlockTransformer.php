<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\BlockAttributesCollection;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\BlockConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\Block;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinitionFactoryInterface;

class BlockTransformer
{
    public function __construct(
        protected BlockConfigurationManager $blockConfigurationManager,
        protected BlockDefinitionFactoryInterface $blockDefinitionFactory,
        protected BlockAttributeValueTransformer $blockAttributeValueTransformer
    ) {
    }

    public function __invoke(BlockValue $blockValue)
    {
        $blockConfiguration = $this->blockConfigurationManager->getConfiguration($blockValue->getType());
        $blockDefinition = $this->blockDefinitionFactory->getBlockDefinition($blockValue->getType());
        $blockFields = new BlockAttributesCollection(
            $blockValue,
            $blockDefinition,
            $blockConfiguration['attributes'],
            $this->blockAttributeValueTransformer
        );

        return new Block(
            $blockValue->getId(),
            $blockValue->getName(),
            $blockValue->getType(),
            $blockValue->getView(),
            $blockFields
        );
    }
}
