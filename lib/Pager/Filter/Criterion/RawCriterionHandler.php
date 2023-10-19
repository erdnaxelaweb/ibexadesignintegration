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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;

class RawCriterionHandler implements FilterCriterionHandlerInterface
{
    public function getCriterion(string $filterName, string $field, $value): Criterion
    {
        $operator = is_array($value) ? Operator::IN : Operator::EQ;
        $criterion = new CustomField($field, $operator, $value);
        return new FilterTag($filterName, $criterion);
    }

    public function getAggregation(string $filterName, string $field): RawTermAggregation
    {
        return new RawTermAggregation($filterName, $field, [$filterName]);
    }
}
