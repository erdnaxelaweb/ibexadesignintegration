<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Symfony\Contracts\EventDispatcher\Event;

class PagerBuildEvent extends Event
{
    public const GLOBAL_PAGER_BUILD = 'erdnaxelaweb.ibexa_design_integration.pager.build';

    public const PAGER_BUILD_PATTERN = 'erdnaxelaweb.ibexa_design_integration.pager.build.%s';

    /**
     * @param array<string, mixed>                                                           $buildContext
     * @param array<string, Criterion>                                                           $queryCriterions
     * @param array<string, Criterion>                                                           $filtersCriterions
     * @param array<string, Aggregation>                                                           $aggregations
     */
    public function __construct(
        public readonly string $pagerType,
        public PagerDefinition $pagerDefinition,
        public readonly Query $pagerQuery,
        public readonly SearchData $searchData,
        public readonly SearchData $defaultSearchData,
        public readonly array $buildContext,
        public array $queryCriterions = [],
        public array $filtersCriterions = [],
        public array $aggregations = []
    ) {
    }

    public static function getEventName(string $pagerType): string
    {
        return sprintf(self::PAGER_BUILD_PATTERN, $pagerType);
    }
}
