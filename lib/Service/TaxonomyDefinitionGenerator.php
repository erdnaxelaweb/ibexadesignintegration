<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Service;

class TaxonomyDefinitionGenerator
{
    /**
     * @var array<string, array{type: string, options?: array<string, mixed>}>
     */
    protected array $typesMapping = [
        "boolean" => [
            "type" => "boolean",
            "options" => [],
        ],
        "matrix" => [
            "type" => "matrix",
            "options" => [
                "columns" => [],
            ],
        ],
        "content" => [
            "type" => "content",
            "options" => [
                "type" => null,
                "max" => 1,
            ],
        ],
        "date" => [
            "type" => "date",
            "options" => [],
        ],
        "datetime" => [
            "type" => "datetime",
            "options" => [],
        ],
        "email" => [
            "type" => "email",
            "options" => [],
        ],
        "file" => [
            "type" => "file",
            "options" => [],
        ],
        "float" => [
            "type" => "float",
            "options" => [
                "min" => null,
                "max" => null,
            ],
        ],
        "form" => [
            "type" => "form",
            "options" => [
                "fields" => null,
            ],
        ],
        "image" => [
            "type" => "image",
            "options" => [],
        ],
        "integer" => [
            "type" => "integer",
            "options" => [
                "min" => null,
                "max" => null,
            ],
        ],
        "location" => [
            "type" => "location",
            "options" => [],
        ],
        "richtext" => [
            "type" => "richtext",
            "options" => [],
        ],
        "selection" => [
            "type" => "selection",
            "options" => [
                "options" => [],
                "isMultiple" => false,
            ],
        ],
        "string" => [
            "type" => "string",
            "options" => [
                "maxLength" => 255,
            ],
        ],
        "taxonomy_entry" => [
            "type" => "taxonomy_entry",
            "options" => [
                "type" => null,
                "max" => 1,
            ],
        ],
        "text" => [
            "type" => "text",
            "options" => [
                "max" => 10,
            ],
        ],
        "time" => [
            "type" => "time",
            "options" => [],
        ],
        "url" => [
            "type" => "url",
            "options" => [],
        ],
        "blocks" => [
            "type" => "blocks",
            "options" => [
                "layout" => null,
                "allowedTypes" => [],
            ],
        ],
    ];

    /**
     * @param array<string, mixed>  $taxonomyData
     *
     * @return array<string, mixed>
     */
    public function __invoke(array $taxonomyData, string $language = 'fre-FR'): array
    {
        $definitionData = [
            'fields' => $this->processFields($taxonomyData['fields'], $language),
            'name' => [
                $language => $this->extractTranslatableValue($taxonomyData['name'], $language),
            ],
            'description' => [
                $language => $this->extractTranslatableValue($taxonomyData['description'], $language),
            ],
        ];

        // Include models only if they exist in the source
        if (isset($taxonomyData['models']) && is_array($taxonomyData['models']) && !empty($taxonomyData['models'])) {
            $definitionData['models'] = $taxonomyData['models'];
        }

        return $definitionData;
    }

    private function extractTranslatableValue(mixed $value, string $language): mixed
    {
        if (is_array($value) && isset($value[$language])) {
            return $value[$language];
        }

        return $value;
    }

    /**
     * @param array<string, mixed>  $fields
     *
     * @return array<string, mixed>
     */
    private function processFields(array $fields, string $language): array
    {
        $fieldDefinitions = [];

        foreach ($fields as $identifier => $field) {
            $targetType = $this->typesMapping[$field['type']]['type'];
            $targetOptions = $this->typesMapping[$field['type']]['options'] ?? [];

            $fieldDefinition = [
                'type' => $targetType,
                'required' => $field['required'],
            ];

            // Process options based on field type
            if (isset($field['options'])) {
                $this->processFieldOptions($fieldDefinition, $field['options'], $targetType);
            }

            $fieldDefinitions[$identifier] = $fieldDefinition;
        }

        return $fieldDefinitions;
    }

    /**
     * @param array<string, mixed>  $fieldDefinition
     * @param array<string, mixed>       $options
     */
    private function processFieldOptions(array &$fieldDefinition, array $options, string $targetType): void
    {
        switch ($targetType) {
            case 'matrix':
                if (isset($options['columns'])) {
                    $fieldDefinition['options']['columns'] = $options['columns'];
                }
                if (isset($options['minimumRows'])) {
                    $fieldDefinition['options']['minimumRows'] = $options['minimumRows'];
                }
                break;

            case 'content':
                if (isset($options['type'])) {
                    $fieldDefinition['options']['type'] = $options['type'];
                }
                if (isset($options['max'])) {
                    $fieldDefinition['options']['max'] = $options['max'];
                }
                break;

            case 'float':
            case 'integer':
                if (isset($options['min'])) {
                    $fieldDefinition['options']['min'] = $options['min'];
                }
                if (isset($options['max'])) {
                    $fieldDefinition['options']['max'] = $options['max'];
                }
                break;

            case 'selection':
                if (isset($options['options'])) {
                    $fieldDefinition['options']['options'] = $options['options'];
                }
                if (isset($options['isMultiple'])) {
                    $fieldDefinition['options']['isMultiple'] = $options['isMultiple'];
                }
                break;

            case 'string':
                if (isset($options['maxLength'])) {
                    $fieldDefinition['options']['maxLength'] = $options['maxLength'];
                }
                break;

            case 'taxonomy_entry':
                if (isset($options['type'])) {
                    $fieldDefinition['options']['type'] = $options['type'];
                }
                if (isset($options['max'])) {
                    $fieldDefinition['options']['max'] = $options['max'];
                }
                break;

            case 'text':
                if (isset($options['max'])) {
                    $fieldDefinition['options']['max'] = $options['max'];
                }
                break;

            case 'blocks':
                if (isset($options['layout'])) {
                    $fieldDefinition['options']['layout'] = $options['layout'];
                }
                if (isset($options['allowedTypes'])) {
                    $fieldDefinition['options']['allowedTypes'] = $options['allowedTypes'];
                }
                break;

            case 'form':
                if (isset($options['fields'])) {
                    $fieldDefinition['options']['fields'] = $options['fields'];
                }
                break;
        }
    }
}
