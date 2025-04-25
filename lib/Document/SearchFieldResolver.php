<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Document;

use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;

class SearchFieldResolver
{
    /**
     * @param array<string, string> $fieldNameGeneratorMap // map between ibexa type and solr suffix
     * @param array<string, class-string<FieldType>> $searchFieldTypesMap // map between ibexa type and
     */
    public function __construct(
        protected array $fieldNameGeneratorMap,
        protected array $searchFieldTypesMap
    ) {
    }

    public function __invoke(string $fieldIdentifier, mixed $value): ?Field
    {
        foreach ($this->fieldNameGeneratorMap as $type => $suffix) {
            if (str_ends_with($fieldIdentifier, "_$suffix")) {
                $fieldType = $this->searchFieldTypesMap[$type] ?? null;
                if (!$fieldType) {
                    return null;
                }

                $name = substr($fieldIdentifier, 0, -strlen("_$suffix"));
                return new Field(
                    $name,
                    $value,
                    new $fieldType()
                );
            }
        }
        return null;
    }
}
