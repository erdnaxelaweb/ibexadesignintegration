<?php

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Fake\Generator;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\StaticFakeDesign\Fake\FakerGenerator;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\SearchFormGenerator as BaseSearchFormGenerator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchFormGenerator extends BaseSearchFormGenerator
{
    public function __construct(
        protected RequestStack $requestStack,
        protected ChainFilterHandler $filterHandler,
        FormFactoryInterface $formFactory,
        FakerGenerator $fakerGenerator
    ) {
        parent::__construct($requestStack, $formFactory, $fakerGenerator);
    }

    public function getFormTypes(): array
    {
        $formTypes = [];
        foreach ($this->filterHandler->getTypes() as $type) {
            $formTypes[$type] = $this->filterHandler->getFakeFormType($type);
        }

        return $formTypes;
    }
}
