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
            $event->filtersCriterions['location'] = new Criterion\ParentLocationId($eventContext['location']->id);
        }

        if (! empty($configuration['contentTypes'])) {
            $event->filtersCriterions['contentTypes'] = new Criterion\ContentTypeIdentifier(
                $configuration['contentTypes']
            );
        }

        foreach ($configuration['filters'] as $filterName => $filter) {
            $criterionType = $filter['criterionType'] === 'query' ? 'queryCriterions' : 'filtersCriterions';
            if (isset($searchData->filters[$filterName]) && ! empty($searchData->filters[$filterName])) {
                $event->{$criterionType}[$filterName] = $this->filterHandler->getCriterion(
                    $filter['type'],
                    $filterName,
                    $searchData->filters[$filterName],
                    $filter['options']
                );
            }
            $aggregation = $this->filterHandler->getAggregation($filter['type'], $filterName, $filter['options']);
            if ($aggregation) {
                $event->aggregations[$filterName] = $aggregation;
            }
        }

        if (! empty($configuration['sorts'])) {
            $sortIdentifier = $searchData->sort ?? $configuration['sorts'];
            foreach ($sortIdentifier as $sort) {
                $this->sortHandler->addSortClause($event->pagerQuery, $sort['type'], $sort['options']);
            }
        }
    }
}
