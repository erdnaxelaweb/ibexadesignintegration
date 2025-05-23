# PagerBuilder

## Overview

The `ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerBuilder` is a service that facilitates creating paginated results from Ibexa search operations. 
It provides a streamlined interface for building customizable, filterable, and configurable pagers that can be used throughout your application.

## Purpose

The primary purpose of the PagerBuilder is to:

1. Abstract the complexity of creating search queries and pagination
2. Provide a unified interface for different search types (content, locations, custom)
3. Support filtering, sorting, and other search operations
4. Allow for event-based customization of search queries
5. Create consistent pagination objects across the application

## Core Features

### Configuration-Driven Search

PagerBuilder uses a configuration-driven approach where each pager is defined by a type identifier that maps to a specific configuration:

```php
$pager = $pagerBuilder->build('article_list');
```

The configuration defines search parameters, display options, and filtering capabilities for each pager type.

### Search Type System

The PagerBuilder uses a search type system to adapt to different data sources and search requirements:

1. **Search Type Factories**: Each search type is provided by a factory registered with a specific identifier
2. **Contextual Queries**: Search types can adjust their behavior based on context, request parameters, or default data
3. **Adapters**: Each search type provides the appropriate Pagerfanta adapter for the data source

### Event-Based Query Modification

The search query can be modified through events, allowing for:

1. **Query Criterion Modification**: Add or modify search criteria
2. **Filter Criterion Modification**: Adjust filtering criteria
3. **Aggregation Addition**: Add custom aggregations for search facets
4. **Context-Specific Modifications**: Different modifications can be applied depending on the pager type

### Search form

PagerBuilder integrates with Symfony forms for search and filtering:

1. **Search Forms**: Built through the `ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder`
2. **Active Filters**: Managed by the `ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder`

## Usage

### Basic Usage

```php
// In a controller
$pager = $this->pagerBuilder->build('article_list');

// Pass to template
return $this->render('articles/list.html.twig', [
    'pager' => $pager,
]);
```

### With Context

```php
// Provide context that might influence the search
$pager = $this->pagerBuilder->build('article_list', [
    'category' => 'technology',
    'featured' => true,
]);
```

### With Default Search Data

```php
// Set default search parameters
$searchData = new SearchData();
$searchData->setSortField('published_date');
$searchData->setSortOrder('desc');

$pager = $this->pagerBuilder->build('article_list', [], $searchData);
```

## Configuration

Pager configurations are managed by the `PagerConfigurationManager` and typically defined in configuration files:

```yaml
pagers:
    article_list:
        searchType: 'content'
        maxPerPage: 12
        headlineCount: 3
        disablePagination: true # When true, the returned pager iterator autoswitch page
        sortOptions:
            - { field: 'published_date', order: 'desc', label: 'Most recent' }
            - { field: 'title', order: 'asc', label: 'Alphabetical' }
        filterOptions:
            - { field: 'category', type: 'select', label: 'Category' }
```

## Event System

The PagerBuilder dispatches events at key points during the build process:

1. **Global Event**: `ErdnaxelaWeb\IbexaDesignIntegration\Event\PagerBuildEvent::GLOBAL_PAGER_BUILD` is dispatched for all pagers
2. **Type-Specific Event**: `ErdnaxelaWeb\IbexaDesignIntegration\Event\PagerBuildEvent::getEventName($type)` is dispatched for a specific pager type

Event subscribers can modify the search query by adding criteria, filters, or aggregations:

```php
public function onBuildArticleList(PagerBuildEvent $event)
{
    if ($event->context['featured'] ?? false) {
        $event->queryCriterions[] = new Criterion\Field('featured', Criterion\Operator::EQ, true);
    }
}
```

## Integration with Content System

The PagerBuilder integrates with the content transformation system:

1. **Content Transformation**: Results are automatically transformed using the `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer`

## Benefits

- **Consistency**: Creates consistent pagination behavior across the application
- **Flexibility**: Supports different search types and configurations
- **Extensibility**: Can be extended through events and custom search types
- **Performance**: Optimized for Ibexa search operations
- **Developer Experience**: Simplifies the creation of paginated content listings

The PagerBuilder is a key component for creating efficient, flexible, and user-friendly paginated listings in Ibexa CMS applications.
