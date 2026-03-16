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

use ErdnaxelaWeb\IbexaDesignIntegration\Migration\MigrationGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('erdnaxelaweb:ibexa_design:migration:generate')]
class GenerateMigrationCommand extends Command
{
    public function __construct(
        protected MigrationGenerator $migrationGeneratorService,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->migrationGeneratorService->generate();
        return Command::SUCCESS;
    }
}
