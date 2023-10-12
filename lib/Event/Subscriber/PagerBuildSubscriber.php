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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event\Subscriber;

use ErdnaxelaWeb\IbexaDesignIntegration\Event\PagerBuildEvent;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\FilterHandlerInterface;
use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PagerBuildSubscriber implements EventSubscriberInterface
{
    /**
     * @var FilterHandlerInterface[]
     */
    protected array $filtersHandler;

    /**
     * @var \ErdnaxelaWeb\IbexaDesignIntegration\Pager\Sort\SortHandlerInterface[]
     */
    protected array $sortsHandler;

    public function __construct(
        iterable                           $filtersHandler,
        iterable                           $sortsHandler,
    ) {
        foreach ($filtersHandler as $type => $filterHandler) {
            $this->filtersHandler[$type] = $filterHandler;
        }
        foreach ($sortsHandler as $type => $sortHandler) {
            $this->sortsHandler[$type] = $sortHandler;
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PagerBuildEvent::GLOBAL_PAGER_BUILD => 'onPagerBuild',
        ];
    }

    public function onPagerBuild(PagerBuildEvent $event): void
    {
        $eventContext = $event->buildContext;
        $configuration = $event->pagerConfiguration;
        $searchData = $event->searchData;

        if ($eventContext['location'] instanceof Location) {
            $event->queryFilters[] = new Criterion\ParentLocationId($eventContext['location']->id);
        }

        if (! empty($configuration['contentTypes'])) {
            $event->queryFilters[] = new Criterion\ContentTypeIdentifier($configuration['contentTypes']);
        }

        foreach ($configuration['filters'] as $filterName => $filter) {
            $field = $filter['field'];

            $filterHandler = $this->filtersHandler[$filter['type']];
            if (isset($searchData->filters[$filterName]) && ! empty($searchData->filters[$filterName])) {
                $event->queryFilters[] = $filterHandler->getCriterion(
                    $filterName,
                    $field,
                    $searchData->filters[$filterName]
                );
            }
            $event->queryAggregations[] = $filterHandler->getAggregation($filterName, $field);
        }

        foreach ($configuration['sorts'] as $sortType => $sortConfig) {
            $sortHandler = $this->sortsHandler[$sortType];
            if (is_string($sortConfig)) {
                $sortConfig = [
                    'sortDirection' => $sortConfig,
                ];
            }
            $event->pagerQuery->sortClauses[] = $sortHandler->getSortClause($sortConfig);
        }
    }
}
