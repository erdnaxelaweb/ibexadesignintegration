<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\BlockAttributesCollection;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\BlockConfigurationManager;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinitionFactoryInterface;
use Symfony\Component\VarExporter\Instantiator;

class BlockTransformer
{
    public function __construct(
        protected BlockConfigurationManager $blockConfigurationManager,
        protected BlockDefinitionFactoryInterface $blockDefinitionFactory,
        protected BlockAttributeValueTransformer $blockAttributeValueTransformer
    ) {
    }

    public function __invoke(BlockValue $blockValue, array $aditionalProperties = [])
    {
        $blockConfiguration = $this->blockConfigurationManager->getConfiguration($blockValue->getType());
        $blockDefinition = $this->blockDefinitionFactory->getBlockDefinition($blockValue->getType());
        $blockAttributes = new BlockAttributesCollection(
            $blockValue,
            $blockDefinition,
            $blockConfiguration['attributes'],
            $this->blockAttributeValueTransformer
        );

        $properties = [
            'id' => (int) $blockValue->getId(),
            'name' => $blockValue->getName(),
            'type' => $blockValue->getType(),
            'view' => $blockValue->getView(),
            'attributes' => $blockAttributes,
        ] + $aditionalProperties;
        return Instantiator::instantiate(\ErdnaxelaWeb\IbexaDesignIntegration\Value\Block::class, $properties);
    }
}
