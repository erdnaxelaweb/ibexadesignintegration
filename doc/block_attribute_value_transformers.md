# Block Attribute Value Transformers

## Overview

Block Attribute Value Transformers are specialized services that handle the transformation of block attribute values from their raw form into a format suitable for rendering or further processing in templates.

These transformers follow a tagged service pattern in Symfony, allowing for extensibility and type-specific handling of different attribute formats.

## Purpose

The primary purpose of these transformers is to:

1. Convert raw block attribute data into usable values
2. Enable type-specific transformations for different attribute types (e.g., integer, string, content, etc.)
3. Provide a consistent interface for accessing block attribute values

## Service Definition

Block attribute value transformers are defined as Symfony services with specific tags. Here's an example of how they are defined:

```yaml
ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute\IntegerBlockAttributeValueTransformer:
    lazy: true
    tags:
        - { name: 'erdnaxelaweb.ibexa_design_integration.transformer.block_attribute_value', type: 'integer'}
```

Key components:
- **Tags**: Tagged with `erdnaxelaweb.ibexa_design_integration.transformer.block_attribute_value` to be collected by the tagged iterator
- **Type**: Each transformer has a specific type (e.g., 'integer') that corresponds to the block attribute type it handles

## Implementation

Block attribute transformers must implement the `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\BlockAttribute\BlockAttributeValueTransformerInterface` which defines the method:

```php
transformAttributeValue(
    BlockValue $blockValue,
    string $attributeIdentifier,
    BlockDefinition $blockDefinition,
    array $attributeConfiguration
)
```

This method takes the block value, attribute identifier, block definition, and attribute configuration and returns the transformed value.

## Registration and Usage

These transformers are automatically collected using Symfony's tagged service feature:

```yaml
$transformers: !tagged_iterator { 
    tag: 'erdnaxelaweb.ibexa_design_integration.transformer.block_attribute_value', 
    index_by: 'type' 
}
```

The main `BlockAttributeValueTransformer` service uses this tagged iterator to access all registered transformers and delegates the transformation to the appropriate one based on the attribute type.

## Supported transformers

The Ibexa Design Integration bundle includes the following block attribute value transformers:

- **integer**
- **string**
- **url**
- **text**
- **richtext**
- **embed**
- **checkbox**
- **multiple**
- **select**
- **radio**
- **locationlist**
- **contenttypelist**
- **ezlandingpage**

## Creating Custom Transformers

To create a custom transformer for a specific block attribute type:

1. Create a class that implements `BlockAttributeValueTransformerInterface`
2. Define the transformation logic in the `transformAttributeValue()` method
3. Register the service with the appropriate tag and type in your services configuration

Example:
```yaml
App\Transformer\BlockAttribute\CustomBlockAttributeValueTransformer:
    lazy: true
    tags:
        - { name: 'erdnaxelaweb.ibexa_design_integration.transformer.block_attribute_value', type: 'custom_type'}
```
