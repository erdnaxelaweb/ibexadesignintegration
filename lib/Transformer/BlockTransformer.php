<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\BlockAttributesCollection;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\BlockConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Exception\ConfigurationNotFoundException;
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
        $blockDefinition = $this->blockDefinitionFactory->getBlockDefinition($blockValue->getType());
        try
        {
            $blockConfiguration = $this->blockConfigurationManager->getConfiguration( $blockValue->getType() );
            $blockAttributesConfiguration = $blockConfiguration['attributes'];
        }
        catch ( ConfigurationNotFoundException $e )
        {
            $blockAttributesConfiguration = [];
        }

        $blockAttributes = new BlockAttributesCollection(
            $blockValue,
            $blockDefinition,
            $blockAttributesConfiguration,
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
