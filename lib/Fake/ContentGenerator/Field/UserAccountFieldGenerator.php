<?php

/*
 * ibexadesignbundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

declare(strict_types=1);

/*
 * Ibexa Design Bundle.
 *
 * @author    Florian ALEXANDRE
 * @copyright 2023-present Florian ALEXANDRE
 * @license   https://github.com/erdnaxelaweb/ibexadesignintegration/blob/main/LICENSE
 */

namespace ErdnaxelaWeb\IbexaDesignIntegration\Fake\ContentGenerator\Field;

use ErdnaxelaWeb\StaticFakeDesign\Fake\ContentGenerator\Field\AbstractFieldGenerator;
use ErdnaxelaWeb\StaticFakeDesign\Fake\FakerGenerator;

class UserAccountFieldGenerator extends AbstractFieldGenerator
{
    public function __construct(
        protected FakerGenerator $fakerGenerator
    ) {
    }


    /**
     * @return array{login: string, email: string, enabled: boolean}
     */
    public function __invoke(): array
    {
        return [
            'login' => $this->fakerGenerator->userName(),
            'email' => $this->fakerGenerator->email(),
            'enabled' => true,
        ];
    }
}
