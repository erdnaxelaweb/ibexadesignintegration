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

namespace ErdnaxelaWeb\IbexaDesignIntegration\Transformer\FieldValue;

use ErdnaxelaWeb\IbexaDesignIntegration\Definition\ContentFieldDefinition;
use ErdnaxelaWeb\IbexaDesignIntegration\Value\AbstractContent;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Base\Exceptions\InvalidArgumentValue;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\IO\IOServiceInterface;

class SvgFieldValueTransformer extends AbstractFieldValueTransformer
{
    public function __construct(
        protected IOServiceInterface $ioService
    ) {
    }

    public function support(?string $ibexaFieldTypeIdentifier): bool
    {
        return $ibexaFieldTypeIdentifier === 'ezbinaryfile';
    }

    protected function transformFieldValue(
        AbstractContent        $content,
        string                 $fieldIdentifier,
        ?FieldDefinition       $ibexaFieldDefinition,
        ContentFieldDefinition $contentFieldDefinition
    ): ?string {
        /** @var \Ibexa\Core\FieldType\BinaryFile\Value $fileFieldValue */
        $fileFieldValue = $content->getFieldValue($fieldIdentifier);

        try {
            $binaryFile = $this->ioService->loadBinaryFile($fileFieldValue->id);
            return $this->ioService->getFileContents($binaryFile);
        } catch (InvalidArgumentValue|NotFoundException $e) {
            return $e->getMessage();
        }
    }
}
