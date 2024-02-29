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

use ErdnaxelaWeb\StaticFakeDesign\Fake\FakerGenerator;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Novactive\EzSolrSearchExtra\Query\Aggregation\RawTermAggregation;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeFilterHandler extends CustomFieldFilterHandler
{
    public function __construct(
        protected ContentTypeService $contentTypeService,
        FakerGenerator $fakerGenerator
    ) {
        parent::__construct($fakerGenerator);
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
        $aggregation = new RawTermAggregation($filterName, 'content_type_id_id', [$filterName]);
        $aggregation->setLimit($options['limit']);
        return $aggregation;
    }

    protected function getChoiceLabel(ValueObject $entry): string
    {
        return $this->getValueLabel($entry->getKey());
    }

    protected function getValueLabel(string $value): string
    {
        $contentType = $this->contentTypeService->loadContentType($value);
        return $contentType->getName();
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->remove('field');
    }
}
