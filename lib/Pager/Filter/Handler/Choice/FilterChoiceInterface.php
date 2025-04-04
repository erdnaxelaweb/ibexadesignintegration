<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice;

interface FilterChoiceInterface
{
    public function getLabel(): string;

    public function getValue(): mixed;

    /**
     * @return array<string, mixed>
     */
    public function getAttr(): array;

    public function getCount(): int;
}
