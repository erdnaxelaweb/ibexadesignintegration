<?php
declare( strict_types=1 );

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;

class PagerPreSearchEvent
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query $query
     * @param array<string, mixed>                                                 $context
     */
    public function __construct(
        protected Query $query,
        protected array $context = [],
    )
    {
    }
}
