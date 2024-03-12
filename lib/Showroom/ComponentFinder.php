<?php

namespace ErdnaxelaWeb\IbexaDesignIntegration\Showroom;

use ErdnaxelaWeb\StaticFakeDesign\Showroom\ComponentFinder as BaseComponentFinder;
use ErdnaxelaWeb\StaticFakeDesign\Value\Component;
use Ibexa\DesignEngine\DesignAwareTrait;
use Symfony\Component\Finder\Finder;
use Twig\Environment;

class ComponentFinder extends BaseComponentFinder
{
    use DesignAwareTrait;

    public function __construct(
        Environment     $twig,
        string          $baseDir,
        protected array $designList,
        protected array $templatePathsMap
    ) {
        parent::__construct($twig, $baseDir);
    }

    protected function getFinder(): Finder
    {
        $finder = new Finder();

        $designThemes = $this->designList[$this->getCurrentDesign()];
        foreach ($designThemes as $designTheme) {
            $paths = $this->templatePathsMap[$designTheme];
            $paths = array_filter($paths, function ($path) {
                return str_starts_with($path, $this->baseDir);
            });
            $finder->in($paths);
        }

        $finder
            ->name('*.html.twig')
            ->files();

        return $finder;
    }

    public function getComponentFromTemplatePath(string $templatePath): ?Component
    {
        return parent::getComponentFromTemplatePath(sprintf('@ibexadesign/%s', $templatePath));
    }
}
