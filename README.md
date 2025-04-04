# Ibexa Design Integration Bundle

This bundle extends [erdnaxelaweb/staticfakedesign](https://github.com/erdnaxelaweb/staticfakedesign) to provide an abstraction layer for the Ibexa CMS in order to streamline the templates development workflow.

It serves as a bridge between static design development and the Ibexa CMS by:

1. Providing a consistent definition model for content types, taxonomy entries, and landing page blocks
2. Managing transformations between Ibexa's native objects and template-friendly representations
3. Enabling developers to build and test templates before the CMS integration
4. Offering a smooth transition from static design to dynamic CMS implementation

## Table of Contents

- [Installation](#installation)
- [Purpose](#purpose)
- [Features](#features)
- [Usage](#usage)
  - [Content types](#content-types)
  - [Taxonomy entries](#taxonomy-entries)
  - [Landing Page blocks](#landing-page-blocks)
  - [Template integration](#template-integration)
- [Documentation](#documentation)
- [License](#license)

## Installation

1. Add the bundle to your project via Composer:

```bash
composer require erdnaxelaweb/ibexadesignintegration
```

2. Register the bundle in your `config/bundles.php`:

```php
return [
    // ...
    ErdnaxelaWeb\IbexaDesignIntegrationBundle\IbexaDesignIntegrationBundle::class => ['all' => true],
];
```

## Features

- **Value Transformers**: Specialized services for transforming Ibexa CMS objects (content, fields, blocks) into template-friendly objects
- **Content Abstraction**: Consistent interfaces for accessing content data regardless of the underlying implementation
- **Block Management**: Enhanced landing page block integration with attribute transformation
- **Taxonomy Integration**: Support for Ibexa Taxonomy with transformation capabilities
- **Pagination System**: Flexible pager builder with search type system for creating paginated listings
- **Performance Optimization**: Lazy-loading mechanisms for improved template rendering performance
- **HTTP Cache Integration**: Automatic response tagging for efficient cache invalidation

## Usage

Refer to the [erdnaxelaweb/staticfakedesign](https://github.com/erdnaxelaweb/staticfakedesign) bundle documentation for detailed usage instructions.

### Content types

Define content types in a standardized way using the definition pattern:

```yaml
parameters:
    erdnaxelaweb.static_fake_design.content_definition:
        article:
            name:
                eng-GB: 'Article'
            nameSchema: '<title>'
            urlAliasSchema: '<title>'
            fields:
                title:
                    type: string
                    required: true
                body:
                    type: richtext
                    required: false
```

### Taxonomy entries

Define taxonomy types for categorizing content:

```yaml
parameters:
    erdnaxelaweb.static_fake_design.taxonomy_entry_definition:
        category:
            fields:
                name:
                    required: true
                    type: string
                identifier:
                    required: true
                    type: string
```

### Landing Page Blocks

Create custom landing page blocks:

```yaml
parameters:
    erdnaxelaweb.static_fake_design.block_definition:
        featured_articles:
            views:
                default: '@@ibexadesign/landing_page/block/featured_articles.html.twig'
            attributes:
                title:
                    type: "string"
                    required: false
                articles:
                    type: "content"
                    required: true
                    options:
                        type: article
                        max: 3
```

### Template Integration

The bundle provide a set of twig functions to integrate templates with the Ibexa CMS.:

- `display_content`: Renders a content view template
```twig
{{ display_content(<template name>, <content>, <parameters>, <is ESI>, <view type>) }}
{{ display_content('@ibexadesign/content/list/news.html.twig', content, []) }}
```

- `display_block`: Renders a block view template
```twig
{{ display_block(<block>, <is ESI>) }}
{{ display_block(block) }}
```

- `display_component`: Renders a component view template
```twig
{{ display_component('@ibexadesign/components/footer.html.twig', []) }}
{{ display_component(<template name>, <parameters>, <controller action>, <is ESI>) }}
```

Thoses functions are used to switch between `include` and `render` depending on the context (static or dynamic version).

## Rules for view template integration

1. A content view template should always be associated with a content object
2. Page template names should follow the pattern: `<view>/<content_type>.html.twig`
3. The content variable in a content view template should always be named `content`

## Documentation

Detailed documentation is available in the `./doc` directory:

- [Block Attribute Value Transformers](./doc/block_attribute_value_transformers.md)
- [Block Transformer](./doc/block_transformer.md)
- [Content Transformer](./doc/content_transformer.md)
- [Field Value Transformers](./doc/field_value_transformers.md)
- [Pager Builder](./doc/pager_builder.md)
- [Pager Search Types](./doc/pager_search_types.md)
- [Taxonomy Entry Transformer](./doc/taxonomy_entry_transformer.md)
- [Developer Guide](./doc/dev_guide.md)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This bundle is released under the [MIT License](LICENSE).
