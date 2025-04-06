<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Definition\BlockLayoutDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Value\Layout;
use ErdnaxelaWeb\StaticFakeDesign\Value\LayoutZone;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\MVC\Symfony\FieldType\View\ParameterProviderRegistryInterface;
use Ibexa\FieldTypePage\Registry\LayoutDefinitionRegistry;

class PageFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function __construct(
        protected LayoutDefinitionRegistry $layoutDefinitionRegistry,
        protected BlockTransformer $blockTransformer,
        protected ParameterProviderRegistryInterface $parameterProviderRegistry,
        protected DefinitionManager $definitionManager,
    ) {
    }

    public function support(string $ibexaFieldTypeIdentifier): bool
    {
        return $ibexaFieldTypeIdentifier === 'ezlandingpage';
    }

    /**
     * @return array{layout: Layout, zones: array<string, LayoutZone>, parameters: array<string, mixed>}
     */
    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        FieldDefinition        $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): array {
        $field = $content->getField($fieldIdentifier);
        /** @var \Ibexa\FieldTypePage\FieldType\LandingPage\Value $fieldValue */
        $fieldValue = $field->value;

        $page = $fieldValue->getPage();
        /** @phpstan-ignore-next-line  */
        $ibexaLayoutDefinition = $this->layoutDefinitionRegistry->getLayoutDefinitionById($page->getLayout());
        $layoutDefinition = $this->definitionManager->getDefinition(BlockLayoutDefinition::class, $page->getLayout());

        $parameters = [];
        if ($this->parameterProviderRegistry->hasParameterProvider($ibexaFieldDefinition->fieldTypeIdentifier)) {
            $parameters = $this->parameterProviderRegistry
                ->getParameterProvider($ibexaFieldDefinition->fieldTypeIdentifier)
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
            "layout" => new Layout($ibexaLayoutDefinition->getTemplate(), $zones, $layoutDefinition->getSections()),
            "zones" => $zones,
            "parameters" => $parameters,
        ];
    }
}
