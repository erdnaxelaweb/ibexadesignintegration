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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilterHandler implements FilterHandlerInterface
{
    public function getAggregation(string $filterName, array $options = []): ?Aggregation
    {
        return null;
    }

    protected function resolveOptions(array $options = []): array
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        return $optionsResolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
    }

    public function getValuesLabels(array $activeValues, FormBuilderInterface $formBuilder): array
    {
        return array_combine($activeValues, $activeValues);
    }
}
