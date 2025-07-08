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
        $suffixes = array_values($this->fieldNameGeneratorMap);
        $regex = sprintf('/^(.*)_(%s)$/', implode('|', $suffixes));
        if (preg_match($regex, $fieldIdentifier, $matches)) {
            $fieldName = $matches[1];
            $fieldSuffix = $matches[2];
        } else {
            $fieldName = $fieldIdentifier;
            $fieldSuffix = 'doc';
        }
        foreach ($this->fieldNameGeneratorMap as $type => $suffix) {
            if ($suffix === $fieldSuffix) {
                $fieldType = $this->searchFieldTypesMap[$type] ?? null;
                if (!$fieldType) {
                    break;
                }
                if ($fieldType === FieldType\StringField::class && !is_scalar($value)) {
                    $value = serialize($value);
                }

                return new Field(
                    $fieldName,
                    $value,
                    new $fieldType()
                );
            }
        }
        return null;
    }
}
