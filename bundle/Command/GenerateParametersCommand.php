<?php
/*
 * staticfakedesignbundle.
 *
 * @package   DesignBundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaHtmlIntegrationBundle/blob/master/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Command;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class GenerateParametersCommand extends Command
{
    protected static $defaultName = "erdnaxelaweb:static_fake_design:generate_parameters";

    protected static array $typesMapping = [
        "ezboolean" =>	"boolean",
        "ezobjectrelationlist" =>	"content",
        "ezdate" =>	"date",
        "ezdatetime" =>	"datetime",
        "ezemail" =>	"email",
        "ezbinaryfile" =>	"file",
        "ezfloat" =>	"float",
        "ezimage" =>	"image",
        "ezimageasset" =>	"image",
        "ezinteger" =>	"integer",
        "ezgmaplocation" =>	"location",
        "ezrichtext" =>	"richtext",
        "ezselection" =>	"selection",
        "ezstring" =>	"string",
        "eztext" =>	"text",
        "eztime" =>	"time",
        "ezurl" =>	"url",
        "ibexa_taxonomy_entry_assignment" =>	"taxonomy_entry",
        "ezlandingpage" =>	"block",
        "novaseometas" =>	null,
    ];

    protected function configure()
    {
        $this->addArgument( 'lang', InputArgument::REQUIRED, 'Language code' );
        $this->addArgument( 'file', InputArgument::REQUIRED, 'Path to the excel file to import' );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $filePath = $input->getArgument( "file" );
        $languageCode = $input->getArgument( "lang" );
        $spreadsheet = IOFactory::load( $filePath );

        $sheets = $spreadsheet->getAllSheets();
        $config = [];
        foreach ( $sheets as $sheet )
        {
            if ( trim($sheet->getCell( 'A2' )->getValue()) !== "Content name" )
            {
                continue;
            }

            $identifier = trim($sheet->getCell( 'B3' )->getValue());
            if ( !$identifier )
            {
                continue;
            }

            $fieldsStartIndex = 14;
            $fieldsConfig = [];
            do {
                $fieldIdentifier = trim($sheet->getCell( "B$fieldsStartIndex" )->getValue());

                $type = static::$typesMapping[trim($sheet->getCell( "C$fieldsStartIndex"  )->getValue())];
                $fieldsConfig[$fieldIdentifier] = [
                    "name" =>[ $languageCode => trim($sheet->getCell( "A$fieldsStartIndex" )->getValue()) ],
                    "description" =>[ $languageCode => trim($sheet->getCell( "D$fieldsStartIndex" )->getValue()) ],
                    "type" => $type,
                    "required" => trim($sheet->getCell( "E$fieldsStartIndex"  )->getValue()) === "Yes",
                    "searchable" => trim($sheet->getCell( "F$fieldsStartIndex"  )->getValue()) === "Yes",
                    "translatable" => trim($sheet->getCell( "G$fieldsStartIndex"  )->getValue()) === "Yes",
                    "category" => trim($sheet->getCell( "H$fieldsStartIndex"  )->getValue()),
                ];
                $fieldsStartIndex++;
            }while(trim($sheet->getCell( "B$fieldsStartIndex" )->getValue()) !== "");


            $config[$identifier] = [
                "name" => [ $languageCode => trim($sheet->getCell( 'B2' )->getValue()) ],
                "description" => [ $languageCode => trim($sheet->getCell( 'B4' )->getValue())],
                "nameSchema" => trim($sheet->getCell( 'B5' )->getValue()),
                "urlAliasSchema" => trim($sheet->getCell( 'B6' )->getValue()),
                "defaultAlwaysAvailable" => trim($sheet->getCell( 'B9' )->getValue()) === "Yes",
                "defaultSortField" => trim($sheet->getCell( 'D7' )->getCalculatedValue()),
                "defaultSortOrder" => trim($sheet->getCell( 'D8' )->getCalculatedValue()),
                "container" => trim($sheet->getCell( 'B10' )->getValue()) === "Yes",
                "fields" => $fieldsConfig
            ];

        }
        $output->writeln(Yaml::dump($config, 4));
        return Command::SUCCESS;
    }


}
