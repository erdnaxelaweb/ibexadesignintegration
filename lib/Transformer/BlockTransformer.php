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

use ErdnaxelaWeb\IbexaDesignIntegration\Value\Block;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\BlockAttributesCollection;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Exception\DefinitionNotFoundException;
use ErdnaxelaWeb\StaticFakeDesign\Exception\DefinitionTypeNotFoundException;
use ErdnaxelaWeb\StaticFakeDesign\Value\LazyValue;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;
use Ibexa\FieldTypePage\FieldType\Page\Block\Definition\BlockDefinitionFactoryInterface;
use Symfony\Component\VarExporter\Instantiator;

class BlockTransformer
{
    public function __construct(
        protected DefinitionManager $definitionManager,
        protected BlockDefinitionFactoryInterface $blockDefinitionFactory,
        protected BlockAttributeValueTransformer $blockAttributeValueTransformer
    ) {
    }

    /**
     * @param array<string, mixed>                                                                 $aditionalProperties
     */
    public function __invoke(BlockValue $blockValue, array $aditionalProperties = []): Block
    {
        $ibexaBlockDefinition = $this->blockDefinitionFactory->getBlockDefinition($blockValue->getType());
        try {
            $blockDefinition = $this->definitionManager->getDefinition(BlockDefinition::class, $blockValue->getType());
            $blockAttributesDefinitions = $blockDefinition->getAttributes();
        } catch (DefinitionNotFoundException|DefinitionTypeNotFoundException $e) {
            $blockAttributesDefinitions = [];
        }

        $blockAttributes = new BlockAttributesCollection();
        foreach ($blockAttributesDefinitions as $attributeIdentifier => $blockAttributeDefinition) {
            $blockAttributes->set(
                $attributeIdentifier,
                new LazyValue(
                    function () use (
                        $blockAttributeDefinition,
                        $attributeIdentifier,
                        $ibexaBlockDefinition,
                        $blockValue
                    ) {
                        return $this->blockAttributeValueTransformer->transform(
                            $blockValue,
                            $ibexaBlockDefinition,
                            $attributeIdentifier,
                            $blockAttributeDefinition
                        );
                    }
                )
            );
        }

        $properties = [
            'id' => (int) $blockValue->getId(),
            'name' => $blockValue->getName(),
            'type' => $blockValue->getType(),
            'view' => $blockValue->getView(),
            'class' => $blockValue->getClass(),
            'style' => $blockValue->getCompiled() ?? $blockValue->getStyle(),
            'since' => $blockValue->getSince(),
            'till' => $blockValue->getTill(),
            'innerValue' => $blockValue,
            'attributes' => $blockAttributes,
        ] + $aditionalProperties + [
            'isVisible' => true,
        ];

        return Instantiator::instantiate(Block::class, $properties);
    }
}
