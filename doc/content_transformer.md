# Content Transformer

## Overview

The `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer` is a service that transforms Ibexa CMS content objects into enhanced, template-friendly content objects. 
It provides a consistent interface for accessing content data and handles lazy-loading of properties for improved performance.

## Purpose

The primary purpose of the ContentTransformer is to:

1. Convert native Ibexa content objects into a more developer-friendly format
2. Implement lazy-loading of content properties to optimize performance
3. Integrate with the HTTP cache system through response tagging
4. Provide consistent access to content fields, metadata, and relationships

## Features

### Content Transformation

The ContentTransformer converts Ibexa's `Ibexa\Contracts\Core\Repository\Values\Content\Content` objects into `ErdnaxelaWeb\IbexaDesignIntegration\Value\Content` objects, enriching them with additional properties and functionality.

### Block Attributes Collection

The transformer creates a `ErdnaxelaWeb\IbexaDesignIntegration\Value\ContentFieldsCollection` that provides access to transformed content fields. This collection works with the [`FieldValueTransformer`](field_value_transformers.md) to ensure fields are properly transformed based on their types.

### Configuration Integration

The service integrates with the `ContentConfigurationManager` to retrieve content-specific configurations, particularly for field handling.

### Attribute Value Transformation

Through the [`FieldValueTransformer`](field_value_transformers.md), the service ensures fields values are transformed into usable formats for templates, with type-specific transformations.

### Lazy Loading

The ContentTransformer implements lazy loading through "ghost" objects, where properties are only loaded when accessed. This significantly improves performance, especially when dealing with complex content structures.

### Content Access Methods

The service provides multiple ways to transform content:

- **From Location ID**: `lazyTransformContentFromLocationId(int $locationId)`
- **From Content ID**: `lazyTransformContentFromContentId(int $contentId)`
- **From Existing Objects**: `transformContent(IbexaContent $ibexaContent, ?IbexaLocation $ibexaLocation = null)`
- **Callable Interface**: The service can be invoked directly as a function via the `__invoke()` method

### Automatic Response Tagging

The transformer automatically adds the appropriate HTTP cache tags for both content and location objects, ensuring proper cache invalidation when content changes.

### Enhanced Content Data

The transformed content objects provide:

- Standard metadata (name, type, creation date, modification date)
- URL generation
- Breadcrumb generation
- Type-specific field value transformation through the FieldValueTransformer service

## Usage

The ContentTransformer can be used in controllers, services, or directly in templates (when registered as a Twig extension) to provide consistent access to content data:

```php
// Using location ID
$content = $contentTransformer->lazyTransformContentFromLocationId($locationId);

// Using content ID
$content = $contentTransformer->lazyTransformContentFromContentId($contentId);

// Using existing objects
$content = $contentTransformer->transformContent($ibexaContent, $ibexaLocation);

// Using invoke
$content = $contentTransformer($ibexaContent, $ibexaLocation);
```

## Content Object Properties

The transformed Content object provides access to:

- **id**: Content ID
- **locationId**: Location ID (main location or specified location)
- **name**: Content name
- **type**: Content type identifier
- **creationDate**: Date when content was created
- **modificationDate**: Date when content was last modified
- **url**: Full URL to the content
- **breadcrumb**: Generated breadcrumb for navigation
- **fields**: Collection of transformed field values

## Integration with Other Services

The ContentTransformer integrates with several other services:

- **ContentConfigurationManager**: Provides content type-specific configuration
- **LinkGenerator**: Generates URLs for content
- **BreadcrumbGenerator**: Creates navigation breadcrumbs
- **FieldValueTransformer**: Transforms field values based on their field types
- **ContentService & LocationService**: Core Ibexa services for loading content
- **TagHandler**: Handles HTTP cache tagging

## Benefits

- **Performance**: Lazy loading ensures optimal performance
- **Developer Experience**: Provides a clean, consistent interface for templates
- **Cache Integration**: Automatically handles HTTP cache tags
- **Extensibility**: Works with the field value transformer system for custom field types

The ContentTransformer is a fundamental part of the Ibexa Design Integration bundle, making it easier to work with content in templates while maintaining performance and flexibility.
