<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DocumentDefinition;
use ErdnaxelaWeb\StaticFakeDesign\Value\Document;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SpellcheckResult;
use Novactive\EzSolrSearchExtra\Query\DocumentQuery;
use Novactive\EzSolrSearchExtra\Repository\DocumentSearchService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\VarExporter\Instantiator;

class DocumentSearchAdapter implements PagerAdapterInterface
{
    private ?AggregationResultCollection $aggregations = null;
    private ?float $time = null;
    private ?bool $timedOut = null;
    private ?float $maxScore = null;
    private ?int $totalCount = null;
    private ?SpellcheckResult $spellcheck = null;
    private ?FormInterface $filtersForm = null;

    /**
     * @param callable(AggregationResultCollection): FormInterface                $filtersCallback
     * @param callable(FormInterface): \Knp\Menu\ItemInterface[]                  $activeFiltersCallback
     * @param array<string, mixed>|array<int, string>                             $languageFilter
     */
    public function __construct(
        protected DocumentQuery         $query,
        protected DocumentSearchService $searchService,
        protected $filtersCallback,
        protected $activeFiltersCallback,
        protected array                 $languageFilter = [],
        protected DefinitionManager $definitionManager
    ) {
    }

    public function getNbResults(): ?int
    {
        if (isset($this->totalCount)) {
            return $this->totalCount;
        }

        $countQuery = clone $this->query;
        $countQuery->limit = 0;
        // Skip facets/aggregations & spellcheck computing
        $countQuery->facetBuilders = [];
        $countQuery->aggregations = [];
        $countQuery->spellcheck = null;

        $searchResults = $this->executeQuery(
            $this->searchService,
            $countQuery,
            $this->languageFilter
        );

        return $this->totalCount = $searchResults->totalCount;
    }

    public function getSlice($offset, $length): array
    {
        $query = clone $this->query;
        $query->offset = $offset;
        $query->limit = $length;
        $query->performCount = false;

        $searchResult = $this->executeQuery(
            $this->searchService,
            $query,
            $this->languageFilter
        );

        $this->aggregations = $searchResult->getAggregations();
        $this->time = $searchResult->time;
        $this->timedOut = $searchResult->timedOut;
        $this->maxScore = $searchResult->maxScore;
        $this->spellcheck = $searchResult->getSpellcheck();

        // Set count for further use if returned by search engine despite !performCount (Solr, ES)
        if (!isset($this->totalCount) && isset($searchResult->totalCount)) {
            $this->totalCount = $searchResult->totalCount;
        }

        $searchHits = $searchResult->searchHits;
        $list = [];

        foreach ($searchHits as $key => $searchHit) {
            $result = json_decode(json_encode($searchHit->valueObject), true);
            $documentType = $result['type_s'];
            $pagerDefinition = $this->definitionManager->getDefinition(DocumentDefinition::class, $documentType);
            $list[$key] = Instantiator::instantiate(Document::class, $result);
            foreach ($pagerDefinition->getFields() as $field => $value) {
                if (!array_key_exists($field, $result)) {
                    continue;
                }
                $list[$key]->fields[$field] =  $result[$field];
            }
        }

        return $list;
    }

    public function getFilters(): FormView
    {
        return $this->getFiltersForm()
                    ->createView();
    }

    public function getActiveFilters(): array
    {
        return call_user_func($this->activeFiltersCallback, $this->getFiltersForm());
    }

    public function getFiltersForm(): FormInterface
    {
        if (!$this->filtersForm) {
            $this->filtersForm = call_user_func($this->filtersCallback, $this->getAggregations());
        }
        return $this->filtersForm;
    }

    public function getAggregations(): AggregationResultCollection
    {
        if ($this->aggregations === null) {
            $aggregationQuery = clone $this->query;
            $aggregationQuery->offset = 0;
            $aggregationQuery->limit = 0;
            $aggregationQuery->spellcheck = null;

            $searchResults = $this->executeQuery(
                $this->searchService,
                $aggregationQuery,
                $this->languageFilter
            );

            $this->aggregations = $searchResults->aggregations;
        }

        return $this->aggregations;
    }

    public function getSpellcheck(): ?SpellcheckResult
    {
        if ($this->spellcheck === null) {
            $spellcheckQuery = clone $this->query;
            $spellcheckQuery->offset = 0;
            $spellcheckQuery->limit = 0;
            $spellcheckQuery->aggregations = [];

            $searchResults = $this->executeQuery(
                $this->searchService,
                $spellcheckQuery,
                $this->languageFilter
            );

            $this->spellcheck = $searchResults->spellcheck;
        }

        return $this->spellcheck;
    }

    public function getTime(): ?float
    {
        return $this->time;
    }

    public function getTimedOut(): ?bool
    {
        return $this->timedOut;
    }

    public function getMaxScore(): ?float
    {
        return $this->maxScore;
    }

    /**
     * @param array<string, mixed>|array<int, string> $languageFilter
     */
    protected function executeQuery(
        DocumentSearchService $searchService,
        DocumentQuery         $query,
        array                 $languageFilter
    ): SearchResult {
        return $searchService->findDocument($query, $languageFilter);
    }
}
