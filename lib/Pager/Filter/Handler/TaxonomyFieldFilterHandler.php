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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Novactive\EzSolrSearchExtra\Query\Aggregation\TaxonomyRawTermAggregation;

class TaxonomyFieldFilterHandler extends CustomFieldFilterHandler
{
    public function getAggregation(string $filterName, array $options = []): ?Aggregation
    {
        $options = $this->resolveOptions($options);
        $aggregation = new TaxonomyRawTermAggregation($filterName, $options['field'], [$filterName]);
        $aggregation->setLimit($options['limit']);
        return $aggregation;
    }

    /**
     * @param \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry $entry
     */
    protected function getChoiceValue(ValueObject $entry): string
    {
        return $entry->getKey();
    }

    /**
     * @param \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry $entry
     */
    protected function getChoiceAttributes(ValueObject $entry): array
    {
        return [
            'class' => $entry->getIdentifier(),
        ];
    }
}
