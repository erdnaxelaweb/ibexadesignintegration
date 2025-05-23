<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Definition;

use ErdnaxelaWeb\StaticFakeDesign\Definition\TaxonomyEntryDefinition as NativeTaxonomyEntryDefinition;
use InvalidArgumentException;

class TaxonomyEntryDefinition extends NativeTaxonomyEntryDefinition
{
    /**
     * @param array<string, ContentFieldDefinition> $fields Array of field definitions
     * @param array<mixed> $models Array of model data used when generating content
     * @param string|string[] $name
     * @param string|string[] $description
     */
    public function __construct(
        protected readonly string $identifier,
        protected readonly array $fields,
        protected readonly array $models,
        protected readonly string|array $name,
        protected readonly string|array $description
    ) {
    }

    /**
     * Get the field definitions.
     *
     * @return array<string, ContentFieldDefinition>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get a specific field definition by identifier.
     */
    public function getField(string $identifier): ContentFieldDefinition
    {
        if (!$this->hasField($identifier)) {
            throw new InvalidArgumentException("Field \"$identifier\" does not exist.");
        }
        return $this->fields[$identifier];
    }

    /**
     * @return string|string[]
     */
    public function getName(): array|string
    {
        return $this->name;
    }

    /**
     * @return string|string[]
     */
    public function getDescription(): array|string
    {
        return $this->description;
    }
}
