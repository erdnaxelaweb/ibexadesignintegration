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

namespace ErdnaxelaWeb\IbexaDesignIntegrationBundle\Command;

use ErdnaxelaWeb\IbexaDesignIntegration\Migration\MigrationGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateMigrationCommand extends Command
{
    protected static $defaultName = 'erdnaxelaweb:ibexa_design:migration:generate';

    public function __construct(
        protected MigrationGenerator $migrationGeneratorService,
        string                       $name = null
    )
    {
        parent::__construct( $name );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $this->migrationGeneratorService->generate();
        return Command::SUCCESS;
    }
}
