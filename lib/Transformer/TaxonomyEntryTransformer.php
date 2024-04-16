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

use ErdnaxelaWeb\IbexaDesignIntegration\Value\ContentFieldsCollection;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\TaxonomyEntry;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\TaxonomyEntryConfigurationManager;
use Ibexa\Contracts\Taxonomy\Value\TaxonomyEntry as IbexaTaxonomyEntry;
use Ibexa\HttpCache\Handler\TagHandler;

class TaxonomyEntryTransformer
{
    public function __construct(
        protected TaxonomyEntryConfigurationManager $taxonomyEntryConfigurationManager,
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
        ];
        $skippedProperties = ['id'];
        return $this->createLazyTaxonomyEntry($initializers, $skippedProperties);
    }

    protected function createLazyTaxonomyEntry(array $initializers, array $skippedProperties = []): TaxonomyEntry
    {
        $initializers += [
            "fields" => function (TaxonomyEntry $instance, string $propertyName, ?string $propertyScope) {
                $contentType = $instance->innerContent->getContentType();
                $contentConfiguration = $this->taxonomyEntryConfigurationManager->getConfiguration(
                    $contentType->identifier
                );
                return new ContentFieldsCollection(
                    $instance->innerContent,
                    $contentType,
                    $contentConfiguration['fields'],
                    $this->fieldValueTransformers
                );
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
        ];

        return TaxonomyEntry::createLazyGhost($initializers, $skippedProperties);
    }

    public function __invoke(IbexaTaxonomyEntry $ibexaTaxonomyEntry): TaxonomyEntry
    {
        return $this->transformTaxonomyEntry($ibexaTaxonomyEntry);
    }
}
