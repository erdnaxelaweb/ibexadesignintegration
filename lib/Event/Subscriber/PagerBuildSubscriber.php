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
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
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
        $pagerDefinition = $event->pagerDefinition;
        $searchData = $event->searchData;

        if (isset($eventContext['location']) && $eventContext['location'] instanceof Location && $eventContext['location']->id) {
            $event->filtersCriterions['location'] = new Criterion\ParentLocationId($eventContext['location']->id);
        }

        if (!empty($pagerDefinition->getContentTypes())) {
            $event->filtersCriterions['contentTypes'] = new Criterion\ContentTypeIdentifier(
                $pagerDefinition->getContentTypes()
            );
        }

        if (!empty($pagerDefinition->getExcludedContentTypes())) {
            $event->filtersCriterions['excludedContentTypes'] = new Criterion\LogicalNot(
                new Criterion\ContentTypeIdentifier($pagerDefinition->getExcludedContentTypes())
            );
        }

        ['criterions' => $criterions, 'aggregations' => $aggregations] = $this->resolveFilters(
            $pagerDefinition->getFilters(),
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

        if (!empty($pagerDefinition->getSorts())) {
            $sortIdentifier = $searchData->sort ?? array_key_first($pagerDefinition->getSorts());
            $sortDefinition = $pagerDefinition->getSort($sortIdentifier);
            $this->sortHandler->addSortClause(
                $event->pagerQuery,
                $sortDefinition->getType(),
                $sortDefinition->getOptions()
            );
        }
    }

    /**
     * @param array<string, \ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerFilterDefinition> $filterDefinitions
     *
     * @return array{criterions: array<string, array<string, CriterionInterface>>, aggregations: array<string, Aggregation>}
     */
    protected function resolveFilters(array $filterDefinitions, PagerBuildEvent $event): array
    {
        $searchData = $event->searchData;
        $criterions = [];
        $aggregations = [];
        foreach ($filterDefinitions as $filterName => $filterDefinition) {
            ['criterions' => $nestedCriterions, 'aggregations' => $nestedAggregations] = $this->resolveFilters(
                $filterDefinition->getNestedFilters(),
                $event
            );

            // Criterion
            $criterionType = $filterDefinition->getCriterionType() === 'query' ? 'queryCriterions' : 'filtersCriterions';
            if (isset($searchData->filters[$filterName]) && !empty($searchData->filters[$filterName])) {
                $criterions[$criterionType][$filterName] = $this->filterHandler->getCriterion(
                    $filterDefinition->getType(),
                    $filterName,
                    $searchData->filters[$filterName],
                    $filterDefinition->getOptions()
                );
            }
            $criterions += $nestedCriterions;

            // Aggregation
            $aggregation = $this->filterHandler->getAggregation(
                $filterDefinition->getType(),
                $filterName,
                $filterDefinition->getOptions()
            );
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
