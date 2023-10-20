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

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\TaxonomyEntryTransformer;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Taxonomy\FieldType\TaxonomyEntryAssignment\Value as TaxonomyEntryAssignmentValue;

class TaxonomyEntryAssignementFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected TaxonomyEntryTransformer $taxonomyEntryTransformer
    ) {
    }

    public function transformFieldValue(
        Content         $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition
    ): array {
        /** @var TaxonomyEntryAssignmentValue $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        $entries = [];
        foreach ($fieldValue->getTaxonomyEntries() as $taxonomyEntry) {
            $entries[] = ($this->taxonomyEntryTransformer)($taxonomyEntry);
        }
        return $entries;
    }
}
