<?php

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\Handler\Choice;

interface FilterChoiceInterface
{
    public function getLabel(): string;

    public function getValue(): mixed;

    public function getAttr(): array;
}
