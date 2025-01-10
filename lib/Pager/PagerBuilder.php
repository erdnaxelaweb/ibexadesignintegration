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

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager;

use ErdnaxelaWeb\IbexaDesignIntegration\Event\PagerBuildEvent;
use ErdnaxelaWeb\IbexaDesignIntegration\Helper\LinkGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\PagerConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\Pager;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PagerBuilder
{
    /**
     * @var \ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory\SearchTypeFactoryInterface[]
     */
    protected array $searchTypeFactories = [];

    public function __construct(
        iterable $searchTypeFactories,
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
        foreach ($searchTypeFactories as $type => $searchTypeFactory) {
            $this->searchTypeFactories[$type] = $searchTypeFactory;
        }
    }

    public function build(
        string $type,
        array $context = [],
        SearchData $defaultSearchData = new SearchData()
    ): PagerfantaInterface {
        $request = $this->requestStack->getCurrentRequest();
        $configuration = $this->pagerConfigurationManager->getConfiguration($type);

        $searchTypeFactory = $this->searchTypeFactories[$configuration['searchType']];
        $searchType = ($searchTypeFactory)(
            $type,
            $configuration,
            $request,
            $defaultSearchData
        );

        $query = $searchType->getQuery();
        $event = new PagerBuildEvent(
            $type,
            $configuration,
            $query,
            $searchType->getSearchData(),
            $defaultSearchData,
            $context
        );
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

        $pagerFanta = new Pager($searchType->getAdapter());
        $pagerFanta->setMaxPerPage($configuration['maxPerPage']);
        $pagerFanta->setHeadlineCount($configuration['headlineCount']);

        $page = $request->get('page', 1);
        $pagerFanta->setCurrentPage(min(is_numeric($page) ? $page : 1, $pagerFanta->getNbPages()));

        return $pagerFanta;
    }
}
