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

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\TaxonomyEntryTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\TaxonomyEntry;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Taxonomy\FieldType\TaxonomyEntryAssignment\Value as TaxonomyEntryAssignmentValue;

class TaxonomyEntryAssignementFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected TaxonomyEntryTransformer $taxonomyEntryTransformer
    ) {
    }

    public function transformFieldValue(
        AbstractContent $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
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
