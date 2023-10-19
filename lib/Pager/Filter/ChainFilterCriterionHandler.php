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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Criterion\FilterCriterionHandlerInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;

class ChainFilterCriterionHandler
{
    /**
     * @var FilterCriterionHandlerInterface[]
     */
    protected array $criterionsHandler;

    public function __construct(
        iterable $criterionsHandler
    ) {
        foreach ($criterionsHandler as $type => $criterionHandler) {
            $this->criterionsHandler[$type] = $criterionHandler;
        }
    }

    public function getCriterion(string $criterionType, string $filterName, string $field, $value): Criterion
    {
        $criterionHandler = $this->getCriterionHandler($criterionType);
        return $criterionHandler->getCriterion($filterName, $field, $value);
    }

    public function getAggregation(string $criterionType, string $filterName, string $field): Aggregation
    {
        $criterionHandler = $this->getCriterionHandler($criterionType);
        return $criterionHandler->getAggregation($filterName, $field);
    }

    private function getCriterionHandler(string $criterionType): FilterCriterionHandlerInterface
    {
        return $this->criterionsHandler[$criterionType];
    }

    public function getTypes(): array
    {
        return array_keys($this->criterionsHandler);
    }
}
