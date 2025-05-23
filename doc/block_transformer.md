# Block Transformer

## Overview

The `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockTransformer` is a service that transforms Ibexa Landing Page block values into enhanced, template-friendly block objects. 
It enriches the native block values with additional functionality and a consistent interface for accessing block data.

## Purpose

The primary purpose of the BlockTransformer is to:

1. Convert native Ibexa Landing Page block objects into a more developer-friendly format
2. Provide consistent access to block attributes with proper type transformation
3. Integrate with block configuration to apply type-specific handling
4. Create a unified interface for block rendering in templates

## Features

### Block Value Transformation

The BlockTransformer converts Ibexa's `Ibexa\Contracts\FieldTypePage\FieldType\LandingPage\Model\BlockValue` objects into `ErdnaxelaWeb\IbexaDesignIntegration\Value\Block` objects, enriching them with additional properties and functionality.

### Block Attributes Collection

The transformer creates a `ErdnaxelaWeb\IbexaDesignIntegration\Value\BlockAttributesCollection` that provides access to transformed block attributes. This collection works with the [`BlockAttributeValueTransformer`](block_attribute_value_transformers.md) to ensure attributes are properly transformed based on their types.

### Configuration Integration

The service integrates with the `BlockConfigurationManager` to retrieve block-specific configurations, particularly for attribute handling.

### Attribute Value Transformation

Through the [`BlockAttributeValueTransformer`](block_attribute_value_transformers.md), the service ensures attribute values are transformed into usable formats for templates, with type-specific transformations.

## Usage

The BlockTransformer is designed to be used directly as a callable through its `__invoke()` method:

```php
// Transform a block value
$block = $blockTransformer($blockValue);

// Transform with additional properties
$block = $blockTransformer($blockValue, [
    'customProperty' => 'value',
]);
```

## Transformed Block Properties

The transformed Block object provides access to:

- **id**: The block ID (as integer)
- **name**: The block name
- **type**: The block type identifier
- **view**: The block view type
- **class**: CSS class(es) applied to the block
- **style**: Compiled CSS styles or raw style definition
- **since**: Timestamp when the block becomes visible (for scheduled blocks)
- **till**: Timestamp when the block stops being visible (for scheduled blocks)
- **innerValue**: The original Ibexa BlockValue object
- **attributes**: Collection of transformed block attributes

Additional properties can be provided during transformation through the `$additionalProperties` parameter.

## Integration with Other Services

The BlockTransformer relies on three main services:

- **BlockConfigurationManager**: Provides block type-specific configuration
- **BlockDefinitionFactoryInterface**: Retrieves block definitions from Ibexa
- **BlockAttributeValueTransformer**: Transforms attribute values based on their types

## Error Handling

The transformer gracefully handles cases where block configuration is not found by using an empty attributes configuration, allowing the system to continue functioning even if configuration is incomplete.

## Benefits

- **Developer Experience**: Provides a clean, consistent interface for templates
- **Type Safety**: Ensures block attributes are properly transformed
- **Flexibility**: Supports additional properties for custom needs
- **Integration**: Works within the broader design integration ecosystem

The BlockTransformer is an essential part of the Ibexa Design Integration bundle's landing page functionality, making it easier to work with page blocks in templates while providing type-safe access to block attributes.
