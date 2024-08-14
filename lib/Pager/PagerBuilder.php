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
use ErdnaxelaWeb\StaticFakeDesign\Value\Pager;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PagerBuilder
{
    public function __construct(
        protected PagerConfigurationManager     $pagerConfigurationManager,
        protected PagerSearchFormBuilder        $pagerSearchFormBuilder,
        protected PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder,
        protected SearchService                 $searchService,
        protected RequestStack                  $requestStack,
        protected ContentTransformer            $contentTransformer,
        protected EventDispatcherInterface      $eventDispatcher,
        protected LinkGenerator                 $linkGenerator,
        protected TranslatorInterface           $translator
    ) {
    }

    public function build(
        string $type,
        array $context = [],
        SearchData $defaultSearchData = new SearchData()
    ): PagerfantaInterface {
        $request = $this->requestStack->getCurrentRequest();

        $configuration = $this->pagerConfigurationManager->getConfiguration($type);
        $rawSearchData = $request->get($type, null);
        $searchData = $rawSearchData !== null ? SearchData::createFromRequest($rawSearchData) : $defaultSearchData;
        $searchFormName = $type;

        $query = $configuration['searchType'] === SearchAdapter::SEARCH_TYPE_LOCATION ? new LocationQuery() : new Query();
        $event = new PagerBuildEvent($type, $configuration, $query, $searchData, $defaultSearchData, $context);
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::GLOBAL_PAGER_BUILD);
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::getEventName($type));

        if (! empty($event->queryCriterions)) {
            $query->query = count($event->queryCriterions) > 1 ? new Criterion\LogicalAnd(
                $event->queryCriterions
            ) : reset($event->queryCriterions);
        }
        if (! empty($event->filtersCriterions)) {
            $query->filter = count($event->filtersCriterions) > 1 ? new Criterion\LogicalAnd(
                $event->filtersCriterions
            ) : reset($event->filtersCriterions);
        }
        if (! empty($event->aggregations)) {
            $query->aggregations = $event->aggregations;
        }

        $adapter = new SearchAdapter(
            $query,
            $this->searchService,
            $configuration['searchType'],
            $this->contentTransformer,
            function (AggregationResultCollection $aggregationResultCollection) use (
                $defaultSearchData,
                $searchFormName,
                $configuration,
            ) {
                $formBuilder = $this->pagerSearchFormBuilder->build(
                    $searchFormName,
                    $configuration,
                    $aggregationResultCollection,
                    $defaultSearchData
                );

                $form = $formBuilder->getForm();
                $form->handleRequest($this->requestStack->getCurrentRequest());
                return $form;
            },
            function (FormInterface $filtersFormBuilder) use ($searchFormName, $configuration, $searchData) {
                return $this->pagerActiveFiltersListBuilder->buildList(
                    $searchFormName,
                    $configuration,
                    $filtersFormBuilder,
                    $searchData
                );
            }
        );
        $pagerFanta = new Pager($adapter);
        $pagerFanta->setMaxPerPage($configuration['maxPerPage']);
        $pagerFanta->setHeadlineCount($configuration['headlineCount']);

        $page = $request->get('page', 1);
        $pagerFanta->setCurrentPage(min(is_numeric($page) ? $page : 1, $pagerFanta->getNbPages()));

        return $pagerFanta;
    }
}
