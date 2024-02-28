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
use ErdnaxelaWeb\StaticFakeDesign\Configuration\TaxonomyEntryConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\TaxonomyEntry;
use Ibexa\Contracts\Taxonomy\Value\TaxonomyEntry as IbexaTaxonomyEntry;

class TaxonomyEntryTransformer
{
    public function __construct(
        protected TaxonomyEntryConfigurationManager $taxonomyEntryConfigurationManager,
        protected FieldValueTransformer $fieldValueTransformers
    ) {
    }

    public function __invoke(IbexaTaxonomyEntry $ibexaTaxonomyEntry): TaxonomyEntry
    {
        $ibexaContent = $ibexaTaxonomyEntry->getContent();
        $contentType = $ibexaContent->getContentType();
        $contentTypeIdentifier = $contentType->identifier;
        $contentConfiguration = $this->taxonomyEntryConfigurationManager->getConfiguration($contentTypeIdentifier);

        $contentFields = new ContentFieldsCollection(
            $ibexaContent,
            $contentType,
            $contentConfiguration['fields'],
            $this->fieldValueTransformers
        );

        return new TaxonomyEntry(
            $ibexaTaxonomyEntry->getId(),
            $ibexaContent->getName(),
            $contentTypeIdentifier,
            $ibexaContent->contentInfo->publishedDate,
            $ibexaContent->contentInfo->modificationDate,
            $contentFields,
        );
    }
}
