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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Form;

use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Symfony\Component\Form\FormBuilderInterface;

interface FilterFormHandlerInterface
{
    public function addForm(
        FormBuilderInterface $formBuilder,
        string $filterName,
        string $field,
        AggregationResult $aggregationResult
    ): void;
}
