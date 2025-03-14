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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\BlockLayoutConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\Layout;
use ErdnaxelaWeb\StaticFakeDesign\Value\LayoutZone;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistryInterface;
use Ibexa\FieldTypePage\Registry\LayoutDefinitionRegistry;

class PageFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected LayoutDefinitionRegistry $layoutDefinitionRegistry,
        protected BlockTransformer $blockTransformer,
        protected ParameterProviderRegistryInterface $parameterProviderRegistry,
        protected BlockLayoutConfigurationManager $blockLayoutConfigurationManager,
    ) {
    }

    public function transformFieldValue(
        AbstractContent $content,
        string $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array $fieldConfiguration
    ) {
        $field = $content->getField($fieldIdentifier);
        /** @var \Ibexa\FieldTypePage\FieldType\LandingPage\Value $fieldValue */
        $fieldValue = $field->value;

        $page = $fieldValue->getPage();
        $layoutDefinition = $this->layoutDefinitionRegistry->getLayoutDefinitionById($page->getLayout());
        $layoutConfiguration = $this->blockLayoutConfigurationManager->getConfiguration($page->getLayout());

        $parameters = [];
        if ($this->parameterProviderRegistry->hasParameterProvider($fieldDefinition->fieldTypeIdentifier)) {
            $parameters = $this->parameterProviderRegistry
                ->getParameterProvider($fieldDefinition->fieldTypeIdentifier)
                ->getViewParameters($field);
        }

        $zones = [];
        foreach ($page->getZones() as $zone) {
            $blocks = [];
            foreach ($zone->getBlocks() as $block) {
                $isVisible = $block->isVisible($parameters['reference_date_time'] ?? null);
                $blocks[] = ($this->blockTransformer)($block, [
                    'contentId' => $content->id,
                    'locationId' => $content->contentInfo->mainLocationId,
                    'versionNo' => $content->getVersionInfo()
                        ->versionNo,
                    'languageCode' => $field->languageCode,
                    'isVisible' => $isVisible,
                ]);
            }
            $zones[$zone->getName()] = new LayoutZone($zone->getId(), $blocks);
        }
        return [
            "layout" => new Layout($layoutDefinition->getTemplate(), $zones, $layoutConfiguration['sections']),
            "zones" => $zones,
        ];
    }
}
