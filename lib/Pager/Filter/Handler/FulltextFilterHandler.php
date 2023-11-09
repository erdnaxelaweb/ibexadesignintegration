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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

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
        return new Criterion\FullText($value);
    }

    public function getFakeFormType(): array
    {
        return [
            'type' => TextType::class,
        ];
    }
}
