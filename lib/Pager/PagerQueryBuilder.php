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

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\FilterHandlerInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\SearchData;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;

class PagerQueryBuilder
{
    /** @var FilterHandlerInterface[] */
    protected array $filtersHandler;

    public function __construct(
        iterable $filtersHandler
    )
    {
        foreach ( $filtersHandler as $type => $filterHandler )
        {
            $this->filtersHandler[$type] = $filterHandler;
        }
    }

    public function build( Location $location, array $configuration, SearchData $searchData )
    {
        $query = new Query();
        $filters = [
            new Criterion\ParentLocationId( $location->id )
        ];
        $aggregations = [];

        foreach ( $configuration['filters'] as $filterName => $filter )
        {
            $field = $filter['field'];

            $filterHandler = $this->filtersHandler[$filter['type']];
            if ( isset( $searchData->filters[$filterName] ) && !empty($searchData->filters[$filterName]) )
            {
                $filters[] = $filterHandler->getCriterion(
                    $filterName,
                    $field,
                    $searchData->filters[$filterName]
                );
            }
            $aggregations[] = $filterHandler->getAggregation( $filterName, $field );
        }

        $query->filter = new Criterion\LogicalAnd( $filters );
        $query->aggregations = $aggregations;

        return $query;
    }
}
