<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Migration\Kaliop;

use ErdnaxelaWeb\IbexaDesignIntegration\Migration\Kaliop\Attribute\AttributeMigrationGeneratorInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Migration\MigrationGenerator;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\DefinitionManager;
use ErdnaxelaWeb\StaticFakeDesign\Definition\ContentDefinition;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

class KaliopMigrationGenerator extends MigrationGenerator
{
    protected string $migrationDirectory;

    /**
     * @var AttributeMigrationGeneratorInterface[]
     */
    protected array $attributeMigrationGenerators;

    /**
     * @param iterable<AttributeMigrationGeneratorInterface>                                                       $attributeMigrationGenerators
     */
    public function __construct(
        string $kernelProjectDir,
        string $eZMigrationDirectory,
        DefinitionManager $definitionManager,
        iterable $attributeMigrationGenerators
    ) {
        parent::__construct($definitionManager);
        $this->migrationDirectory = $kernelProjectDir . '/src/' . $eZMigrationDirectory;
        foreach ($attributeMigrationGenerators as $type => $attributeMigrationGenerator) {
            $this->attributeMigrationGenerators[$type] = $attributeMigrationGenerator;
        }
    }

    public function generate(): void
    {
        $contentTypes = $this->definitionManager->getDefinitionsByType(ContentDefinition::class);
        foreach ($contentTypes as $contentType) {
            /** @var \ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentDefinition $contentDefinition */
            $contentDefinition = $this->definitionManager->getDefinition(ContentDefinition::class, $contentType);

            $name = $contentDefinition->getName();
            $lang = is_array($name) ? array_key_first($name) : 'eng-GB';
            $attributes = [];
            foreach ($contentDefinition->getFields() as $fieldIdentifier => $field) {
                $attributeMigrationGenerator = $this->attributeMigrationGenerators[$field->getType()];
                $attributes[] = $attributeMigrationGenerator->generate($fieldIdentifier, $field);
            }

            $parameters = [
                'type' => 'content_type',
                'mode' => 'create',
                'content_type_group' => 'Content',
                'identifier' => $contentType,
                'name' => $name,
                'description' => $contentDefinition->getDescription(),
                'name_pattern' => $contentDefinition->getNameSchema(),
                'url_name_pattern' => $contentDefinition->getUrlAliasSchema(),
                'is_container' => $contentDefinition->isContainer(),
                'default_always_available' => $contentDefinition->isDefaultAlwaysAvailable(),
                'default_sort_field' => $contentDefinition->getDefaultSortField(),
                'default_sort_order' => $contentDefinition->getDefaultSortOrder(),
                'lang' => $lang,
                'attributes' => $attributes,
            ];
            $code = Yaml::dump([$parameters], 5);
            $fileName = date('YmdHis') . '_' . $contentType . '.yml';
            file_put_contents($this->migrationDirectory . '/' . $fileName, $code);
        }
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
    }
}
