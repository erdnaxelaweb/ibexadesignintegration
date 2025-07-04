<?php
declare( strict_types=1 );

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event;

use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;

class PagerPostSearchEvent
{
    public function __construct(
        protected SearchResult $searchResult,
        protected array $results,
        protected array $context = [],
    )
    {
    }
}
