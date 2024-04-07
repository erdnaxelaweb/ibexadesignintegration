<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

declare(strict_types=1);

namespace ErdnaxelaWeb\IbexaDesignIntegration\Value;

use DateTime;
use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
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

class Content extends IbexaContent
{
    use LazyGhostTrait {
        LazyGhostTrait::__get as lazyGet;
        LazyGhostTrait::__isset as lazyIsset;
    }

    public function __construct(
        public readonly IbexaApiContent $innerContent,
        public readonly ?IbexaApiLocation $innerLocation,
        public readonly int $id,
        public readonly int $locationId,
        public readonly string $name,
        public readonly string $type,
        public readonly DateTime $creationDate,
        public readonly DateTime $modificationDate,
        ContentFieldsCollection  $fields,
        public readonly string $url,
        public readonly Breadcrumb  $breadcrumb
    ) {
        $this->fields = $fields;
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->innerContent, $name], $arguments);
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

    protected function getProperties($dynamicProperties = ['id', 'contentInfo'])
    {
        return $this->innerContent->getProperties($dynamicProperties);
    }

    public function __get($property)
    {
        switch ($property) {
            case 'versionInfo':
                return $this->getVersionInfo();

            case 'contentInfo':
                return $this->getVersionInfo()
                    ->getContentInfo();

            case 'thumbnail':
                return $this->getThumbnail();
        }

        return $this->lazyGet($property);
    }

    public function __isset($property)
    {
        if ($property === 'contentInfo') {
            return true;
        }

        return $this->lazyIsset($property);
    }

    public function getLazyObjectState()
    {
        return $this->lazyObjectState;
    }
}
