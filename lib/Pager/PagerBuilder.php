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

    public function build(string $type, array $context = []): Pagerfanta
    {
        $request = $this->requestStack->getCurrentRequest();

        $configuration = $this->pagerConfigurationManager->getConfiguration($type);
        $defaultSearchData = new SearchData();
        $rawSearchData = $request->get($type, null);
        $searchData = $rawSearchData !== null ? SearchData::createFromRequest($rawSearchData) : $defaultSearchData;
        $searchFormName = $type;

        $query = new LocationQuery();
        $event = new PagerBuildEvent($type, $configuration, $query, $searchData, $defaultSearchData, $context);
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::GLOBAL_PAGER_BUILD);
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::getEventName($type));

        $query->filter = new Criterion\LogicalAnd($event->queryFilters);
        $query->aggregations = $event->queryAggregations;

        $adapter = new SearchAdapter(
            $query,
            $this->searchService,
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
        $pagerFanta = new Pagerfanta($adapter);
        $pagerFanta->setMaxPerPage($configuration['maxPerPage']);
        $pagerFanta->setCurrentPage((int) $request->get('page', 1));
        return $pagerFanta;
    }
}
