<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\SearchTypeInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Symfony\Component\HttpFoundation\Request;

interface SearchTypeFactoryInterface
{
    /**
     * @param string                                                          $searchFormName
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Definition\PagerDefinition $pagerDefinition
     * @param \Symfony\Component\HttpFoundation\Request|null                  $request
     * @param \ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData           $defaultSearchData
     * @param array<string, mixed>                                                           $context
     *
     * @return \ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\SearchTypeInterface
     */
    public function __invoke(
        string $searchFormName,
        PagerDefinition $pagerDefinition,
        ?Request $request,
        SearchData $defaultSearchData = new SearchData(),
        array $context = []
    ): SearchTypeInterface;
}
