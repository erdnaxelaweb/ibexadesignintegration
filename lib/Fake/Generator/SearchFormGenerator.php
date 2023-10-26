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

use ErdnaxelaWeb\StaticFakeDesign\Fake\Generator\SearchFormGenerator as BaseSearchFormGenerator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchFormGenerator extends BaseSearchFormGenerator
{
    public function getFormTypes(): array
    {
        return [
            'fulltext' => [
                'type' => TextType::class,
            ],
            'content_type' => [
                'type' => ChoiceType::class,
                'options' => [
                    'choices' => array_flip($this->fakerGenerator->words()),
                    'expanded' => false,
                    'multiple' => false,
                ],
            ],
            'custom_field' => [
                'type' => ChoiceType::class,
                'options' => [
                    'choices' => array_flip($this->fakerGenerator->words()),
                    'expanded' => false,
                    'multiple' => false,
                ],
            ],
        ];
    }
}
