<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer;

use DateTime;
use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Helper\BreadcrumbGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Helper\LinkGenerator;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\Content;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\ContentFieldsCollection;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
use ErdnaxelaWeb\StaticFakeDesign\Value\LazyValue;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content as IbexaContent;
use Ibexa\Contracts\Core\Repository\Values\Content\Location as IbexaLocation;
use Ibexa\Core\MVC\Symfony\Routing\UrlAliasRouter;
use Ibexa\HttpCache\Handler\TagHandler;
use Symfony\Component\VarExporter\Instantiator;

class ContentTransformer
{
    public function __construct(
        protected DefinitionManager $definitionManager,
        protected LinkGenerator $linkGenerator,
        protected BreadcrumbGenerator $breadcrumbGenerator,
        protected FieldValueTransformer $fieldValueTransformers,
        protected ContentService $contentService,
        protected LocationService $locationService,
        protected TagHandler $responseTagger
    ) {
    }

    public function __invoke(IbexaContent $ibexaContent, ?IbexaLocation $ibexaLocation = null): Content
    {
        return $this->transformContent($ibexaContent, $ibexaLocation);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Symfony\Component\VarExporter\Exception\ExceptionInterface
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function lazyTransformContentFromLocationRemoteId(string $remoteId): Content
    {
        $location = $this->locationService->loadLocationByRemoteId($remoteId);
        $initializers = [
            'id' => fn(Content $instance, string $propertyName, ?string $propertyScope): int => $instance->innerLocation->contentId,
            'locationId' => fn(Content $instance, string $propertyName, ?string $propertyScope): int => $instance->innerLocation->id,
            'innerContent' => function (Content $instance, string $propertyName, ?string $propertyScope): IbexaContent {
                $content = $instance->innerLocation->getContent();
                $this->responseTagger->addContentTags([$content->id]);
                return $content;
            },
            'innerLocation' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $location
            ): IbexaLocation {
                $this->responseTagger->addLocationTags([$location->id]);
                return $location;
            },
        ];

        $instance = Instantiator::instantiate(Content::class);
        $skippedProperties = [];
        return $this->createLazyContent($initializers, $skippedProperties, $instance);
    }

    public function lazyTransformContentFromLocationId(int $locationId): Content
    {
        $initializers = [
            'id' => fn(Content $instance, string $propertyName, ?string $propertyScope): int => $instance->innerLocation->contentId,
            'innerContent' => function (Content $instance, string $propertyName, ?string $propertyScope): IbexaContent {
                $content = $instance->innerLocation->getContent();
                $this->responseTagger->addContentTags([$content->id]);
                return $content;
            },
            'innerLocation' => function (Content $instance, string $propertyName, ?string $propertyScope): IbexaLocation {
                $this->responseTagger->addLocationTags([$instance->locationId]);
                return $this->locationService->loadLocation($instance->locationId);
            },
        ];

        $instance = Instantiator::instantiate(Content::class, [
            'locationId' => $locationId,
        ]);
        $skippedProperties = [
            'locationId' => true,
        ];
        return $this->createLazyContent($initializers, $skippedProperties, $instance);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Symfony\Component\VarExporter\Exception\ExceptionInterface
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function lazyTransformContentFromContentRemoteId(string $remoteId): Content
    {
        $content = $this->contentService->loadContentByRemoteId($remoteId);
        $initializers = [
            'id' => fn(Content $instance, string $propertyName, ?string $propertyScope): int => $instance->innerContent->id,
            'locationId' => fn(Content $instance, string $propertyName, ?string $propertyScope): int => $instance->innerContent->contentInfo->mainLocationId,
            'innerContent' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $content
            ): IbexaContent {
                $this->responseTagger->addContentTags([$content->id]);
                return $content;
            },
            'innerLocation' => function (
                Content $instance,
                string $propertyName,
                ?string $propertyScope
            ): IbexaLocation {
                $location = $instance->innerContent->contentInfo->getMainLocation();
                if ($location) {
                    $this->responseTagger->addLocationTags([$location->id]);
                }
                return $location;
            },
        ];

        $instance = Instantiator::instantiate(Content::class);
        $skippedProperties = [];
        return $this->createLazyContent($initializers, $skippedProperties, $instance);
    }

    public function lazyTransformContentFromContentId(int $contentId): Content
    {
        $initializers = [
            'locationId' => fn(Content $instance, string $propertyName, ?string $propertyScope): int => $instance->innerContent->contentInfo->mainLocationId,
            'innerContent' => function (Content $instance, string $propertyName, ?string $propertyScope): IbexaContent {
                $this->responseTagger->addContentTags([$instance->id]);
                return $this->contentService->loadContent($instance->id);
            },
            'innerLocation' => function (
                Content $instance,
                string $propertyName,
                ?string $propertyScope
            ): IbexaLocation {
                $location = $instance->innerContent->contentInfo->getMainLocation();
                if ($location) {
                    $this->responseTagger->addLocationTags([$location->id]);
                }
                return $location;
            },
        ];

        $instance = Instantiator::instantiate(Content::class, [
            'id' => $contentId,
        ]);
        $skippedProperties = [
            'id' => true,
        ];
        return $this->createLazyContent($initializers, $skippedProperties, $instance);
    }

    public function transformContent(IbexaContent $ibexaContent, ?IbexaLocation $ibexaLocation = null): Content
    {
        if ($ibexaContent instanceof Content) {
            return $ibexaContent;
        }

        $initializers = [
            'innerContent' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $ibexaContent
            ): IbexaContent {
                $this->responseTagger->addContentTags([$ibexaContent->id]);
                return $ibexaContent;
            },
            'innerLocation' => function (Content $instance, string $propertyName, ?string $propertyScope) use (
                $ibexaLocation
            ): IbexaLocation {
                $location = $ibexaLocation ?? $instance->innerContent->contentInfo->getMainLocation();
                if ($location) {
                    $this->responseTagger->addLocationTags([$location->id]);
                }
                return $location;
            },
        ];
        $instance = Instantiator::instantiate(Content::class, [
            'id' => $ibexaContent->id,
            'locationId' => $ibexaLocation->id ?? $ibexaContent->contentInfo->mainLocationId,
        ]);
        $skippedProperties = [
            'id' => true,
            'locationId' => true,
        ];
        return $this->createLazyContent($initializers, $skippedProperties, $instance);
    }

    /**
     * @param array<string, callable(Content, string, ?string): mixed> $initializers
     * @param array<string, true> $skippedProperties
     */
    protected function createLazyContent(array $initializers, array $skippedProperties = [], ?Content $instance = null): Content
    {
        $initializers += [
            "\0*\0fields" => function (
                Content $instance,
                string $propertyName,
                ?string $propertyScope
            ): ContentFieldsCollection {
                $contentType = $instance->getContentType();
                $contentDefinition = $this->definitionManager->getDefinition(
                    ContentDefinition::class,
                    $contentType->identifier
                );

                $contentFields = new ContentFieldsCollection();
                foreach ($contentDefinition->getFields() as $fieldIdentifier => $contentFieldDefinition) {
                    $contentFields->set(
                        $fieldIdentifier,
                        new LazyValue(
                            fn() => $this->fieldValueTransformers->transform(
                                $instance,
                                $fieldIdentifier,
                                $contentFieldDefinition
                            )
                        )
                    );
                }

                return $contentFields;
            },
            "name" => fn(Content $instance, string $propertyName, ?string $propertyScope): string => $instance->innerContent->getName(),
            "type" => fn(Content $instance, string $propertyName, ?string $propertyScope): string => $instance->getContentType()
                ->identifier,
            "languageCodes" => fn(Content $instance, string $propertyName, ?string $propertyScope): array => array_keys($instance->innerContent->versionInfo->getNames()),
            "mainLanguageCode" => fn(Content $instance, string $propertyName, ?string $propertyScope): string => $instance->innerContent->contentInfo->mainLanguageCode,
            "alwaysAvailable" => fn(Content $instance, string $propertyName, ?string $propertyScope): bool => $instance->innerContent->contentInfo->alwaysAvailable,
            "hidden" => fn(Content $instance, string $propertyName, ?string $propertyScope): bool => $instance->innerContent->contentInfo->isHidden() || $instance->innerLocation->isHidden() || $instance->innerLocation->isInvisible(),
            "creationDate" => fn(Content $instance, string $propertyName, ?string $propertyScope): DateTime => $instance->innerContent->contentInfo->publishedDate,
            "modificationDate" => fn(Content $instance, string $propertyName, ?string $propertyScope): DateTime => $instance->innerContent->contentInfo->modificationDate,
            "url" => fn(Content $instance, string $propertyName, ?string $propertyScope): string => $instance->innerLocation ? $this->linkGenerator->generateUrl(
                UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
                [
                    'locationId' => $instance->innerLocation->id,
                ]
            ) : '',
            "breadcrumb" => fn(Content $instance, string $propertyName, ?string $propertyScope): Breadcrumb => $instance->innerLocation ?
                $this->breadcrumbGenerator->generateLocationBreadcrumb($instance->innerLocation) :
                new Breadcrumb(),
            "parent" => fn(Content $instance, string $propertyName, ?string $propertyScope): ?Content => $instance->innerLocation ?
                $this->lazyTransformContentFromLocationId($instance->innerLocation->parentLocationId) :
                null,
        ];

        return Content::createLazyGhost($initializers, $skippedProperties, $instance);
    }
}
