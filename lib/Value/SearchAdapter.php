<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use Symfony\Component\Form\FormView;

class SearchAdapter extends ContentSearchAdapter
{
    public function __construct(
        Query $query,
        SearchService $searchService,
        ContentTransformer $contentTransformer,
        protected $filtersCallback,
        array $languageFilter = []
    )
    {
        parent::__construct( $query, $searchService, $languageFilter );
    }

    public function getSlice( $offset, $length )
    {
        $results = parent::getSlice( $offset, $length );
        $list = [];
        foreach ( $results as $result )
        {
            $list[] = [
                'contentId' => $result->id
            ];
        }
        return $list;
    }

    public function getFilters(): FormView
    {
        return ($this->filtersCallback)($this->getAggregations());
    }

}
