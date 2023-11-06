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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Fake\Generator;

use ErdnaxelaWeb\IbexaDesignIntegration\Pager\Filter\ChainFilterHandler;
use ErdnaxelaWeb\StaticFakeDesign\Fake\FakerGenerator;
use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\SearchFormGenerator as BaseSearchFormGenerator;
use Symfony\Component\Form\FormFactoryInterface;

class SearchFormGenerator extends BaseSearchFormGenerator
{
    public function __construct(
        protected ChainFilterHandler $filterHandler,
        FormFactoryInterface $formFactory,
        FakerGenerator $fakerGenerator
    ) {
        parent::__construct($formFactory, $fakerGenerator);
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
