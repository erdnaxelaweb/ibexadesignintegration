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
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\TaxonomyEntryTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\TaxonomyEntry;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Taxonomy\FieldType\TaxonomyEntry\Value as TaxonomyEntryValue;

class TaxonomyEntryFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function __construct(
        protected TaxonomyEntryTransformer $taxonomyEntryTransformer
    ) {
    }

    public function support(string $ibexaFieldTypeIdentifier): bool
    {
        return $ibexaFieldTypeIdentifier === 'ibexa_taxonomy_entry';
    }


    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        FieldDefinition        $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): ?TaxonomyEntry {
        /** @var TaxonomyEntryValue $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        $taxonomyEntry = $fieldValue->getTaxonomyEntry();
        return $taxonomyEntry ? ($this->taxonomyEntryTransformer)($taxonomyEntry) : null;
    }
}
