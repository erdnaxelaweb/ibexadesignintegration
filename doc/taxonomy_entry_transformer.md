# Taxonomy Entry Transformer

## Overview

The `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\TaxonomyEntryTransformer` is a service that transforms Ibexa Taxonomy entries into enhanced, template-friendly objects. 
It provides a consistent interface for accessing taxonomy data and implements lazy-loading of properties for improved performance.

## Purpose

The primary purpose of the TaxonomyEntryTransformer is to:

1. Convert native Ibexa Taxonomy entries into a more developer-friendly format
2. Implement lazy-loading of taxonomy properties to optimize performance
3. Integrate with the HTTP cache system through response tagging
4. Provide consistent access to taxonomy-related content fields, metadata, and relationships

## Features

### Lazy Loading

The TaxonomyEntryTransformer implements lazy loading through "ghost" objects, where properties are only loaded when accessed. This significantly improves performance, especially when dealing with complex taxonomy hierarchies.

### Taxonomy Entry Access

The service provides methods to transform Ibexa taxonomy entries:

- **Transform Taxonomy Entry**: `transformTaxonomyEntry(IbexaTaxonomyEntry $ibexaTaxonomyEntry)`

### Automatic Response Tagging

The transformer automatically adds the appropriate HTTP cache tags for taxonomy-related content objects, ensuring proper cache invalidation when content changes.

### Enhanced Taxonomy Data

The transformed taxonomy entry objects provide:

- Standard metadata (name, type, creation date, modification date)
- Taxonomy-specific data (identifier, level)
- Access to the underlying content
- Type-specific field value transformation through the FieldValueTransformer service

## Usage

The TaxonomyEntryTransformer can be used in controllers, services, or directly in templates (when registered as a Twig extension) to provide consistent access to taxonomy data:

```php
// Transform a taxonomy entry
$taxonomyEntry = $taxonomyEntryTransformer->transformTaxonomyEntry($ibexaTaxonomyEntry);
```

## Taxonomy Entry Object Properties

The transforme
