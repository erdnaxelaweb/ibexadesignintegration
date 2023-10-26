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
use ErdnaxelaWeb\IbexaDesignIntegration\Helper\LinkGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchAdapter;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\PagerConfigurationManager;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PagerBuilder
{
    public function __construct(
        protected PagerConfigurationManager $pagerConfigurationManager,
        protected PagerSearchFormBuilder    $pagerSearchFormBuilder,
        protected SearchService             $searchService,
        protected RequestStack              $requestStack,
        protected ContentTransformer        $contentTransformer,
        protected EventDispatcherInterface $eventDispatcher,
        protected LinkGenerator $linkGenerator,
        protected TranslatorInterface $translator
    ) {
    }

    public function build(string $type, array $context = []): Pagerfanta
    {
        $request = $this->requestStack->getCurrentRequest();

        $configuration = $this->pagerConfigurationManager->getConfiguration($type);
        $searchData = SearchData::createFromRequest($request->get($type, []));
        $searchFormName = $type;

        $query = new LocationQuery();
        $event = new PagerBuildEvent($type, $configuration, $query, $searchData, $context);
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::GLOBAL_PAGER_BUILD);
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::getEventName($type));

        $query->filter = new Criterion\LogicalAnd($event->queryFilters);
        $query->aggregations = $event->queryAggregations;

        $adapter = new SearchAdapter(
            $query,
            $this->searchService,
            $this->contentTransformer,
            function (AggregationResultCollection $aggregationResultCollection) use (
                $searchFormName,
                $configuration,
                $searchData
            ) {
                return $this->pagerSearchFormBuilder->build(
                    $searchFormName,
                    $configuration,
                    $aggregationResultCollection,
                    $searchData
                );
            },
            function () use ($searchFormName, $searchData, $request, $type) {
                $links = [];
                foreach ($searchData->filters as $filter => $filterValue) {
                    if (empty($filterValue)) {
                        continue;
                    }
                    $linkGeneraton = function (string $label, array $query, array $options) use ($request) {
                        return $this->linkGenerator->generateLink(
                            $this->linkGenerator->generateUrl(
                                $request->attributes->get('_route'),
                                array_merge($query, $request->attributes->get('_route_params', []))
                            ),
                            $label,
                            $options
                        );
                    };
                    if (is_array($filterValue)) {
                        foreach ($filterValue as $value) {
                            $query = $request->query->all();
                            $valueKey = array_search($value, $query[$searchFormName]['filters'][$filter]);
                            unset($query[$searchFormName]['filters'][$filter][$valueKey]);
                            $links[] = $linkGeneraton($value, $query, [
                                'extras' => [
                                    'filter' => $filter,
                                    'value' => $value,
                                ],
                            ]);
                        }
                    } else {
                        $query = $request->query->all();
                        unset($query[$searchFormName]['filters'][$filter]);
                        $links[] = $linkGeneraton($filterValue, $query, [
                            'extras' => [
                                'filter' => $filter,
                                'value' => $filterValue,
                            ],
                        ]);
                    }
                }
                return $links;
            }
        );
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($configuration['maxPerPage']);
        $pagerFanta->setCurrentPage($request->get('page', 1));
        return $pagerFanta;
    }
}
