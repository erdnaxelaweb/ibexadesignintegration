# Field Value Transformers

## Overview

Field Value Transformers are specialized services that handle the transformation of Ibexa field values from their raw form into a format suitable for rendering or further processing in templates.

These transformers follow a tagged service pattern in Symfony, allowing for extensibility and type-specific handling of different field types available in the Ibexa CMS.

## Purpose

The primary purpose of these transformers is to:

1. Convert raw field data into usable values
2. Enable type-specific transformations for different field types (e.g., binary files, images, richtext, etc.)
3. Provide a consistent interface for accessing field values

## Service Definition

Field value transformers are defined as Symfony services with specific tags. Here's an example of how they are defined:

```yaml
ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue\FileFieldValueTransformer:
    lazy: true
    tags:
        - { name: 'erdnaxelaweb.ibexa_design_integration.transformer.field_value', type: 'file' }
```

Key components:
- **Tags**: Tagged with `erdnaxelaweb.ibexa_design_integration.transformer.field_value` to be collected by the tagged iterator
- **Type**: Each transformer has a specific type (e.g., 'file') that corresponds to the content field type



## Implementation

Field value transformers must implement the `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue\FieldValueTransformerInterface` which defines the method:

```php
transformFieldValue(
    AbstractContent $content,
    string $fieldIdentifier,
    FieldDefinition $fieldDefinition,
    array $fieldConfiguration
)
```

This method takes the content, field identifier, field definition, and field configuration and returns the transformed value.

## Registration and Usage

These transformers are automatically collected using Symfony's tagged service feature:

```yaml
$transformers: !tagged_iterator { 
    tag: 'erdnaxelaweb.ibexa_design_integration.transformer.field_value', 
    index_by: 'type' 
}
```

The main `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValueTransformer` service uses this tagged iterator to access all registered transformers and delegates the transformation to the appropriate one based on the field type.

## Supported transformers

The Ibexa Design Integration bundle includes the following field types value transformers:

- **ezboolean**
- **ezdate**
- **ezemail**
- **ezstring**
- **eztime**
- **ezdatetime**
- **ezfloat**
- **ezinteger**
- **ezbinaryfile**
- **eztext**
- **ezgmaplocation**
- **ezrichtext**
- **ezselection**
- **ezmatrix**
- **ezimage**
- **ezimageasset**
- **ezobjectrelation**
- **ezobjectrelationlist**
- **ezurl**
- **ezform**
- **ezlandingpage**
- **ibexa_product_specification**
- **ibexa_taxonomy_entry_assignment**
- **ibexa_taxonomy_entry**
- **segment_content_map**

## Creating Custom Transformers

To create a custom transformer for a specific field type:

1. Create a class that implements `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue\FieldValueTransformerInterface`
2. Define the transformation logic for the specific field type
3. Register the service with the appropriate tag and field type in your services configuration

Example:
```yaml
App\Transformer\FieldValue\CustomFieldValueTransformer:
    lazy: true
    tags:
        - { name: 'erdnaxelaweb.ibexa_design_integration.transformer.field_value', type: 'custom_field_type' }
```
