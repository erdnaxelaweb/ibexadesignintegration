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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager;

use ErdnaxelaWeb\IbexaDesignIntegration\Event\PagerBuildEvent;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\PagerConfigurationManager;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\Core\Pagination\Pagerfanta\LocationSearchAdapter;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PagerBuilder
{
    public function __construct(
        protected PagerConfigurationManager $pagerConfigurationManager,
        protected PagerSearchFormBuilder    $pagerSearchFormBuilder,
        protected SearchService             $searchService,
        protected RequestStack              $requestStack,
        protected ContentTransformer        $contentTransformer,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function build(string $type, array $context = []): Pagerfanta
    {
        $request = $this->requestStack->getCurrentRequest();

        $configuration = $this->pagerConfigurationManager->getConfiguration($type);
        $searchData = SearchData::createFromRequest($request->get('form', []));

        $query = new LocationQuery();
        $event = new PagerBuildEvent($type, $configuration, $query, $searchData, $context);
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::GLOBAL_PAGER_BUILD);
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::getEventName($type));

        $query->filter = new Criterion\LogicalAnd($event->queryFilters);
        $query->aggregations = $event->queryAggregations;

        $adapter = new LocationSearchAdapter(
            $query,
            $this->searchService,
        );
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($configuration['maxPerPage']);
        $pagerFanta->setCurrentPage($request->get('page', 1));
        return $pagerFanta;
    }
}
