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

use ErdnaxelaWeb\StaticFakeDesign\Definition\PagerDefinition as NativePagerDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Definition\PagerSortDefinition;
use InvalidArgumentException;

class PagerDefinition extends NativePagerDefinition
{
    /**
     * @param array<string>                        $contentTypes         Array of content types to include
     * @param int                                  $maxPerPage           Maximum number of items per page
     * @param array<string, PagerSortDefinition>   $sorts                Array of sort options
     * @param array<string, PagerFilterDefinition> $filters              Array of filter options
     * @param array<string>                        $excludedContentTypes Array of content types to exclude
     * @param int                                  $headlineCount        Number of headline items
     */
    public function __construct(
        protected readonly string $identifier,
        protected readonly array $contentTypes,
        protected readonly int $maxPerPage,
        protected readonly array $sorts,
        protected readonly array $filters,
        protected readonly array $excludedContentTypes,
        protected readonly int $headlineCount,
        protected readonly string $searchType
    ) {
    }

    public function getSearchType(): string
    {
        return $this->searchType;
    }

    /**
     * Get the filter options.
     *
     * @return array<string, PagerFilterDefinition>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFilter(string $name): PagerFilterDefinition
    {
        if (!$this->hasFilter($name)) {
            throw new InvalidArgumentException("Filter \"$name\" does not exist.");
        }
        return $this->filters[$name];
    }
}
