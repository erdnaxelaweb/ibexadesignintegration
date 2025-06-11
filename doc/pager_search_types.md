# Pager Search Types Documentation

## Overview

Pager Search Types are components that handle different types of search.

## Search Types Architecture

Search types extend the `ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\AbstractSearchType` class and implement specific query logic for different data sources.

### Key Components

1. **Search Type Factory**: Creates instances of search types based on configuration
2. **Search Type**: Intialize the query and create the adapter
3. **Search Adapters**: Handle the actual querying and result transformation

## Available Search Types

The bundle provides several search types, which are registered as services :
### Content search type

#### Features

- Uses Ibexa's `SearchService` to query content
- Transforms content results using a `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer`
- Provides form-based filtering and sorting
- Tracks active filters for UI display

#### Usage in Configuration

To use the ContentSearchType in your pager configuration:

```yaml
ibexa_design_integration:
   system:
      default:
         pager_definition:
            article_list:
              searchType: 'content'
              # Other configuration options...
```

### location search type

#### Features

- Uses Ibexa's `SearchService` to query location
- Transforms location results using a `ErdnaxelaWeb\IbexaDesignIntegration\Transformer\ContentTransformer`
- Provides form-based filtering and sorting
- Tracks active filters for UI display

#### Usage in Configuration

To use the LocationSearchType in your pager configuration:

```yaml
ibexa_design_integration:
   system:
      default:
         pager_definition:
            article_list:
              searchType: 'location'
              # Other configuration options...
```

## Implementing a Custom Search Type

To create a custom search type:

1. Create a class that extends `ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\AbstractSearchType`
2. Implement the required methods:
    - `initializeQuery()`: Set up your query object
    - `getAdapter()`: Return a custom adapter implementing `ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface`
3. Create the factory that implement `ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory\SearchTypeFactoryInterface`
4. Register your search type factory as a service with the appropriate tag
5. Create a class that implement `ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface`

### Example Implementation

```yaml
    App\Pager\SearchType\CustomSearchTypeFactory:
        tags:
            - { name: erdnaxelaweb.ibexa_design_integration.pager.search_type, type: custom }
```

```php
namespace App\Pager\SearchType;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\Factory\SearchTypeFactoryInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerActiveFiltersListBuilder;
use ErdnaxelaWeb\IbexaDesignIntegration\Pager\PagerSearchFormBuilder;
use App\Pager\CustomSearchAdapter;

class CustomSearchTypeFactory implements SearchTypeFactoryInterface
{
    public function __construct(
        protected PagerSearchFormBuilder        $pagerSearchFormBuilder,
        protected PagerActiveFiltersListBuilder $pagerActiveFiltersListBuilder,
    ) {
    }

    public function __invoke(
        string     $searchFormName,
        array      $configuration,
        Request    $request,
        SearchData $defaultSearchData = new SearchData()
    ): SearchTypeInterface {
        return new CustomSearchType(
            $this->pagerSearchFormBuilder,
            $this->pagerActiveFiltersListBuilder,
            $searchFormName,
            $configuration,
            $request,
            $defaultSearchData
        );
    }
}
```

```php
namespace App\Pager\SearchType;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\SearchType\AbstractSearchType;
use ErdnaxelaWeb\StaticFakeDesign\Value\PagerAdapterInterface;
use App\Value\CustomSearchAdapter;

class CustomSearchType extends AbstractSearchType
{
    protected function initializeQuery(): void
    {
        $this->query = new CustomQuery();
    }

    public function getAdapter(): PagerAdapterInterface
    {
        return new CustomSearchAdapter(
            $this->query,
            // Additional dependencies
            [$this, 'getFiltersForm'],
            [$this, 'getActiveFilters']
        );
    }
}
```
