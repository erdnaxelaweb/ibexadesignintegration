# Ibexa CMS Development Guide

This guide documents the step-by-step process for extending Ibexa CMS with new content types, taxonomy types, and landing page block types.

## 1. Adding a New Content Type

### Step 1: Create Content Definition
Add the content type definition in `ibexa/config/packages/project/static_parameters/content_definition.yaml`:

```yaml
ibexa_design_integration:
   system:
      default:
          content_definition:
              article:
                  name:
                      fre-FR: 'Article'
                  description:
                      fre-FR: ''
                  nameSchema: '<title>'
                  urlAliasSchema: '<title>'
                  defaultAlwaysAvailable: true
                  defaultSortField: published
                  defaultSortOrder: desc
                  container: false
                  fields:
                      title:
                          name: { fre-FR: Titre }
                          description: { fre-FR: '' }
                          type: string
                          required: true
                          searchable: true
                          translatable: true
                          category: Content
                      intro:
                          name: { fre-FR: Introduction }
                          description: { fre-FR: '' }
                          type: richtext
                          required: false
                          searchable: true
                          translatable: true
                          category: Content
                      body:
                          name: { fre-FR: Corps de texte }
                          description: { fre-FR: '' }
                          type: richtext
                          required: false
                          searchable: true
                          translatable: true
                          category: Content
                      image:
                          name: { fre-FR: Image }
                          description: { fre-FR: '' }
                          type: image
                          required: false
                          searchable: false
                          translatable: false
                          category: Content
```

the available field types can be found in [content.md](../ibexa/vendor/erdnaxelaweb/staticfakedesign/doc/value_types/content.md)

### Step 2: Create Content Type Migration
Create a migration file for the content type in the proper format:

```yaml
-
    type: content_type
    mode: create
    metadata:
        identifier: article
        contentTypeGroups: [ Content ]
        mainTranslation: fre-FR
        nameSchema: '<title>'
        container: true
        translations: { fre-FR: { name: Article } }
    fields:
        - identifier: title
          type: ezstring
          position: 10
          translations: { fre-FR: { name: 'Titre' } }
          required: true
          searchable: true
        - identifier: intro
          type: ezrichtext
          position: 20
          translations: { fre-FR: { name: 'Introduction' } }
          required: false
          searchable: true
        - identifier: body
          type: ezrichtext
          position: 30
          translations: { fre-FR: { name: 'Corps de texte' } }
          required: false
          searchable: true
        - identifier: image
          type: ezimageasset
          position: 40
          translations: { fre-FR: { name: 'Image' } }
          required: false
          searchable: false
```

### Step 3: Create View Templates
Create templates for each view (full, line, etc.) in `ibexa/templates/themes/site/content/`:
This step can be omitted for content without views

```twig
{# article/full.html.twig #}
{% component {
    name: 'Article full view',
    properties: {
        content: 'content("article")'
    },
    parameters: []
} %}

<article class="article">
    <h1>{{ content.name }}</h1>
    
    {% if content.fields.image is defined and content.fields.image is not empty %}
        <div class="article__image">
            {{ ez_render_field(content.content, 'image') }}
        </div>
    {% endif %}
    
    <div class="article__intro">
        {{ ez_render_field(content.content, 'intro') }}
    </div>
    
    <div class="article__body">
        {{ ez_render_field(content.content, 'body') }}
    </div>
</article>
```

## 2. Adding a New Taxonomy Type

### Step 1: Create Taxonomy Definition
Add the taxonomy entry definition in `ibexa/config/packages/project/static_parameters/taxonomy_definitions.yaml`:

```yaml
ibexa_design_integration:
   system:
      default:
         taxonomy_entry_definition:
            theme_tag:
               models:
                   - name: tag1
                     identifier: tag1
                   - name: tag2
                     identifier: tag2
               fields:
                   name:
                       required: true
                       type: string
                   identifier:
                       required: true
                       type: string
                   parent:
                       required: false
                       type: taxonomy_entry
                       options:
                           type: theme_tag
                           max: 1
```

the available field types can be found in [taxonomy_entry.md](../ibexa/vendor/erdnaxelaweb/staticfakedesign/doc/value_types/taxonomy_entry.md)

### Step 2: Create Taxonomy Migration File
Create a migration file for the taxonomy using a template with variables:

```yaml
###
# Variables :
# - taxonomy_identifier: theme
# - taxonomy_content_type_name: Thème
# - taxonomy_name: Thématiques
###
-
    type: content_type
    mode: create
    metadata:
        identifier: theme_tag
        contentTypeGroups: [ Taxonomy ]
        mainTranslation: fre-FR
        nameSchema: '<name|identifier>'
        container: false
        translations: { fre-FR: { name: Thème } }
    fields:
        - identifier: name
          type: ezstring
          position: 10
          translations: { fre-FR: { name: 'Nom' } }
          required: true
        - identifier: identifier
          type: ezstring
          position: 20
          translations: { fre-FR: { name: 'Identifiant' } }
          required: true
        - identifier: parent
          type: ibexa_taxonomy_entry
          position: 30
          translations: { fre-FR: { name: 'Parent' } }
          required: false
          fieldSettings: { taxonomy: theme }

### FOLDERS
-
    type: content
    mode: create
    metadata:
        contentType: folder
        mainTranslation: fre-FR
        alwaysAvailable: true
        section: { identifier: taxonomy }
    location:
        locationRemoteId: theme_taxonomy_folder
        parentLocationRemoteId: taxonomy_root_folder
    fields:
        - { fieldDefIdentifier: name, languageCode: fre-FR, value: Thématiques }

### PERMISSIONS
-
    type: role
    mode: update
    match: { field: identifier, value: Anonymous }
    policies:
        mode: append
        list:
            - module: taxonomy
              function: read
              limitations:
                  - { identifier: Taxonomy, values: [ 'theme' ] }

### ROOT TAG
-
    type: content
    mode: create
    metadata:
        contentType: theme_tag
        mainTranslation: fre-FR
        alwaysAvailable: true
        section: { identifier: taxonomy }
    location:
        locationRemoteId: theme_taxonomy_tag_root
        parentLocationRemoteId: theme_taxonomy_folder
    fields:
        - { fieldDefIdentifier: identifier, languageCode: fre-FR, value: theme_root }
        - { fieldDefIdentifier: parent, languageCode: fre-FR, value: { taxonomy_entry: ~, taxonomy: theme } }
        - { fieldDefIdentifier: name, languageCode: fre-FR, value: Thématiques }
    references:
        -   name: taxonomy_theme_root_taxonomy_id
            type: taxonomy_id
```

### Step 3: Configure Taxonomy in Ibexa
Update the configuration in `ibexa/config/packages/project/ibexa/taxonomy.yaml`:

```yaml
parameters:
    app.default.taxonomy_menu:
        - tags
        - theme

ibexa_taxonomy:
    taxonomies:
        theme:
            register_main_menu: false
            content_type: theme_tag
            parent_location_remote_id: theme_taxonomy_folder
            field_mappings:
                identifier: identifier
                parent: parent
                name: name
```

## 3. Adding a New Landing Page Block Type

### Step 1: Create Block Definition
Add the block definition in `ibexa/config/packages/project/static_parameters/block_definitions.yaml`:

```yaml
ibexa_design_integration:
   system:
      default:
         block_definition:
            last_articles:
               views:
                   default: '@@ibexadesign/landing_page/block/last_articles.html.twig'
               attributes:
                   title:
                       type: "string"
                       required: false
                   articles:
                       type: "content"
                       required: true
                       options:
                           type: article
                           max: 5
```

the available attributes types can be found in [block.md](../ibexa/vendor/erdnaxelaweb/staticfakedesign/doc/value_types/block.md)

### Step 2: Create Block Template
Create a template in `ibexa/templates/themes/site/landing_page/block/last_articles.html.twig`:
A need at least a `default` view

```twig
{% component {
    name: 'Block "Last Articles"',
    description: 'Block displaying the latest articles with a title',
    properties: {
        block: 'block("last_articles")'
    },
    parameters: []
} %}

<div class="last-articles-block">
    {% if block.attributes.title is not empty %}
        <h2 class="last-articles-block__title">{{ block.attributes.title }}</h2>
    {% endif %}
    
    {% if block.attributes.articles is not empty %}
        <div class="last-articles-block__content">
            <div class="articles-grid">
                {% for article in block.attributes.articles %}
                    <div class="article-card">
                        {% if article.fields.image is defined and article.fields.image is not empty %}
                            <div class="article-card__image">
                                {{ ez_render_field(article.content, 'image', {
                                    'parameters': {
                                        'alias': 'article_card'
                                    }
                                }) }}
                            </div>
                        {% endif %}
                        <div class="article-card__content">
                            <h3 class="article-card__title">
                                <a href="{{ path('ez_urlalias', {'contentId': article.id}) }}">{{ article.name }}</a>
                            </h3>
                            {% if article.fields.intro is defined and article.fields.intro is not empty %}
                                <div class="article-card__intro">
                                    {{ ez_render_field(article.content, 'intro') }}
                                </div>
                            {% endif %}
                        </div>
                    </div>
                {% endfor %}
            </div>
        </div>
    {% endif %}
</div>
```

### Step 3: Configure Block in Ibexa
Update the configuration in `ibexa/config/packages/project/ibexa/landing_page.yaml`:

```yaml
ibexa:
    system:
        default:
            page_builder:
                blocks:
                    last_articles:
                        name: Last Articles
                        category: Content
                        thumbnail: '/assets/images/blocks/last_articles.svg'
                        views:
                            default:
                                template: themes/site/landing_page/block/last_articles.html.twig
                                name: Default
                        attributes:
                            title:
                                type: text
                                name: Title
                                category: Content
                            articles:
                                type: collection
                                name: Articles
                                category: Content
                                validators:
                                    - { name: ContentTypeConstraint, options: { contentTypes: [article] } }
```

## Summary of Required Operations

For each extension type, here's a checklist of necessary operations:

### Content Type Implementation
- ✅ Content type definition in `content_definitions.yaml` with proper structure
- ✅ Content type migration file using the correct format with translations
- ✅ Content type view templates (full, line, etc.)

### Taxonomy Type Implementation
- ✅ Taxonomy entry definition in `taxonomy_definitions.yaml`
- ✅ Taxonomy migration file with content type, folders, permissions and root tag
- ✅ Taxonomy configuration in `taxonomy.yaml`

### Block Type Implementation
- ✅ Block type definition in `block_definitions.yaml`
- ✅ Block view templates
- ✅ Block configuration in `landing_page.yaml`

## Important Notes

1. **Multilingual Support**:
   - Always include translations for field names (at minimum in `fre-FR`)
   - Define proper name schema and URL alias schema for content types

2. **Taxonomy Structure**:
   - Create a root tag for each taxonomy
   - Set up proper permissions for taxonomy access
   - Use references for taxonomy IDs when needed

3. **Migration Best Practices**:
   - Use remote IDs for consistent references across environments
   - Include proper section identifiers (e.g., `taxonomy` for taxonomy content)
   - Set up proper parent-child relationships for taxonomy entries

4. **Cache Management**:
   - Clear cache after adding new content types, taxonomies, or blocks:
     ```bash
     php bin/console cache:clear
     ```
