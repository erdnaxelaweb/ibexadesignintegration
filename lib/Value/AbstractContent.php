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
use ErdnaxelaWeb\StaticFakeDesign\LazyLoading\LazyObjectTrait;
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaApiContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as IbexaApiLocation;
use Ibexa\Contracts\Core\Repository\Values\Content\Thumbnail;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo as APIVersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;

/**
 * @property-read IbexaApiContent $innerContent
 * @property-read ?IbexaApiLocation $innerLocation
 * @property-read int $id
 * @property-read ?int $locationId
 * @property-read string $name
 * @property-read string $type
 * @property-read ?DateTime $creationDate
 * @property-read ?DateTime $modificationDate
 * @property-read ContentFieldsCollection $fields
 */
class AbstractContent extends IbexaApiContent
{
    use LazyObjectTrait {
        LazyObjectTrait::__get as lazyGet;
        LazyObjectTrait::__isset as lazyIsset;
    }

    public function __construct(
        protected readonly IbexaApiContent $innerContent,
        protected readonly ?IbexaApiLocation $innerLocation,
        protected readonly int $id,
        protected readonly ?int $locationId,
        protected readonly string $name,
        protected readonly string $type,
        protected readonly ?DateTime $creationDate,
        protected readonly ?DateTime $modificationDate,
        protected ContentFieldsCollection $fields
    ) {
    }

    /**
     * @param array<mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return call_user_func_array([$this->getInnerContent(), $name], $arguments);
    }

    /**
     * @param string $property
     */
    public function __get($property): mixed
    {
        return match ($property) {
            'versionInfo' => $this->getVersionInfo(),
            'contentInfo' => $this->getVersionInfo()
                ->getContentInfo(),
            'thumbnail' => $this->getThumbnail(),
            default => $this->lazyGet($property),
        };
    }

    /**
     * @param string $property
     */
    public function __isset($property): bool
    {
        if ($property === 'contentInfo') {
            return true;
        }

        return $this->lazyIsset($property);
    }

    public function getInnerContent(): IbexaApiContent
    {
        return $this->getPropertyValue('innerContent');
    }

    public function getInnerLocation(): ?IbexaApiLocation
    {
        return $this->getPropertyValue('innerLocation');
    }

    public function getLocationId(): ?int
    {
        return $this->getPropertyValue('locationId');
    }

    public function getId(): int
    {
        return $this->getPropertyValue('id');
    }

    public function getName(?string $languageCode = null): string
    {
        return $this->getPropertyValue('name');
    }

    public function getType(): string
    {
        return $this->getPropertyValue('type');
    }

    public function getCreationDate(): DateTime
    {
        return $this->getPropertyValue('creationDate');
    }

    public function getModificationDate(): DateTime
    {
        return $this->getPropertyValue('modificationDate');
    }

    public function getFields(): ContentFieldsCollection
    {
        return $this->getPropertyValue('fields');
    }

    public function getThumbnail(): ?Thumbnail
    {
        return $this->getInnerContent()->getThumbnail();
    }

    public function getVersionInfo(): APIVersionInfo
    {
        return $this->getInnerContent()->getVersionInfo();
    }

    public function getContentType(): ContentType
    {
        return $this->getInnerContent()->getContentType();
    }

    public function getFieldValue(string $fieldDefIdentifier, ?string $languageCode = null): ?Value
    {
        return $this->getInnerContent()->getFieldValue($fieldDefIdentifier, $languageCode);
    }

    public function getFieldsByLanguage(?string $languageCode = null): iterable
    {
        return $this->getInnerContent()->getFieldsByLanguage($languageCode);
    }

    public function getField(string $fieldDefIdentifier, ?string $languageCode = null): ?Field
    {
        return $this->getInnerContent()->getField($fieldDefIdentifier, $languageCode);
    }

    public function getDefaultLanguageCode(): string
    {
        return $this->getInnerContent()->getDefaultLanguageCode();
    }

    protected function getProperties($dynamicProperties = ['id', 'contentInfo']): array
    {
        return $this->getInnerContent()->getProperties($dynamicProperties);
    }
}
