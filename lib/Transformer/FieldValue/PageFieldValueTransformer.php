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
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\FieldTypePage\Registry\LayoutDefinitionRegistry;

class PageFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected LayoutDefinitionRegistry $layoutDefinitionRegistry,
        protected BlockTransformer $blockTransformer
    ) {
    }

    public function transformFieldValue(
        Content         $content,
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
}
