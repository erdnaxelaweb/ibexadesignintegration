<?php
/*
 * ibexadesignbundle.
 *
 * @package   ibexadesignbundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Migration\Kaliop;

use ErdnaxelaWeb\IbexaDesignIntegration\Migration\Kaliop\Attribute\AttributeMigrationGeneratorInterface;
use ErdnaxelaWeb\IbexaDesignIntegration\Migration\MigrationGenerator;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\ContentConfigurationManager;
use ErdnaxelaWeb\StaticFakeDesign\Configuration\TaxonomyEntryConfigurationManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

class KaliopMigrationGenerator extends MigrationGenerator
{
    protected string $migrationDirectory;

    /**
     * @var AttributeMigrationGeneratorInterface[]
     */
    protected array $attributeMigrationGenerators;

    public function __construct(
        string $kernelProjectDir,
        string $eZMigrationDirectory,
        ContentConfigurationManager $contentConfigurationManager,
        TaxonomyEntryConfigurationManager $taxonomyEntryConfigurationManager,
        iterable $attributeMigrationGenerators
    ) {
        parent::__construct($contentConfigurationManager, $taxonomyEntryConfigurationManager);
        $this->migrationDirectory = $kernelProjectDir . '/src/' . $eZMigrationDirectory;
        foreach ($attributeMigrationGenerators as $type => $attributeMigrationGenerator) {
            $this->attributeMigrationGenerators[$type] = $attributeMigrationGenerator;
        }
    }

    protected function configureOptions(OptionsResolver $optionsResolver): void
    {
    }

    public function generate(): void
    {
        $contentTypes = $this->contentConfigurationManager->getConfigurationsType();
        foreach ($contentTypes as $contentType) {
            $contentTypeConfiguration = $this->contentConfigurationManager->getConfiguration($contentType);

            $name = $contentTypeConfiguration['name'];
            $lang = is_array($name) ? array_key_first($name) : 'eng-GB';
            $attributes = [];
            foreach ($contentTypeConfiguration['fields'] as $fieldIdentifier => $field) {
                $attributeMigrationGenerator = $this->attributeMigrationGenerators[$field['type']];
                $attributes[] = $attributeMigrationGenerator->generate($fieldIdentifier, $field);
            }

            $parameters = [
                'type' => 'content_type',
                'mode' => 'create',
                'content_type_group' => 'Content',
                'identifier' => $contentType,
                'name' => $name,
                'description' => $contentTypeConfiguration['description'],
                'name_pattern' => $contentTypeConfiguration['nameSchema'],
                'url_name_pattern' => $contentTypeConfiguration['urlAliasSchema'],
                'is_container' => $contentTypeConfiguration['container'],
                'default_always_available' => $contentTypeConfiguration['defaultAlwaysAvailable'],
                'default_sort_field' => $contentTypeConfiguration['defaultSortField'],
                'default_sort_order' => $contentTypeConfiguration['defaultSortOrder'],
                'lang' => $lang,
                'attributes' => $attributes,
            ];
            $code = Yaml::dump([$parameters], 5);
            $fileName = date('YmdHis') . '_' . $contentType . '.yml';
            file_put_contents($this->migrationDirectory . '/' . $fileName, $code);
        }
    }
}
