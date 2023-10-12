<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Symfony\Contracts\EventDispatcher\Event;

class PagerBuildEvent extends Event
{
    public const GLOBAL_PAGER_BUILD = 'erdnaxelaweb.ibexa_design_integration.pager.build';

    public const PAGER_BUILD_PATTERN = 'erdnaxelaweb.ibexa_design_integration.pager.build.%s';

    public function __construct(
        public readonly string     $pagerType,
        public readonly array      $pagerConfiguration,
        public readonly Query      $pagerQuery,
        public readonly SearchData $searchData,
        public readonly array      $buildContext,
        public array               $queryFilters = [],
        public array               $queryAggregations = []
    ) {
    }

    public static function getEventName(string $pagerType): string
    {
        return sprintf(self::PAGER_BUILD_PATTERN, $pagerType);
    }
}
