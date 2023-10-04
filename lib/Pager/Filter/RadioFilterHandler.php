<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter;

class RadioFilterHandler extends ChoiceFilterHandler
{
    protected function getFormOptions(): array
    {
        return [
            'expanded' => true,
            'multiple' => false,
        ];
    }
}
