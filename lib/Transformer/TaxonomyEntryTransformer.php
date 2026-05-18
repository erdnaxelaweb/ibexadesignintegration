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

use DateTime;
use ErdnaxelaWeb\IbexaDesignIntegration\Definition\TaxonomyEntryDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\ContentFieldsCollection;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\TaxonomyEntry;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\LazyLoading\LazyValue;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Taxonomy\Value\TaxonomyEntry as IbexaTaxonomyEntry;
use Ibexa\HttpCache\Handler\TagHandler;

class TaxonomyEntryTransformer
{
    public function __construct(
        protected DefinitionManager $definitionManager,
        protected FieldValueTransformer $fieldValueTransformers,
        protected TagHandler $responseTagger
    ) {
    }

    public function __invoke(IbexaTaxonomyEntry $ibexaTaxonomyEntry): TaxonomyEntry
    {
        return $this->transformTaxonomyEntry($ibexaTaxonomyEntry);
    }

    public function transformTaxonomyEntry(IbexaTaxonomyEntry $ibexaTaxonomyEntry): TaxonomyEntry
    {
        $baseProperties = [
            'id' => $ibexaTaxonomyEntry->getId(),
            'innerTaxonomy' => $ibexaTaxonomyEntry,
        ];
        return $this->createLazyTaxonomyEntry($baseProperties);
    }

    /**
     * @param array<string, mixed>    $baseProperties
     * @param array<string, callable(TaxonomyEntry): mixed> $initializers
     */
    protected function createLazyTaxonomyEntry(
        array $baseProperties,
        array $initializers = []
    ): TaxonomyEntry {
        $initializers += [
            'innerContent' => function (TaxonomyEntry $instance): IbexaContent {
                $content = $instance->getInnerTaxonomy()->getContent();
                $this->responseTagger->addContentTags([$content->id]);
                return $content;
            },
            'fields' => function (TaxonomyEntry $instance): ContentFieldsCollection {
                $contentType = $instance->getInnerContent()->getContentType();
                $taxonomyEntryDefinition = $this->definitionManager->getDefinition(
                    TaxonomyEntryDefinition::class,
                    $contentType->identifier
                );

                $contentFields = new ContentFieldsCollection();
                foreach ($taxonomyEntryDefinition->getFields() as $fieldIdentifier => $contentFieldDefinition) {
                    $contentFields->set(
                        $fieldIdentifier,
                        new LazyValue(
                            fn () => $this->fieldValueTransformers->transform(
                                $instance,
                                $fieldIdentifier,
                                $contentFieldDefinition
                            )
                        )
                    );
                }

                return $contentFields;
            },
            'name' => fn (TaxonomyEntry $instance): string => $instance->getInnerContent()->getName(),
            'type' => fn (TaxonomyEntry $instance): string => $instance->getInnerContent()->getContentType()->identifier,
            'languageCodes' => fn (TaxonomyEntry $instance): array => array_keys($instance->getInnerContent()->versionInfo->getNames()),
            'mainLanguageCode' => fn (TaxonomyEntry $instance): string => $instance->getInnerContent()->contentInfo->mainLanguageCode,
            'alwaysAvailable' => fn (TaxonomyEntry $instance): bool => $instance->getInnerContent()->contentInfo->alwaysAvailable,
            'hidden' => fn (TaxonomyEntry $instance): bool => $instance->getInnerContent()->contentInfo->isHidden() || $instance->getInnerLocation()?->isHidden() || $instance->getInnerLocation()?->isInvisible(),
            'creationDate' => fn (TaxonomyEntry $instance): DateTime => $instance->getInnerContent()->contentInfo->publishedDate,
            'modificationDate' => fn (TaxonomyEntry $instance): DateTime => $instance->getInnerContent()->contentInfo->modificationDate,
            'identifier' => fn (TaxonomyEntry $instance): string => $instance->getInnerTaxonomy()->getIdentifier(),
            'level' => fn (TaxonomyEntry $instance): int => $instance->getInnerTaxonomy()->getLevel(),
            'parent' => function (TaxonomyEntry $instance): ?TaxonomyEntry {
                $parent = $instance->getInnerTaxonomy()->getParent();
                return $parent ? $this->transformTaxonomyEntry($parent) : null;
            },
        ];

        return TaxonomyEntry::instantiate($baseProperties, $initializers);
    }
}
