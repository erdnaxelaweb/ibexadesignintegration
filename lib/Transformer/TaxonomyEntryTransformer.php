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

use ErdnaxelaWeb\StaticFakeDesign\Configuration\TaxonomyEntryConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use ErdnaxelaWeb\StaticFakeDesign\Value\TaxonomyEntry;
use Ibexa\Contracts\Taxonomy\Value\TaxonomyEntry as IbexaTaxonomyEntry;

class TaxonomyEntryTransformer
{
    use FieldValueTransformerTrait;

    public function __construct(
        protected TaxonomyEntryConfigurationManager $taxonomyEntryConfigurationManager,
        iterable $fieldValueTransformers
    ) {
        foreach ($fieldValueTransformers as $type => $fieldValueTransformer) {
            $this->fieldValueTransformers[$type] = $fieldValueTransformer;
        }
    }

    public function __invoke(IbexaTaxonomyEntry $ibexaTaxonomyEntry): TaxonomyEntry
    {
        $ibexaContent = $ibexaTaxonomyEntry->getContent();
        $contentType = $ibexaContent->getContentType();
        $contentTypeIdentifier = $contentType->identifier;
        $contentConfiguration = $this->taxonomyEntryConfigurationManager->getConfiguration($contentTypeIdentifier);

        $contentFields = new ContentFieldsCollection();
        foreach ($contentConfiguration['fields'] as $fieldIdentifier => $fieldConfiguration) {
            $contentFields->set(
                $fieldIdentifier,
                $this->transformFieldValue($ibexaContent, $contentType, $fieldIdentifier, $fieldConfiguration)
            );
        }

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
