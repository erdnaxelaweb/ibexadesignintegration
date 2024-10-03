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

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\MultipleFieldsFullText;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FulltextFilterHandler extends AbstractFilterHandler
{
    public function addForm(
        FormBuilderInterface $formBuilder,
        string               $filterName,
        ?AggregationResult   $aggregationResult = null,
        array                $options = []
    ): void {
        $options = [];
        $options['label'] = sprintf('searchform.%s', $filterName);
        $options['block_prefix'] = "filter_$filterName";
        $options['required'] = false;
        $formBuilder->add($filterName, TextType::class, $options);
    }

    public function getCriterion(string $filterName, $value, array $options = []): Criterion
    {
        $options = $this->resolveOptions($options);
        return new MultipleFieldsFullText($value, $options);
    }

    public function getFakeFormType(): array
    {
        return [
            'type' => TextType::class,
        ];
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver->define('fuzziness')
            ->default(1)
            ->allowedTypes('int');

        $optionsResolver->define('boost')
            ->default([])
            ->allowedTypes('array');

        $optionsResolver->define('metaBoost')
            ->default([])
            ->allowedTypes('array');

        $optionsResolver->define('boostQueries')
            ->default([])
            ->allowedTypes('array');

        $optionsResolver->define('boostFunctions')
            ->default([])
            ->allowedTypes('array');

        $optionsResolver->define('boostPublishDate')
            ->default(false)
            ->allowedTypes('boolean');
    }
}
