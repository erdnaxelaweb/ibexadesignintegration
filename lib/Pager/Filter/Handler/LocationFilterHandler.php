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

use ErdnaxelaWeb\StaticFakeDesign\Definition\DefinitionOptions;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use InvalidArgumentException;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\FilterTag;
use Novactive\EzSolrSearchExtra\Query\Content\Criterion\LocationDistance;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationFilterHandler extends AbstractFilterHandler
{
    public function addForm(
        FormBuilderInterface $formBuilder,
        string               $filterName,
        DefinitionOptions    $options,
        ?AggregationResult   $aggregationResult = null,
    ): void {
        $options = [];
        $options['label'] = sprintf('searchform.%s', $filterName);
        $options['block_prefix'] = "filter_$filterName";
        $options['required'] = false;
        $options['compound'] = true;
        $group = $formBuilder->create($filterName, FormType::class, $options);

        $group->add('latitude', TextType::class, [
            'label' => sprintf('searchform.%s.latitude', $filterName),
        ]);
        $group->add('longitude', TextType::class, [
            'label' => sprintf('searchform.%s.longitude', $filterName),
        ]);
        $group->add('operator', ChoiceType::class, [
            'label' => sprintf('searchform.%s.operator', $filterName),
            'choices' => [
                Operator::IN,
                Operator::EQ,
                Operator::GT,
                Operator::GTE,
                Operator::LT,
                Operator::LTE,
                Operator::BETWEEN,
            ],
        ]);
        $group->add('distance', IntegerType::class, [
            'label' => sprintf('searchform.%s.distance', $filterName),
        ]);

        $formBuilder->add($group);
    }

    public function getCriterion(string $filterName, mixed $value, DefinitionOptions $options): Criterion
    {
        $defaultOperator = $options['default_operator'];
        $operator = $value['operator'] ?? $defaultOperator;
        if (!$operator) {
            throw new InvalidArgumentException('You must provide a valid operator');
        }

        $defaultDistance = $options['default_distance'];
        $distance = $value['distance'] ?? $defaultDistance;
        if (!$distance) {
            throw new InvalidArgumentException('You must provide a valid distance');
        }

        if (!$value['latitude']) {
            throw new InvalidArgumentException('You must provide a valid latitude');
        }

        if (!$value['longitude']) {
            throw new InvalidArgumentException('You must provide a valid longitude');
        }

        $criterion = new LocationDistance(
            $options['field'],
            $operator,
            $distance,
            (float) $value['latitude'],
            (float) $value['longitude'],
        );
        return new FilterTag($filterName, $criterion);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);
        $optionsResolver->define('field')
                        ->required()
                        ->allowedTypes('string');

        $optionsResolver->define('default_distance')
                        ->default(null)
                        ->allowedTypes('int', 'float', 'null');

        $optionsResolver->define('default_operator')
                        ->default(null)
                        ->allowedTypes('string', 'null')
                        ->allowedValues(
                            null,
                            Operator::IN,
                            Operator::EQ,
                            Operator::GT,
                            Operator::GTE,
                            Operator::LT,
                            Operator::LTE,
                            Operator::BETWEEN
                        );
    }

    public function getFakeFormType(): array
    {
        return [
            'type' => FormType::class,
        ];
    }

    public function getValuesLabels($activeValues, FormInterface $formBuilder): mixed
    {
        return sprintf('%s,%s', $activeValues['latitude'], $activeValues['longitude']);
    }
}
