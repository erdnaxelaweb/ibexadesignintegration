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
use ErdnaxelaWeb\StaticFakeDesign\Value\TaxonomyEntry;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Taxonomy\FieldType\TaxonomyEntry\Value as TaxonomyEntryValue;

class TaxonomyEntryFieldValueTransformer implements FieldValueTransformerInterface
{
    public function __construct(
        protected TaxonomyEntryTransformer $taxonomyEntryTransformer
    ) {
    }

    public function transformFieldValue(
        Content         $content,
        string          $fieldIdentifier,
        FieldDefinition $fieldDefinition,
        array $fieldConfiguration
    ): ?TaxonomyEntry {
        /** @var TaxonomyEntryValue $fieldValue */
        $fieldValue = $content->getFieldValue($fieldIdentifier);
        $taxonomyEntry = $fieldValue->getTaxonomyEntry();
        return $taxonomyEntry ? ($this->taxonomyEntryTransformer)($taxonomyEntry) : null;
    }
}
