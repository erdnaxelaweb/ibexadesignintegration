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

class LazyTransformer
{
    /**
     * @param callable(): mixed $initializer
     */
    public function __construct(
        protected $initializer
    ) {
    }

    public function __invoke(): mixed
    {
        return ($this->initializer)();
    }
}
