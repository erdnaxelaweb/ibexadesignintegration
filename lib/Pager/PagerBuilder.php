<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager;

use ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchAdapter;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\PagerConfigurationManager;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;

class PagerBuilder
{
    public function __construct(
        protected PagerConfigurationManager $pagerConfigurationManager,
        protected PagerQueryBuilder         $pagerQueryBuilder,
        protected PagerSearchFormBuilder    $pagerSearchFormBuilder,
        protected SearchService             $searchService,
        protected RequestStack              $requestStack,
        protected ContentTransformer        $contentTransformer
    )
    {
    }

    public function build( Location $location, string $type )
    {
        $request = $this->requestStack->getCurrentRequest();

        $configuration = $this->pagerConfigurationManager->getConfiguration( $type );
        $searchData = SearchData::createFromRequest( $request->get('form', []) );

        $query = $this->pagerQueryBuilder->build( $location, $configuration, $searchData );

        $adapter = new SearchAdapter(
            $query,
            $this->searchService,
            $this->contentTransformer,
            function ( AggregationResultCollection $aggregationResultCollection ) use ( $configuration, $searchData ) {
                return $this->pagerSearchFormBuilder->build(
                    $configuration,
                    $aggregationResultCollection,
                    $searchData
                );
            }
        );
        $pagerFanta = new Pagerfanta( $adapter );
        $pagerFanta->setMaxPerPage( $configuration['maxPerPage'] );
        $pagerFanta->setCurrentPage( $request->get( 'page', 1 ) );
        return $pagerFanta;
    }
}
