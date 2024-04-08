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

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Command;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class GenerateDefinitionCommand extends Command
{
    protected static $defaultName = "erdnaxelaweb:ibexa_design:generate_definition";

    protected static array $typesMapping = [
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
            "type" => "block",
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

    protected function configure()
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument("file");
        $languageCode = $input->getArgument("lang");
        $sheetNames = $input->getOption('sheets');
        $spreadsheet = IOFactory::load($filePath);

        $sheets = $spreadsheet->getAllSheets();
        $foundContenTypeIdentifiers = [];
        foreach ($sheets as $sheet) {
            if (trim($sheet->getCell('A2')->getValue()) !== "Content name") {
                continue;
            }

            $identifier = trim($sheet->getCell('B3')->getValue());
            if (! $identifier) {
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
                $sheetNames = explode(',', $response);
            }
        }

        $config = [];
        foreach ($sheetNames as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $io->info($sheetName);

            $identifier = trim($sheet->getCell('B3')->getValue());

            $fieldsStartIndex = 14;
            $fieldsConfig = [];
            do {
                $fieldIdentifier = trim($sheet->getCell("B$fieldsStartIndex")->getValue());
                $fieldTypeIdentifier = trim($sheet->getCell("C$fieldsStartIndex")->getValue());
                $typeConfig = static::$typesMapping[$fieldTypeIdentifier] ?? [
                    'type' => null,
                ];
                if (! $typeConfig['type']) {
                    $io->warning(sprintf(
                        'No field type found for field "%s" of type "%s" (Line %d)',
                        $fieldIdentifier,
                        $fieldTypeIdentifier,
                        $fieldsStartIndex
                    ));
                }
                $fieldsConfig[$fieldIdentifier] = [
                    "name" => [
                        $languageCode => trim($sheet->getCell("A$fieldsStartIndex")->getValue()),
                    ],
                    "description" => [
                        $languageCode => trim($sheet->getCell("D$fieldsStartIndex")->getValue()),
                    ],
                    "type" => $typeConfig['type'],
                    "options" => $typeConfig['options'] ?? [],
                    "required" => trim($sheet->getCell("E$fieldsStartIndex")->getValue()) === "Yes",
                    "searchable" => trim($sheet->getCell("F$fieldsStartIndex")->getValue()) === "Yes",
                    "translatable" => trim($sheet->getCell("G$fieldsStartIndex")->getValue()) === "Yes",
                    "category" => trim($sheet->getCell("H$fieldsStartIndex")->getValue()),
                ];
                $fieldsStartIndex++;
            } while (trim($sheet->getCell("B$fieldsStartIndex")->getValue()) !== "");

            $config[$identifier] = [
                "name" => [
                    $languageCode => trim($sheet->getCell('B2')->getValue()),
                ],
                "description" => [
                    $languageCode => trim($sheet->getCell('B4')->getValue()),
                ],
                "nameSchema" => trim($sheet->getCell('B5')->getValue()),
                "urlAliasSchema" => trim($sheet->getCell('B6')->getValue()),
                "defaultAlwaysAvailable" => trim($sheet->getCell('B9')->getValue()) === "Yes",
                "defaultSortField" => trim($sheet->getCell('D7')->getCalculatedValue()),
                "defaultSortOrder" => trim($sheet->getCell('D8')->getCalculatedValue()),
                "container" => trim($sheet->getCell('B10')->getValue()) === "Yes",
                "fields" => $fieldsConfig,
            ];
        }
        $output->writeln(Yaml::dump($config, 4));
        return Command::SUCCESS;
    }
}
