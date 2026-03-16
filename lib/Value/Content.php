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
use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaApiContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as IbexaApiLocation;

class Content extends AbstractContent implements ContentInterface
{
    /**
     * @param string[]                                                         $languageCodes
     */
    public function __construct(
        IbexaApiContent $innerContent,
        ?IbexaApiLocation $innerLocation,
        int $id,
        ?int $locationId,
        string $name,
        string $type,
        ?DateTime $creationDate,
        ?DateTime $modificationDate,
        ContentFieldsCollection $fields,
        protected readonly array                  $languageCodes,
        protected readonly string                    $mainLanguageCode,
        protected readonly bool                    $alwaysAvailable,
        protected readonly bool                    $hidden,
        protected readonly string $url,
        protected readonly Breadcrumb $breadcrumb,
        protected readonly ?Content $parent
    ) {
        parent::__construct(
            $innerContent,
            $innerLocation,
            $id,
            $locationId,
            $name,
            $type,
            $creationDate,
            $modificationDate,
            $fields
        );
    }

    /**
     * @return string[]
     */
    public function getLanguageCodes(): array
    {
        return $this->getPropertyValue('languageCodes');
    }

    public function getMainLanguageCode(): string
    {
        return $this->getPropertyValue('mainLanguageCode');
    }

    public function isAlwaysAvailable(): bool
    {
        return $this->getPropertyValue('alwaysAvailable');
    }

    public function isHidden(): bool
    {
        return $this->getPropertyValue('hidden');
    }

    public function getUrl(): string
    {
        return $this->getPropertyValue('url');
    }

    public function getBreadcrumb(): Breadcrumb
    {
        return $this->getPropertyValue('breadcrumb');
    }

    public function getParent(): ?Content
    {
        return $this->getPropertyValue('parent');
    }
}
