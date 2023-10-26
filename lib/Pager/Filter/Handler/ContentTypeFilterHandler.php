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

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeFilterHandler extends AbstractFilterHandler
{
    public function __construct(
        protected ContentTypeService $contentTypeService
    ) {
    }

    public function getCriterion(string $filterName, $value, array $options = []): Criterion
    {
        $options = $this->resolveOptions($options);
        $criterion = new Criterion\ContentTypeId($value);

        if ($options['multiple'] === false) {
            return $criterion;
        }
        return new FilterTag($filterName, $criterion);
    }

    public function getAggregation(string $filterName, array $options = []): ?Aggregation
    {
        return new RawTermAggregation($filterName, 'content_type_id_id', [$filterName]);
    }

    /**
     * @param \Novactive\EzSolrSearchExtra\Search\AggregationResult\RawTermAggregationResult $aggregationResult
     */
    public function addForm(
        FormBuilderInterface $formBuilder,
        string               $filterName,
        ?AggregationResult   $aggregationResult = null,
        array                $options = []
    ): void {
        $options = $this->resolveOptions($options);

        $formOptions['label'] = sprintf('searchform.%s', $filterName);
        $formOptions['required'] = false;
        $formOptions['multiple'] = $options['multiple'];
        $formOptions['expanded'] = $options['expanded'];
        $formOptions['choices'] = [];
        if ($aggregationResult) {
            foreach ($aggregationResult->getEntries() as $entry) {
                $contentType = $this->contentTypeService->loadContentType($entry->getKey());
                $formOptions['choices'][$contentType->getName()] = $contentType->id;
            }
        }
        $formBuilder->add($filterName, ChoiceType::class, $formOptions);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->define('multiple')
            ->default(false)
            ->allowedTypes('bool');
        $optionsResolver->define('expanded')
            ->default(false)
            ->allowedTypes('bool');
    }
}
