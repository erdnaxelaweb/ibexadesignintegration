<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Migration\Kaliop\Attribute;

class GenericAttributeMigrationGenerator implements AttributeMigrationGeneratorInterface
{
    public function __construct(
        protected string $type,
        protected array  $optionsMap = []
    ) {
    }

    public function generate(string $fieldIdentifier, array $field): array
    {
        $fieldSettings = [];
        foreach ($field['options'] as $option => $value) {
            if (! isset($this->optionsMap[$option])) {
                continue;
            }
            $fieldSettings[$this->optionsMap[$option]] = $value;
        }
        return [
            'identifier' => $fieldIdentifier,
            'type' => $this->type,
            'name' => $field['name'],
            'description' => $field['description'],
            'required' => $field['required'],
            'searchable' => $field['searchable'],
            'info-collector' => $field['infoCollector'],
            'disable-translation' => ! $field['translatable'],
            'category' => $field['category'],
            'field-settings' => $fieldSettings,
        ];
    }
}
