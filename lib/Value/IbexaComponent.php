<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use ErdnaxelaWeb\StaticFakeDesign\Value\Component;

class IbexaComponent extends Component
{
    public function getTemplateName(): string
    {
        $templateName = parent::getTemplateName();
        return preg_replace('#@[^/]+/(.*)#', '@ibexadesign/$1', $templateName);
    }
}
