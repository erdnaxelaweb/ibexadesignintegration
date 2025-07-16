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
use ErdnaxelaWeb\StaticFakeDesign\Value\LazyValue;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Taxonomy\Value\TaxonomyEntry as IbexaTaxonomyEntry;
use Ibexa\HttpCache\Handler\TagHandler;
use Symfony\Component\VarExporter\Instantiator;

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
        $instance = Instantiator::instantiate(TaxonomyEntry::class, [
            'id' => $ibexaTaxonomyEntry->getId(),
            'innerTaxonomy' => $ibexaTaxonomyEntry,
        ]);
        $skippedProperties = [
            'id' => true,
            'innerTaxonomy' => true,
        ];
        return $this->createLazyTaxonomyEntry([], $skippedProperties, $instance);
    }

    /**
     * @param array<string, callable(TaxonomyEntry, string, ?string): mixed> $initializers
     * @param array<string, true> $skippedProperties
     */
    protected function createLazyTaxonomyEntry(array $initializers, array $skippedProperties = [], ?TaxonomyEntry $instance = null): TaxonomyEntry
    {
        $initializers += [
            'innerContent' => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope): IbexaContent {
                $content = $instance->innerTaxonomy->getContent();
                $this->responseTagger->addContentTags([$content->id]);
                return $content;
            },
            "\0*\0fields" => function (
                TaxonomyEntry $instance,
                string $propertyName,
                ?string $propertyScope
            ): ContentFieldsCollection {
                $contentType = $instance->innerContent->getContentType();
                $taxonomyEntryDefinition = $this->definitionManager->getDefinition(
                    TaxonomyEntryDefinition::class,
                    $contentType->identifier
                );

                $contentFields = new ContentFieldsCollection();
                foreach ($taxonomyEntryDefinition->getFields() as $fieldIdentifier => $contentFieldDefinition) {
                    $contentFields->set(
                        $fieldIdentifier,
                        new LazyValue(
                            function () use ($instance, $fieldIdentifier, $contentFieldDefinition) {
                                return $this->fieldValueTransformers->transform(
                                    $instance,
                                    $fieldIdentifier,
                                    $contentFieldDefinition
                                );
                            }
                        )
                    );
                }

                return $contentFields;
            },
            "name" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope): string {
                return $instance->innerContent->getName();
            },
            "type" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope): string {
                return $instance->innerContent->getContentType()
                    ->identifier;
            },
            "languageCodes" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope): array {
                return array_keys($instance->innerContent->versionInfo->getNames());
            },
            "mainLanguageCode" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope): string {
                return $instance->innerContent->contentInfo->mainLanguageCode;
            },
            "alwaysAvailable" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope): bool {
                return $instance->innerContent->contentInfo->alwaysAvailable;
            },
            "hidden" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope): bool {
                return $instance->innerContent->contentInfo->isHidden;
            },
            "creationDate" => function (
                TaxonomyEntry $instance,
                string $propertyName,
                ?string $propertyScope
            ): DateTime {
                return $instance->innerContent->contentInfo->publishedDate;
            },
            "modificationDate" => function (
                TaxonomyEntry $instance,
                string $propertyName,
                ?string $propertyScope
            ): DateTime {
                return $instance->innerContent->contentInfo->modificationDate;
            },
            "identifier" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope): string {
                return $instance->innerTaxonomy->getIdentifier();
            },
            "level" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope): int {
                return $instance->innerTaxonomy->getLevel();
            },
            "parent" => function (
                TaxonomyEntry $instance,
                string $propertyName,
                ?string $propertyScope
            ): ?TaxonomyEntry {
                $parent = $instance->innerTaxonomy->getParent();
                return $parent ? $this->transformTaxonomyEntry($parent) : null;
            },
        ];

        return TaxonomyEntry::createLazyGhost($initializers, $skippedProperties, $instance);
    }
}
