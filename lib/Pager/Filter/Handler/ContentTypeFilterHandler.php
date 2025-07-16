<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice\FilterChoice;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice\FilterChoiceInterface;
use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use ErdnaxelaWeb\StaticFakeDesign\Fake\FakerGenerator;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResultEntry;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeFilterHandler extends CustomFieldFilterHandler
{
    public function __construct(
        protected ContentTypeService $contentTypeService,
        FakerGenerator $fakerGenerator
    ) {
        parent::__construct($fakerGenerator);
    }

    public function getCriterion(string $filterName, mixed $value, DefinitionOptions $options, array $searchData): ?Criterion
    {
        $criterion = new Criterion\ContentTypeId($value);

        if ($options['multiple'] === false) {
            return $criterion;
        }
        return new FilterTag($filterName, $criterion);
    }

    public function getAggregation(string $filterName, DefinitionOptions $options, array $searchData): ?Aggregation
    {
        $sort = null;
        $requestedSort = $options->get('sort');
        if ($requestedSort && $requestedSort !== "label") {
            $sort = sprintf('%s %s', $requestedSort, $options->get('sort_direction'));
        }

        $aggregation = new RawTermAggregation(
            $filterName,
            'content_type_id_id',
            [$filterName],
            $sort
        );
        $aggregation->setLimit($options['limit']);
        return $aggregation;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->remove('field');
    }

    protected function getValueLabel(mixed $value): string
    {
        $contentType = $this->contentTypeService->loadContentType((int) $value);
        return $contentType->getName();
    }

    protected function buildChoiceFromAggregationResultEntry(
        RawTermAggregationResultEntry $entry,
        DefinitionOptions $options
    ): FilterChoiceInterface {
        return new FilterChoice(
            $this->getValueLabel($entry->getKey()),
            $entry->getKey(),
            $entry->getCount(),
            [],
            $options['choice_label_format']
        );
    }
}
