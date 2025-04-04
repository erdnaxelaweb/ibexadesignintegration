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

use ErdnaxelaWeb\StaticFakeDesign\Definition\ContentDefinition as NativeContentDefinition;
use InvalidArgumentException;

class ContentDefinition extends NativeContentDefinition
{
    /**
     * @param array<string, ContentFieldDefinition> $fields Array of field definitions
     * @param string[] $parent Array of possible parent types
     * @param array<mixed> $models Array of model data used when generating content
     * @param string|string[] $name
     * @param string|string[] $description
     */
    public function __construct(
        protected readonly string $identifier,
        protected readonly array $fields,
        protected readonly array $parent,
        protected readonly array $models,
        protected readonly string|array $name,
        protected readonly string|array $description,
        protected readonly string $nameSchema,
        protected readonly string $urlAliasSchema,
        protected readonly bool $container,
        protected readonly bool $defaultAlwaysAvailable,
        protected readonly string $defaultSortField,
        protected readonly string $defaultSortOrder,
    ) {
    }

    /**
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
    public function getName(): string|array
    {
        return $this->name;
    }

    /**
     * @return string|string[]
     */
    public function getDescription(): string|array
    {
        return $this->description;
    }

    public function getNameSchema(): string
    {
        return $this->nameSchema;
    }

    public function getUrlAliasSchema(): string
    {
        return $this->urlAliasSchema;
    }

    public function isContainer(): bool
    {
        return $this->container;
    }

    public function isDefaultAlwaysAvailable(): bool
    {
        return $this->defaultAlwaysAvailable;
    }

    public function getDefaultSortField(): string
    {
        return $this->defaultSortField;
    }

    public function getDefaultSortOrder(): string
    {
        return $this->defaultSortOrder;
    }
}
