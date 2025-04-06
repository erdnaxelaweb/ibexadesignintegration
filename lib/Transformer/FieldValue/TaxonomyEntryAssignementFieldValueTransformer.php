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
use Ibexa\Taxonomy\FieldType\TaxonomyEntryAssignment\Value as TaxonomyEntryAssignmentValue;

class TaxonomyEntryAssignementFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function __construct(
        protected TaxonomyEntryTransformer $taxonomyEntryTransformer
    ) {
    }

    public function support(string $ibexaFieldTypeIdentifier): bool
    {
        return $ibexaFieldTypeIdentifier === 'ibexa_taxonomy_entry_assignment';
    }

    /**
     * @return TaxonomyEntry[]|TaxonomyEntry
     */
    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        FieldDefinition        $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): array|TaxonomyEntry {
        $max = $contentFieldDefinition->getOption('max');
        /** @var TaxonomyEntryAssignmentValue $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        $entries = [];
        $taxonomyEntries = array_slice($fieldValue->getTaxonomyEntries(), 0, $max);
        foreach ($taxonomyEntries as $taxonomyEntry) {
            $taxonomyEntry = ($this->taxonomyEntryTransformer)($taxonomyEntry);
            if ($max === 1) {
                return $taxonomyEntry;
            }
            $entries[] = $taxonomyEntry;
        }

        return $entries;
    }
}
