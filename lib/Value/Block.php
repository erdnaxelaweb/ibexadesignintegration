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

use DateTime;
use ErdnaxelaWeb\StaticFakeDesign\Value\Block as BaseBlock;
use ErdnaxelaWeb\StaticFakeDesign\Value\BlockAttributesCollection;
use Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue;

class Block extends BaseBlock
{
    public function __construct(
        int $id,
        string $name,
        string $type,
        string $view,
        ?string $class,
        ?string $style,
        ?DateTime $since,
        ?DateTime $till,
        bool $isVisible,
        BlockAttributesCollection $attributes,
        public readonly int $contentId,
        public readonly ?int $locationId,
        public readonly int $versionNo,
        public readonly string $languageCode,
        public readonly BlockValue $innerValue
    ) {
        parent::__construct(
            $id,
            $name,
            $type,
            $view,
            $class,
            $style,
            $since,
            $till,
            $isVisible,
            $attributes
        );
    }

    public function getContentId(): int
    {
        return $this->getPropertyValue('contentId');
    }

    public function getLocationId(): ?int
    {
        return $this->getPropertyValue('locationId');
    }

    public function getVersionNo(): int
    {
        return $this->getPropertyValue('versionNo');
    }

    public function getLanguageCode(): string
    {
        return $this->getPropertyValue('languageCode');
    }

    public function getInnerValue(): BlockValue
    {
        return $this->getPropertyValue('innerValue');
    }
}
