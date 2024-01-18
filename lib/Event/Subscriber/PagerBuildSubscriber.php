<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Event\Subscriber;

use ErdnaxelaWeb\IbexaDesignIntegration\Event\PagerBuildEvent;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Sort\ChainSortHandler;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PagerBuildSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected ChainFilterHandler            $filterHandler,
        protected ChainSortHandler              $sortHandler,
    ) {
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

        if (isset($eventContext['location']) && $eventContext['location'] instanceof Location) {
            $event->queryFilters[] = new Criterion\ParentLocationId($eventContext['location']->id);
        }

        if (! empty($configuration['contentTypes'])) {
            $event->queryFilters[] = new Criterion\ContentTypeIdentifier($configuration['contentTypes']);
        }

        foreach ($configuration['filters'] as $filterName => $filter) {
            if (isset($searchData->filters[$filterName]) && ! empty($searchData->filters[$filterName])) {
                $event->queryFilters[] = $this->filterHandler->getCriterion(
                    $filter['type'],
                    $filterName,
                    $searchData->filters[$filterName],
                    $filter['options']
                );
            }
            $aggregation = $this->filterHandler->getAggregation($filter['type'], $filterName, $filter['options']);
            if ($aggregation) {
                $event->queryAggregations[] = $aggregation;
            }
        }

        if ($searchData->sort) {
            $sortIdentifier = $searchData->sort;
            $sortConfig = $configuration['sorts'][$sortIdentifier];
            $this->sortHandler->addSortClause($event->pagerQuery, $sortConfig['type'], $sortConfig['options']);
        } else {
            foreach ($configuration['sorts'] as $sort) {
                $this->sortHandler->addSortClause($event->pagerQuery, $sort['type'], $sort['options']);
            }
        }
    }
}
