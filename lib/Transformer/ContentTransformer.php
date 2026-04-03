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
use ErdnaxelaWeb\StaticFakeDesign\LazyLoading\LazyValue;
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
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function lazyTransformContentFromLocationRemoteId(string $remoteId): Content
    {
        $location = $this->locationService->loadLocationByRemoteId($remoteId);
        $initializers = [
            'id' => fn (Content $instance): int => $instance->innerLocation->contentId,
            'locationId' => fn (Content $instance): int => $instance->innerLocation->id,
            'innerContent' => function (Content $instance): IbexaContent {
                $content = $instance->innerLocation->getContent();
                $this->responseTagger->addContentTags([$content->id]);
                return $content;
            },
            'innerLocation' => function (Content $instance) use (
                $location
            ): IbexaLocation {
                $this->responseTagger->addLocationTags([$location->id]);
                return $location;
            },
        ];

        $baseProperties = [];
        return $this->createLazyContent($baseProperties, $initializers);
    }

    public function lazyTransformContentFromLocationId(int $locationId): Content
    {
        $initializers = [
            'id' => fn (Content $instance): int => $instance->innerLocation->contentId,
            'innerContent' => function (Content $instance): IbexaContent {
                $content = $instance->innerLocation->getContent();
                $this->responseTagger->addContentTags([$content->id]);
                return $content;
            },
            'innerLocation' => function (Content $instance): IbexaLocation {
                $this->responseTagger->addLocationTags([$instance->locationId]);
                return $this->locationService->loadLocation($instance->locationId);
            },
        ];

        $baseProperties = [
            'locationId' => $locationId,
        ];
        return $this->createLazyContent($baseProperties, $initializers);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function lazyTransformContentFromContentRemoteId(string $remoteId): Content
    {
        $content = $this->contentService->loadContentByRemoteId($remoteId);
        $initializers = [
            'id' => fn (Content $instance): int => $instance->innerContent->id,
            'locationId' => fn (Content $instance): int => $instance->innerContent->contentInfo->mainLocationId,
            'innerContent' => function (Content $instance) use (
                $content
            ): IbexaContent {
                $this->responseTagger->addContentTags([$content->id]);
                return $content;
            },
            'innerLocation' => function (Content $instance): IbexaLocation {
                $location = $instance->innerContent->contentInfo->getMainLocation();
                if ($location) {
                    $this->responseTagger->addLocationTags([$location->id]);
                }
                return $location;
            },
        ];

        $baseProperties = [];
        return $this->createLazyContent($baseProperties, $initializers);
    }

    public function lazyTransformContentFromContentId(int $contentId): Content
    {
        $initializers = [
            'locationId' => fn (Content $instance): int => $instance->innerContent->contentInfo->mainLocationId,
            'innerContent' => function (Content $instance): IbexaContent {
                $this->responseTagger->addContentTags([$instance->id]);
                return $this->contentService->loadContent($instance->id);
            },
            'innerLocation' => function (Content $instance): IbexaLocation {
                $location = $instance->innerContent->contentInfo->getMainLocation();
                if ($location) {
                    $this->responseTagger->addLocationTags([$location->id]);
                }
                return $location;
            },
        ];

        $baseProperties = [
            'id' => $contentId,
        ];
        return $this->createLazyContent($baseProperties, $initializers);
    }

    public function transformContent(IbexaContent $ibexaContent, ?IbexaLocation $ibexaLocation = null): Content
    {
        if ($ibexaContent instanceof Content) {
            return $ibexaContent;
        }

        $initializers = [
            'innerContent' => function (Content $instance) use (
                $ibexaContent
            ): IbexaContent {
                $this->responseTagger->addContentTags([$ibexaContent->id]);
                return $ibexaContent;
            },
            'innerLocation' => function (Content $instance) use (
                $ibexaLocation
            ): IbexaLocation {
                $location = $ibexaLocation ?? $instance->innerContent->contentInfo->getMainLocation();
                if ($location) {
                    $this->responseTagger->addLocationTags([$location->id]);
                }
                return $location;
            },
        ];

        $baseProperties = [
            'id' => $ibexaContent->id,
            'locationId' => $ibexaLocation->id ?? $ibexaContent->contentInfo->mainLocationId,
        ];
        return $this->createLazyContent($baseProperties, $initializers);
    }

    /**
     * @param array<string, mixed> $baseProperties
     * @param array<string, callable(Content): mixed> $initializers
     */
    protected function createLazyContent(
        array $baseProperties,
        array $initializers = []
    ): Content {
        $initializers += [
            "fields" => function (
                Content $instance
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
                            fn () => $this->fieldValueTransformers->transform(
                                $instance,
                                $fieldIdentifier,
                                $contentFieldDefinition
                            )
                        )
                    );
                }

                return $contentFields;
            },
            "name" => fn (Content $instance): string => $instance->innerContent->getName(),
            "type" => fn (Content $instance): string => $instance->getContentType()
                ->identifier,
            "languageCodes" => fn (Content $instance): array => array_keys($instance->innerContent->versionInfo->getNames()),
            "mainLanguageCode" => fn (Content $instance): string => $instance->innerContent->contentInfo->mainLanguageCode,
            "alwaysAvailable" => fn (Content $instance): bool => $instance->innerContent->contentInfo->alwaysAvailable,
            "hidden" => fn (Content $instance): bool => $instance->innerContent->contentInfo->isHidden() || $instance->innerLocation->isHidden() || $instance->innerLocation->isInvisible(),
            "creationDate" => fn (Content $instance): DateTime => $instance->innerContent->contentInfo->publishedDate,
            "modificationDate" => fn (Content $instance): DateTime => $instance->innerContent->contentInfo->modificationDate,
            "url" => fn (Content $instance): string => $instance->innerLocation ? $this->linkGenerator->generateUrl(
                UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
                [
                    'locationId' => $instance->innerLocation->id,
                ]
            ) : '',
            "breadcrumb" => fn (Content $instance): Breadcrumb => $instance->innerLocation ?
                $this->breadcrumbGenerator->generateLocationBreadcrumb($instance->innerLocation) :
                new Breadcrumb(),
            "parent" => fn (Content $instance): ?Content => $instance->innerLocation ?
                $this->lazyTransformContentFromLocationId($instance->innerLocation->parentLocationId) :
                null,
        ];

        return Content::instantiate($baseProperties, $initializers);
    }
}
