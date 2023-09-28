## The rules for the magic to work

1. A page template should always be associated to a content
2. A page template name should follow the following patern : `full/<content type>.html.twig`

   Example : `full/article.html.twig`
3. The variable name use to store the content associated to a page should always be named `content`

   Example for an "article" page :
    ```
    {# @fake content content('article') }}
    ```

4. Use the following macro to display a content and try to follow the following patern for the name of the template
   used : `<view type>/<content type>.html.twig`
    ```
    {{ macros.display_content(...template to used, ...content to display, {...parameters}, ...view type) }}
    ```
   Example :
    ```
    {% import '@ibexadesign/macros.html.twig' as macros %}
    {# @fake pager pager('article') }}
    {% for article in pager %}
        {{ macros.display_content('@ibexadesign/list/article.html.twig', article, {}, 'list') }}
    {% endfor %}
    {{ pagerfanta(pager) }}
    ```
5. Try to use an include when generating a recurring pattern like an image

