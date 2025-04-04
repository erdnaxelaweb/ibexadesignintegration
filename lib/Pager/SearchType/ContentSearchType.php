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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\ContentSearchAdapter;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends \ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\AbstractSearchType<Query>
 */
class ContentSearchType extends AbstractSearchType
{
    public function __construct(
        protected SearchService                 $searchService,
        protected ContentTransformer            $contentTransformer,
        PagerSearchFormBuilder $pagerSearchFormBuilder,
        PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder,
        string $searchFormName,
        PagerDefinition $pagerDefinition,
        Request $request,
        SearchData $defaultSearchData = new SearchData()
    ) {
        parent::__construct(
            $pagerSearchFormBuilder,
            $pagerActiveFiltersListBuilder,
            $searchFormName,
            $pagerDefinition,
            $request,
            $defaultSearchData
        );
    }

    protected function initializeQuery(): void
    {
        $this->query = new Query();
    }

    public function getAdapter(): PagerAdapterInterface
    {
        return new ContentSearchAdapter(
            $this->query,
            $this->searchService,
            $this->contentTransformer,
            [$this, 'getFiltersForm'],
            [$this, 'getActiveFilters']
        );
    }
}
