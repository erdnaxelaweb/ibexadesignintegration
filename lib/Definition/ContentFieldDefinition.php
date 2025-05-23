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

use ErdnaxelaWeb\StaticFakeDesign\Definition\ContentFieldDefinition as NativeContentFieldDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;

class ContentFieldDefinition extends NativeContentFieldDefinition
{
    /**
     * @param string|string[]                                                $name
     * @param string|string[]                                                $description
     */
    public function __construct(
        string $identifier,
        string $type,
        bool $required,
        mixed $value,
        DefinitionOptions $options,
        protected readonly string|array $name,
        protected readonly string|array $description,
        protected readonly bool $searchable,
        protected readonly bool $infoCollector,
        protected readonly bool $translatable,
        protected readonly string $category,
    ) {
        parent::__construct($identifier, $type, $required, $value, $options);
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

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isInfoCollector(): bool
    {
        return $this->infoCollector;
    }

    public function isTranslatable(): bool
    {
        return $this->translatable;
    }

    public function getCategory(): string
    {
        return $this->category;
    }
}
