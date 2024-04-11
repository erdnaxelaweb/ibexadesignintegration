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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use ErdnaxelaWeb\IbexaDesignIntegration\Helper\BreadcrumbGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Helper\LinkGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\ContentFieldsCollection;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\ContentConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as IbexaLocation;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Ibexa\HttpCache\Handler\TagHandler;

class ContentTransformer
{
    public function __construct(
        protected ContentConfigurationManager $contentConfigurationManager,
        protected LinkGenerator               $linkGenerator,
        protected BreadcrumbGenerator         $breadcrumbGenerator,
        protected FieldValueTransformer       $fieldValueTransformers,
        protected ContentService $contentService,
        protected LocationService $locationService,
        protected TagHandler $responseTagger
    ) {
    }

    public function lazyTransformContentFromLocationId(int $locationId): Content
    {
        $initializers = [
            'id' => function (Content $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerLocation->contentId;
            },
            'locationId' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $locationId
            ) {
                return $locationId;
            },
            'innerContent' => function (Content $instance, string $propertyName, ?string $propertyScope) {
                $content = $instance->innerLocation->getContent();
                $this->responseTagger->addContentTags([$content]);
                return $content;
            },
            'innerLocation' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $locationId
            ) {
                $this->responseTagger->addLocationTags([$locationId]);
                return $this->locationService->loadLocation($locationId);
            },
        ];

        $skippedProperties = ['locationId'];
        return $this->createLazyContent($initializers, $skippedProperties);
    }

    public function lazyTransformContentFromContentId(int $contentId): Content
    {
        $initializers = [
            'id' => function (Content $instance, string $propertyName, ?string $propertyScope) use ($contentId) {
                return $contentId;
            },
            'locationId' => function (Content $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerContent->contentInfo->mainLocationId;
            },
            'innerContent' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $contentId
            ) {
                $this->responseTagger->addContentTags([$contentId]);
                return $this->contentService->loadContent($contentId);
            },
            'innerLocation' => function (Content $instance, string $propertyName, ?string $propertyScope) {
                $location = $instance->innerContent->contentInfo->getMainLocation();
                $this->responseTagger->addLocationTags([$location->id]);
                return $location;
            },
        ];

        $skippedProperties = ['id'];
        return $this->createLazyContent($initializers, $skippedProperties);
    }

    public function transformContent(IbexaContent $ibexaContent, ?IbexaLocation $ibexaLocation = null): Content
    {
        if ($ibexaContent instanceof Content) {
            return $ibexaContent;
        }

        $initializers = [
            'id' => function (Content $instance, string $propertyName, ?string $propertyScope) use ($ibexaContent) {
                return $ibexaContent->id;
            },
            'locationId' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $ibexaLocation
            ) {
                return $ibexaLocation->id ?? $instance->innerContent->contentInfo->mainLocationId;
            },
            'innerContent' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $ibexaContent
            ) {
                $this->responseTagger->addContentTags([$ibexaContent->id]);
                return $ibexaContent;
            },
            'innerLocation' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $ibexaLocation
            ) {
                $location = $ibexaLocation ?? $instance->innerContent->contentInfo->getMainLocation();
                $this->responseTagger->addLocationTags([$location->id]);
                return $location;
            },
        ];
        $skippedProperties = ['id', 'locationId'];
        return $this->createLazyContent($initializers, $skippedProperties);
    }

    protected function createLazyContent(array $initializers, array $skippedProperties = []): Content
    {
        $initializers += [
            "\0*\0fields" => function (Content $instance, string $propertyName, ?string $propertyScope) {
                $contentType = $instance->getContentType();
                $contentConfiguration = $this->contentConfigurationManager->getConfiguration(
                    $contentType->identifier
                );
                return new ContentFieldsCollection(
                    $instance->innerContent,
                    $instance->getContentType(),
                    $contentConfiguration['fields'],
                    $this->fieldValueTransformers
                );
            },
            "name" => function (Content $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerContent->getName();
            },
            "type" => function (Content $instance, string $propertyName, ?string $propertyScope) {
                return $instance->getContentType()
->identifier;
            },
            "creationDate" => function (Content $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerContent->contentInfo->publishedDate;
            },
            "modificationDate" => function (Content $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerContent->contentInfo->modificationDate;
            },
            "url" => function (Content $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerLocation ? $this->linkGenerator->generateUrl(
                    UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
                    [
                        'locationId' => $instance->innerLocation->id,
                    ]
                ) : '';
            },
            "breadcrumb" => function (Content $instance, string $propertyName, ?string $propertyScope) {
                return $instance->innerLocation ?
                    $this->breadcrumbGenerator->generateLocationBreadcrumb($instance->innerLocation) :
                    new Breadcrumb();
            },
        ];

        return Content::createLazyGhost($initializers, $skippedProperties);
    }

    public function __invoke(IbexaContent $ibexaContent, ?IbexaLocation $ibexaLocation = null): Content
    {
        return $this->transformContent($ibexaContent, $ibexaLocation);
    }
}
