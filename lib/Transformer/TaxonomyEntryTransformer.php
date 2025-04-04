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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use DateTime;
use ErdnaxelaWeb\IbexaDesignIntegration\Definition\TaxonomyEntryDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\ContentFieldsCollection;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\LazyTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\TaxonomyEntry;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
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

    public function transformTaxonomyEntry(IbexaTaxonomyEntry $ibexaTaxonomyEntry): TaxonomyEntry
    {
        $initializers = [
            'id' => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) use (
                $ibexaTaxonomyEntry
            ) {
                return $ibexaTaxonomyEntry->getId();
            },
            'innerContent' => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) use (
                $ibexaTaxonomyEntry
            ) {
                $content = $ibexaTaxonomyEntry->getContent();
                $this->responseTagger->addContentTags([$content->id]);
                return $content;
            },
            'innerTaxonomy' => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) use (
                $ibexaTaxonomyEntry
            ) {
                return $ibexaTaxonomyEntry;
            },
        ];
        $skippedProperties = ['id'];
        return $this->createLazyTaxonomyEntry($initializers, $skippedProperties);
    }

    protected function createLazyTaxonomyEntry(array $initializers, array $skippedProperties = []): TaxonomyEntry
    {
        $initializers += [
            "\0*\0fields" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) {
                $contentType = $instance->innerContent->getContentType();
                $taxonomyEntryDefinition = $this->definitionManager->getDefinition(
                    TaxonomyEntryDefinition::class,
                    $contentType->identifier
                );

                $contentFields = new ContentFieldsCollection();
                foreach ($taxonomyEntryDefinition->getFields() as $fieldIdentifier => $contentFieldDefinition) {
                    $contentFields->set(
                        $fieldIdentifier,
                        new LazyTransformer(
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
            "name" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerContent->getName();
            },
            "type" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerContent->getContentType()
                    ->identifier;
            },
            "creationDate" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerContent->contentInfo->publishedDate;
            },
            "modificationDate" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerContent->contentInfo->modificationDate;
            },
            "identifier" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerTaxonomy->getIdentifier();
            },
            "level" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerTaxonomy->getLevel();
            },
            "parent" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) {
                $parent = $instance->innerTaxonomy->getParent();
                return $parent ? $this->transformTaxonomyEntry($parent) : null;
            },
        ];

        return TaxonomyEntry::createLazyGhost($initializers, $skippedProperties);
    }

    public function __invoke(IbexaTaxonomyEntry $ibexaTaxonomyEntry): TaxonomyEntry
    {
        return $this->transformTaxonomyEntry($ibexaTaxonomyEntry);
    }
}
