<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Component;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\IbexaComponent;
use ErdnaxelaWeb\StaticFakeDesign\Component\ComponentBuilder as BaseComponentBuilder;
use ErdnaxelaWeb\StaticFakeDesign\Value\Component;

class ComponentBuilder extends BaseComponentBuilder
{
    protected function instanciate(array $componentArgs): Component
    {
        return new IbexaComponent(...$componentArgs);
    }
}
