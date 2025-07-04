<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager;

use ErdnaxelaWeb\IbexaDesignIntegration\Event\PagerBuildEvent;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory\SearchTypeFactoryInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Definition\PagerDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Value\Pager;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PagerBuilder
{
    /**
     * @var SearchTypeFactoryInterface[]
     */
    protected array $searchTypeFactories = [];

    /**
     * @param iterable<SearchTypeFactoryInterface>                                                       $searchTypeFactories
     */
    public function __construct(
        iterable $searchTypeFactories,
        private readonly DefinitionManager $definitionManager,
        private readonly RequestStack $requestStack,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        foreach ($searchTypeFactories as $type => $searchTypeFactory) {
            $this->searchTypeFactories[$type] = $searchTypeFactory;
        }
    }

    /**
     * @param array<string, mixed>                                                 $context
     */
    public function build(
        string $type,
        array $context = [],
        SearchData $defaultSearchData = new SearchData()
    ): Pager {
        /** @var \ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition $pagerDefinition */
        $pagerDefinition = $this->definitionManager->getDefinition(PagerDefinition::class, $type);

        $request = $this->requestStack->getCurrentRequest();

        $defaultLimit = $pagerDefinition->getMaxPerPage();
        $defaultPage = 1;
        $requestedLimit = $request ? $request->get('limit', $defaultLimit) : $defaultLimit;
        $requestedPage = $request ? $request->get('page', $defaultPage) : $defaultPage;

        $searchTypeFactory = $this->searchTypeFactories[$pagerDefinition->getSearchType()];
        $searchType = ($searchTypeFactory)(
            $type,
            $pagerDefinition,
            $request,
            $defaultSearchData,
            $context
        );

        $query = $searchType->getQuery();
        $event = new PagerBuildEvent(
            $type,
            $pagerDefinition,
            $query,
            $searchType->getSearchData(),
            $defaultSearchData,
            $context
        );
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::GLOBAL_PAGER_BUILD);
        $this->eventDispatcher->dispatch($event, PagerBuildEvent::getEventName($type));

        if (!empty($event->queryCriterions)) {
            $query->query = count($event->queryCriterions) > 1 ? new Criterion\LogicalAnd(
                $event->queryCriterions
            ) : reset($event->queryCriterions);
        }
        if (!empty($event->filtersCriterions)) {
            $query->filter = count($event->filtersCriterions) > 1 ? new Criterion\LogicalAnd(
                $event->filtersCriterions
            ) : reset($event->filtersCriterions);
        }
        if (!empty($event->aggregations)) {
            $query->aggregations = $event->aggregations;
        }

        $pagerFanta = new Pager($type, $searchType->getAdapter());
        $pagerFanta->setMaxPerPage((int) $requestedLimit);
        $pagerFanta->setHeadlineCount($pagerDefinition->getHeadlineCount());
        $pagerFanta->setDisablePagination($pagerDefinition->isPaginationDisabled());
        $pagerFanta->setCurrentPage((int) $requestedPage);

        return $pagerFanta;
    }
}
