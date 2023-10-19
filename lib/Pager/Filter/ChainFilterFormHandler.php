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

use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Symfony\Component\Form\FormBuilderInterface;

class ChainFilterFormHandler
{
    /**
     * @var \ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Form\FilterFormHandlerInterface[]
     */
    protected array $formsHandler;

    public function __construct(
        iterable $formsHandler,
    ) {
        foreach ($formsHandler as $type => $formHandler) {
            $this->formsHandler[$type] = $formHandler;
        }
    }

    public function addForm(
        FormBuilderInterface $formBuilder,
        string $formType,
        string $filterName,
        string $field,
        AggregationResult $aggregationResult
    ): void {
        $formHandler = $this->formsHandler[$formType];
        $formHandler->addForm($formBuilder, $filterName, $field, $aggregationResult);
    }

    public function getTypes(): array
    {
        return array_keys($this->formsHandler);
    }
}
