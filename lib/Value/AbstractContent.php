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
use ErdnaxelaWeb\StaticFakeDesign\Value\ContentFieldsCollection;
use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaApiContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as IbexaApiLocation;
use Ibexa\Contracts\Core\Repository\Values\Content\Thumbnail;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo as APIVersionInfo;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Core\Repository\Values\Content\Content as IbexaContent;
use Symfony\Component\VarExporter\LazyGhostTrait;

/**
 * @property-read ContentFieldsCollection $fields
 */
class AbstractContent extends IbexaContent
{
    use LazyGhostTrait {
        LazyGhostTrait::__get as lazyGet;
        LazyGhostTrait::__isset as lazyIsset;
    }

    public function __construct(
        public readonly IbexaApiContent $innerContent,
        public readonly ?IbexaApiLocation $innerLocation,
        public readonly int $id,
        public readonly ?int $locationId,
        public readonly string $name,
        public readonly string $type,
        public readonly ?DateTime $creationDate,
        public readonly ?DateTime $modificationDate,
        ContentFieldsCollection $fields
    ) {
        $this->fields = $fields;
    }

    /**
     * @param array<mixed>  $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return call_user_func_array([$this->innerContent, $name], $arguments);
    }

    /**
     * @param string $name
     */
    public function __get($name): mixed
    {
        switch ($name) {
            case 'versionInfo':
                return $this->getVersionInfo();

            case 'contentInfo':
                return $this->getVersionInfo()
                    ->getContentInfo();

            case 'thumbnail':
                return $this->getThumbnail();
        }

        return $this->lazyGet($name);
    }

    /**
     * @param string $name
     */
    public function __isset($name): bool
    {
        if ($name === 'contentInfo') {
            return true;
        }

        return $this->lazyIsset($name);
    }

    public function getThumbnail(): ?Thumbnail
    {
        return $this->innerContent->getThumbnail();
    }

    public function getVersionInfo(): APIVersionInfo
    {
        return $this->innerContent->getVersionInfo();
    }

    public function getContentType(): ContentType
    {
        return $this->innerContent->getContentType();
    }

    public function getFieldValue(string $fieldDefIdentifier, ?string $languageCode = null): ?Value
    {
        return $this->innerContent->getFieldValue($fieldDefIdentifier, $languageCode);
    }

    public function getFields(): iterable
    {
        return $this->innerContent->getFields();
    }

    public function getFieldsByLanguage(?string $languageCode = null): iterable
    {
        return $this->innerContent->getFieldsByLanguage($languageCode);
    }

    public function getField(string $fieldDefIdentifier, ?string $languageCode = null): ?Field
    {
        return $this->innerContent->getField($fieldDefIdentifier, $languageCode);
    }

    public function getDefaultLanguageCode(): string
    {
        return $this->innerContent->getDefaultLanguageCode();
    }

    public function getLazyObjectState(): \Symfony\Component\VarExporter\Internal\LazyObjectState
    {
        return $this->lazyObjectState;
    }

    protected function getProperties($dynamicProperties = ['id', 'contentInfo']): array
    {
        return $this->innerContent->getProperties($dynamicProperties);
    }
}
