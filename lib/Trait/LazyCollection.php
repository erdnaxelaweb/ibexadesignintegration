<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Trait;

use ErdnaxelaWeb\IbexaDesignIntegration\Value\LazyTransformer;

trait LazyCollection
{
    public function get(string|int $key): mixed
    {
        $value = parent::get($key);
        if ($value instanceof LazyTransformer) {
            $value = ($value)();
            $this->set($key, $value);
        }
        return $value;
    }
}
