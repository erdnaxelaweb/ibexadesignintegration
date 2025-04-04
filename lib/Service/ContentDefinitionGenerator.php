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

class ContentDefinitionGenerator
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $typesMapping = [
        "ezboolean" => [
            "type" => "boolean",
            "options" => [],
        ],
        "ezmatrix" => [
            "type" => "matrix",
            "options" => [
                "columns" => [],
            ],
        ],
        "ezobjectrelationlist" => [
            "type" => "content",
            "options" => [
                "type" => null,
            ],
        ],
        "ezobjectrelation" => [
            "type" => "content",
            "options" => [
                "type" => null,
                "max" => 1,
            ],
        ],
        "ezdate" => [
            "type" => "date",
            "options" => [],
        ],
        "ezdatetime" => [
            "type" => "datetime",
            "options" => [],
        ],
        "ezemail" => [
            "type" => "email",
            "options" => [],
        ],
        "ezbinaryfile" => [
            "type" => "file",
            "options" => [],
        ],
        "ezfloat" => [
            "type" => "float",
            "options" => [],
        ],
        "ezimage" => [
            "type" => "image",
            "options" => [],
        ],
        "ezimageasset" => [
            "type" => "image",
            "options" => [],
        ],
        "ezinteger" => [
            "type" => "integer",
            "options" => [],
        ],
        "ezgmaplocation" => [
            "type" => "location",
            "options" => [],
        ],
        "ezrichtext" => [
            "type" => "richtext",
            "options" => [],
        ],
        "ezselection" => [
            "type" => "selection",
            "options" => [
                "options" => [],
            ],
        ],
        "ezstring" => [
            "type" => "string",
            "options" => [],
        ],
        "eztext" => [
            "type" => "text",
            "options" => [],
        ],
        "eztime" => [
            "type" => "time",
            "options" => [],
        ],
        "ezurl" => [
            "type" => "url",
            "options" => [],
        ],
        "ibexa_taxonomy_entry_assignment" => [
            "type" => "taxonomy_entry",
            "options" => [
                "type" => null,
            ],
        ],
        "ezlandingpage" => [
            "type" => "blocks",
            "options" => [
                'layout' => null,
                'allowedTypes' => [],
            ],
        ],
        "novaseometas" => [
            "type" => null,
            "options" => [],
        ],
        "ezform" => [
            "type" => 'form',
            "options" => [],
        ],
    ];

    /**
     * @param array<string, mixed>  $contentData
     *
     * @return array<string, mixed>
     */
    public function __invoke(array $contentData, string $language = 'fre-FR'): array
    {
        $definitionData = [
            'fields' => $this->processFields($contentData['fields'], $language),
            'name' => [
                $language => $this->extractTranslatableValue($contentData['name'], $language),
            ],
            'description' => [
                $language => $this->extractTranslatableValue($contentData['description'], $language),
            ],
        ];

        $propertyMappings = [
            'nameSchema',
            'urlAliasSchema',
            'defaultAlwaysAvailable',
            'container',
            'defaultSortField',
            'defaultSortOrder',
        ];

        foreach ($propertyMappings as $property) {
            if (isset($contentData[$property])) {
                $definitionData[$property] = $contentData[$property];
            }
        }

        // Include models only if they exist in the source
        if (is_array($contentData['models']) && !empty($contentData['models'])) {
            $definitionData['models'] = $contentData['models'];
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
                'name' => [
                    $language => $this->extractTranslatableValue($field['name'], $language),
                ],
                'description' => [
                    $language => $this->extractTranslatableValue($field['description'], $language),
                ],
                'type' => $targetType,
                'options' => $targetOptions,
            ];

            if ($targetType === 'content') {
                $fieldDefinition['options']['type'] = $field['options'];
            }

            if ($targetType === 'selection') {
                $fieldDefinition['options']['options'] = $field['options'];
            }

            if ($targetType === 'taxonomy') {
                $fieldDefinition['options']['type'] = $field['options'];
            }

            if ($targetType === 'blocks') {
                $fieldDefinition['options']['allowed_types'] = $field['options'];
            }

            // Add other field properties, but only if they exist
            $fieldProperties = ['required', 'translatable', 'searchable', 'category'];

            foreach ($fieldProperties as $property) {
                if (isset($field[$property])) {
                    $fieldDefinition[$property] = $field[$property];
                }
            }

            $fieldDefinitions[$identifier] = $fieldDefinition;
        }

        return $fieldDefinitions;
    }
}
