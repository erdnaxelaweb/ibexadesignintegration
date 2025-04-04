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

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;

class GenericAttributeMigrationGenerator implements AttributeMigrationGeneratorInterface
{
    public function __construct(
        protected string $type,
        protected array  $optionsMap = []
    ) {
    }

    public function generate(string $fieldIdentifier, ContentFieldDefinition $field): array
    {
        $fieldSettings = [];
        foreach ($field->getOptions() as $option => $value) {
            if (!isset($this->optionsMap[$option])) {
                continue;
            }
            $fieldSettings[$this->optionsMap[$option]] = $value;
        }
        return [
            'identifier' => $fieldIdentifier,
            'type' => $this->type,
            'name' => $field->getName(),
            'description' => $field->getDescription(),
            'required' => $field->isRequired(),
            'searchable' => $field->isSearchable(),
            'info-collector' => $field->isInfoCollector(),
            'disable-translation' => !$field->isTranslatable(),
            'category' => $field->getCategory(),
            'field-settings' => $fieldSettings,
        ];
    }
}
