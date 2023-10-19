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

class RadioFormHandler extends ChoiceFormHandler
{
    protected function getFormOptions(): array
    {
        return [
            'expanded' => true,
            'multiple' => false,
        ];
    }
}
