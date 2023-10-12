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

class CheckboxFilterHandler extends ChoiceFilterHandler
{
    protected function getFormOptions(): array
    {
        return [
            'expanded' => true,
            'multiple' => true,
        ];
    }
}
