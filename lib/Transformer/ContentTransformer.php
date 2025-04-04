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
use ErdnaxelaWeb\IbexaDesignIntegration\Value\LazyTransformer;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Value\Breadcrumb;
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

    public function lazyTransformContentFromLocationId(int $locationId): Content
    {
        $initializers = [
            'id' => function (Content $instance, string $propertyName, ?string $propertyScope): int {
                return $instance->innerLocation->contentId;
            },
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

    public function lazyTransformContentFromContentId(int $contentId): Content
    {
        $initializers = [
            'locationId' => function (Content $instance, string $propertyName, ?string $propertyScope): int {
                return $instance->innerContent->contentInfo->mainLocationId;
            },
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
                        new LazyTransformer(
                            function () use ($instance, $fieldIdentifier, $contentFieldDefinition) {
                                return $this->fieldValueTransformers->transform(
                                    $instance,
                                    $fieldIdentifier,
                                    $contentFieldDefinition
                                );
                            }
                        )
                    );
                }

                return $contentFields;
            },
            "name" => function (Content $instance, string $propertyName, ?string $propertyScope): string {
                return $instance->innerContent->getName();
            },
            "type" => function (Content $instance, string $propertyName, ?string $propertyScope): string {
                return $instance->getContentType()
                    ->identifier;
            },
            "creationDate" => function (Content $instance, string $propertyName, ?string $propertyScope): DateTime {
                return $instance->innerContent->contentInfo->publishedDate;
            },
            "modificationDate" => function (Content $instance, string $propertyName, ?string $propertyScope): DateTime {
                return $instance->innerContent->contentInfo->modificationDate;
            },
            "url" => function (Content $instance, string $propertyName, ?string $propertyScope): string {
                return $instance->innerLocation ? $this->linkGenerator->generateUrl(
                    UrlAliasRouter::URL_ALIAS_ROUTE_NAME,
                    [
                        'locationId' => $instance->innerLocation->id,
                    ]
                ) : '';
            },
            "breadcrumb" => function (Content $instance, string $propertyName, ?string $propertyScope): Breadcrumb {
                return $instance->innerLocation ?
                    $this->breadcrumbGenerator->generateLocationBreadcrumb($instance->innerLocation) :
                    new Breadcrumb();
            },
        ];

        return Content::createLazyGhost($initializers, $skippedProperties, $instance);
    }
}
