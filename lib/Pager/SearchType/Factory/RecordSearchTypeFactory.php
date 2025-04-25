<?php
declare( strict_types=1 );

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\RecordSearchType;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\SearchTypeInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\RecordSearchAdapter;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Symfony\Component\HttpFoundation\Request;

class RecordSearchTypeFactory implements SearchTypeFactoryInterface
{
    public function __construct(

        protected PagerSearchFormBuilder $pagerSearchFormBuilder,
        protected PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder,
    )
    {
    }

    public function __invoke( string          $searchFormName,
                              PagerDefinition $pagerDefinition,
                              Request         $request,
                              SearchData      $defaultSearchData = new SearchData()
    ): SearchTypeInterface
    {
        return new RecordSearchType(
            $this->pagerSearchFormBuilder,
            $this->pagerActiveFiltersListBuilder,
            $searchFormName,
            $pagerDefinition,
            $request,
            $defaultSearchData
        );
    }
}
