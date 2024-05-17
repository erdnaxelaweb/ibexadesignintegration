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
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\FieldTypePage\Registry\LayoutDefinitionRegistry;
use Symfony\Component\HttpFoundation\RequestStack;

class PageFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected RequestStack $requestStack,
        protected LayoutDefinitionRegistry $layoutDefinitionRegistry,
        protected BlockTransformer $blockTransformer
    ) {
    }

    public function transformFieldValue(
        AbstractContent $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array           $fieldConfiguration
    ) {
        $field = $content->getField($fieldIdentifier);
        /** @var \Ibexa\FieldTypePage\FieldType\LandingPage\Value $fieldValue */
        $fieldValue = $field->value;

        $page = $fieldValue->getPage();
        $layoutDefinition = $this->layoutDefinitionRegistry->getLayoutDefinitionById($page->getLayout());

        $zones = [];
        foreach ($page->getZones() as $zone) {
            $zones[$zone->getName()] = [
                'id' => $zone->getId(),
                'blocks' => [],
            ];
            foreach ($zone->getBlocks() as $block) {
                if (!$block->isVisible() and !$this->inEditorialMode()) {
                    continue;
                }
                $zones[$zone->getName()]['blocks'][] = ($this->blockTransformer)($block, [
                    'contentId' => $content->id,
                    'locationId' => $content->contentInfo->mainLocationId,
                    'versionNo' => $content->getVersionInfo()
->versionNo,
                    'languageCode' => $field->languageCode,
                ]);
            }
        }
        return [
            "layout" => $layoutDefinition->getTemplate(),
            "zones" => $zones,
        ];
    }

    private function inEditorialMode(): bool
    {
        $masterRequest = $this->requestStack->getMainRequest();

        return (bool)$masterRequest->attributes->get(PageController::EDITORIAL_MODE_PARAMETER, false);
    }
}
