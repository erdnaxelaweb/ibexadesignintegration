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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\ContentSearchType;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\SearchTypeInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Ibexa\Contracts\Core\Repository\SearchService;
use Symfony\Component\HttpFoundation\Request;

class ContentSearchTypeFactory implements SearchTypeFactoryInterface
{
    public function __construct(
        protected SearchService                 $searchService,
        protected ContentTransformer            $contentTransformer,
        protected PagerSearchFormBuilder        $pagerSearchFormBuilder,
        protected PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder,
    ) {
    }

    public function __invoke(
        string $searchFormName,
        PagerDefinition $pagerDefinition,
        Request $request,
        SearchData $defaultSearchData = new SearchData()
    ): SearchTypeInterface {
        return new ContentSearchType(
            $this->searchService,
            $this->contentTransformer,
            $this->pagerSearchFormBuilder,
            $this->pagerActiveFiltersListBuilder,
            $searchFormName,
            $pagerDefinition,
            $request,
            $defaultSearchData
        );
    }
}
