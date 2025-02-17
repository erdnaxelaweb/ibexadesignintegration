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

        if (isset($eventContext['location']) && $eventContext['location'] instanceof Location && $eventContext['location']->id) {
            $event->filtersCriterions['location'] = new Criterion\ParentLocationId($eventContext['location']->id);
        }

        if (! empty($configuration['contentTypes'])) {
            $event->filtersCriterions['contentTypes'] = new Criterion\ContentTypeIdentifier(
                $configuration['contentTypes']
            );
        }

        if (! empty($configuration['excludedContentTypes'])) {
            $event->filtersCriterions['excludedContentTypes'] = new Criterion\LogicalNot(
                new Criterion\ContentTypeIdentifier($configuration['excludedContentTypes'])
            );
        }

        ['criterions' => $criterions, 'aggregations' => $aggregations] = $this->resolveFilters(
            $configuration['filters'],
            $event
        );

        foreach ($criterions as $criterionType => $typeCriterions) {
            foreach ($typeCriterions as $filterName => $criterion) {
                $event->{$criterionType}[$filterName] = $criterion;
            }
        }
        foreach ($aggregations as $filterName => $aggregation) {
            $event->aggregations[$filterName] = $aggregation;
        }

        if (! empty($configuration['sorts'])) {
            $sortIdentifier = $searchData->sort ?? array_key_first($configuration['sorts']);
            $sortConfig = $configuration['sorts'][$sortIdentifier];
            $this->sortHandler->addSortClause($event->pagerQuery, $sortConfig['type'], $sortConfig['options']);
        }
    }

    protected function resolveFilters(array $filters, PagerBuildEvent $event): array
    {
        $searchData = $event->searchData;
        $criterions = [];
        $aggregations = [];
        foreach ($filters as $filterName => $filter) {
            ['criterions' => $nestedCriterions, 'aggregations' => $nestedAggregations] = $this->resolveFilters(
                $filter['nested'],
                $event
            );

            // Criterion
            $criterionType = $filter['criterionType'] === 'query' ? 'queryCriterions' : 'filtersCriterions';
            if (isset($searchData->filters[$filterName]) && ! empty($searchData->filters[$filterName])) {
                $criterions[$criterionType][$filterName] = $this->filterHandler->getCriterion(
                    $filter['type'],
                    $filterName,
                    $searchData->filters[$filterName],
                    $filter['options']
                );
            }
            $criterions += $nestedCriterions;

            // Aggregation
            $aggregation = $this->filterHandler->getAggregation($filter['type'], $filterName, $filter['options']);
            if ($aggregation) {
                $aggregations[$filterName] = $aggregation;
            }

            if ($aggregation && method_exists($aggregation, 'setNestedAggregations')) {
                $aggregation->setNestedAggregations($nestedAggregations);
            } else {
                $aggregations += $nestedAggregations;
            }
        }

        return [
            'criterions' => $criterions,
            'aggregations' => $aggregations,
        ];
    }
}
