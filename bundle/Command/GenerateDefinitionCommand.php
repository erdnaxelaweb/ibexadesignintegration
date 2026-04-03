<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Command;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

#[AsCommand('erdnaxelaweb:ibexa_design:generate_definition')]
class GenerateDefinitionCommand extends Command
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected static array $typesMapping = [
        "ibexa_boolean" => [
            "type" => "boolean",
            "options" => [],
        ],
        "ibexa_matrix" => [
            "type" => "matrix",
            "options" => [
                "columns" => [],
            ],
        ],
        "ibexa_object_relation_list" => [
            "type" => "content",
            "options" => [
                "type" => null,
            ],
        ],
        "ibexa_object_relation" => [
            "type" => "content",
            "options" => [
                "type" => null,
                "max" => 1,
            ],
        ],
        "ibexa_date" => [
            "type" => "date",
            "options" => [],
        ],
        "ibexa_datetime" => [
            "type" => "datetime",
            "options" => [],
        ],
        "ibexa_email" => [
            "type" => "email",
            "options" => [],
        ],
        "ibexa_binaryfile" => [
            "type" => "file",
            "options" => [],
        ],
        "ibexa_float" => [
            "type" => "float",
            "options" => [],
        ],
        "ibexa_image" => [
            "type" => "image",
            "options" => [],
        ],
        "ibexa_image_asset" => [
            "type" => "image",
            "options" => [],
        ],
        "ibexa_integer" => [
            "type" => "integer",
            "options" => [],
        ],
        "ibexa_gmap_location" => [
            "type" => "location",
            "options" => [],
        ],
        "ibexa_richtext" => [
            "type" => "richtext",
            "options" => [],
        ],
        "ibexa_selection" => [
            "type" => "selection",
            "options" => [
                "options" => [],
            ],
        ],
        "ibexa_string" => [
            "type" => "string",
            "options" => [],
        ],
        "ibexa_text" => [
            "type" => "text",
            "options" => [],
        ],
        "ibexa_time" => [
            "type" => "time",
            "options" => [],
        ],
        "ibexa_url" => [
            "type" => "url",
            "options" => [],
        ],
        "ibexa_taxonomy_entry_assignment" => [
            "type" => "taxonomy_entry",
            "options" => [
                "type" => null,
            ],
        ],
        "ibexa_landing_page" => [
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
        "ibexa_form" => [
            "type" => 'form',
            "options" => [],
        ],
    ];

    protected function configure(): void
    {
        $this->addArgument('lang', InputArgument::REQUIRED, 'Language code');
        $this->addArgument('file', InputArgument::REQUIRED, 'Path to the excel file to import');
        $this->addOption(
            'sheets',
            'i',
            InputOption::VALUE_IS_ARRAY + InputOption::VALUE_OPTIONAL,
            'Name of sheets for which to generate definition',
            []
        );
        $this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument("file");
        $languageCode = $input->getArgument("lang");
        $sheetNames = $input->getOption('sheets');
        $spreadsheet = IOFactory::load($filePath);

        $sheets = $spreadsheet->getAllSheets();
        $foundContenTypeIdentifiers = [];
        foreach ($sheets as $sheet) {
            if (trim((string) $sheet->getCell('A2')->getValue()) !== "Content name") {
                continue;
            }

            $identifier = trim((string) $sheet->getCell('B3')->getValue());
            if (!$identifier) {
                continue;
            }
            $foundContenTypeIdentifiers[] = $sheet->getTitle();
        }

        if (empty($sheetNames)) {
            $question = new Question(
                sprintf('Content types to generate (%s)', implode(' / ', $foundContenTypeIdentifiers)),
                'all'
            );
            $question->setAutocompleterValues($foundContenTypeIdentifiers);
            $response = $io->askQuestion($question);
            if ($response === "all") {
                $sheetNames = $foundContenTypeIdentifiers;
            } else {
                $sheetNames = explode(',', (string) $response);
            }
        }

        $configs = [];
        foreach ($sheetNames as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $io->info($sheetName);

            $identifier = trim((string) $sheet->getCell('B3')->getValue());

            $fieldsStartIndex = 14;
            $fieldsConfig = [];
            do {
                $fieldIdentifier = trim((string) $sheet->getCell("B$fieldsStartIndex")->getValue());
                $fieldTypeIdentifier = trim((string) $sheet->getCell("C$fieldsStartIndex")->getValue());
                $typeConfig = static::$typesMapping[$fieldTypeIdentifier] ?? [
                    'type' => null,
                ];
                if (!$typeConfig['type']) {
                    $io->warning(sprintf(
                        'No field type found for field "%s" of type "%s" (Line %d)',
                        $fieldIdentifier,
                        $fieldTypeIdentifier,
                        $fieldsStartIndex
                    ));
                    $fieldsStartIndex++;
                    continue;
                }
                $fieldsConfig[$fieldIdentifier] = [
                    "name" => [
                        $languageCode => trim((string) $sheet->getCell("A$fieldsStartIndex")->getValue()),
                    ],
                    "description" => [
                        $languageCode => trim((string) $sheet->getCell("D$fieldsStartIndex")->getValue()),
                    ],
                    "type" => $typeConfig['type'],
                    "options" => $typeConfig['options'] ?? [],
                    "required" => trim((string) $sheet->getCell("E$fieldsStartIndex")->getValue()) === "Yes",
                    "searchable" => trim((string) $sheet->getCell("F$fieldsStartIndex")->getValue()) === "Yes",
                    "translatable" => trim((string) $sheet->getCell("G$fieldsStartIndex")->getValue()) === "Yes",
                    "category" => trim((string) $sheet->getCell("H$fieldsStartIndex")->getValue()),
                ];
                $fieldsStartIndex++;
            } while (trim((string) $sheet->getCell("B$fieldsStartIndex")->getValue()) !== "");

            $configs[$identifier] = [
                "name" => [
                    $languageCode => trim((string) $sheet->getCell('B2')->getValue()),
                ],
                "description" => [
                    $languageCode => trim((string) $sheet->getCell('B4')->getValue()),
                ],
                "nameSchema" => trim((string) $sheet->getCell('B5')->getValue()),
                "urlAliasSchema" => trim((string) $sheet->getCell('B6')->getValue()),
                "defaultAlwaysAvailable" => trim((string) $sheet->getCell('B9')->getValue()) === "Yes",
                "defaultSortField" => trim((string) $sheet->getCell('D7')->getCalculatedValue()),
                "defaultSortOrder" => trim((string) $sheet->getCell('D8')->getCalculatedValue()),
                "container" => trim((string) $sheet->getCell('B10')->getValue()) === "Yes",
                "fields" => $fieldsConfig,
            ];
        }

        $outputPath = $input->getOption('output');
        if ($outputPath) {
            $existingConfigs = Yaml::parse(file_get_contents($outputPath));
            foreach ($configs as $identifier => $config) {
                $newConfig = array_merge_recursive($config, $existingConfigs[$identifier] ?? []);
                foreach ($newConfig['fields'] as $fieldIdentifier => $fieldConfig) {
                    if (!isset($config['fields'][$fieldIdentifier])) {
                        unset($newConfig['fields'][$fieldIdentifier]);
                    }
                }
                $existingConfigs[$identifier] = $newConfig;
            }
            Yaml::dump($existingConfigs, 4);
            $output->writeln('Configs writen to ' . $outputPath);
        } else {
            $output->writeln(Yaml::dump($configs, 4));
        }

        return Command::SUCCESS;
    }
}
