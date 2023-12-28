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
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Novactive\EzSolrSearchExtra\Query\Aggregation\TaxonomyRawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountyFieldFilterHandler extends CustomFieldFilterHandler
{
    public function getAggregation(string $filterName, array $options = []): ?Aggregation
    {
        $options = $this->resolveOptions($options);
        return new TaxonomyRawTermAggregation($filterName, $options['field'], [$filterName]);
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

    protected function getChoices(?AggregationResult $aggregationResult = null, ?array $excludeTags = null): array
    {
        $choices = [];
        if ($aggregationResult) {
            foreach ($aggregationResult->getEntries() as $entry) {
                if (is_array($excludeTags) && in_array($entry->getIdentifier(), $excludeTags)) {
                    continue;
                }
                $choices[] = $entry;
            }
        }
        return $choices;
    }

    public function getCriterion(string $filterName, $value, array $options = []): Criterion
    {
        $options = $this->resolveOptions($options);
        $operator = is_array($value) ? Operator::IN : Operator::EQ;
        if (isset($options['excludeTags']) && is_array($options['excludeTags'])) {
            foreach ($options['excludeTags'] as $excludeTag) {
                $excludeTaxonomy = $this->taxonomyEntryRepository->findOneBy(['identifier' => $excludeTag]);
                if ($excludeTaxonomy) {
                    $value[] = (string) $excludeTaxonomy->getId();
                }
            }
        }

        $criterion = new CustomField($options['field'], $operator, $value);

        if ($options['multiple'] === false) {
            return $criterion;
        }

        return new FilterTag($filterName, $criterion);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->define('excludeTags')
            ->allowedTypes('array');
    }
}
