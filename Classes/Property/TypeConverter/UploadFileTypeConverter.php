<?php
declare(strict_types=1);

namespace Featdd\Mailer\Property\TypeConverter;

/***
 *
 * This file is part of the "Mailer" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2021 Daniel Dorndorf <dorndorf@featdd.de>
 *
 ***/

use DateTime;
use Featdd\Mailer\Property\TypeConverter\Exception\UploadFileException;
use Featdd\Mailer\Utility\PathUtility;
use TYPO3\CMS\Core\Resource\Driver\LocalDriver;
use TYPO3\CMS\Core\Resource\Exception as ResourceException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

/**
 * @package Featdd\Mailer\Property\TypeConverter
 */
class UploadFileTypeConverter extends AbstractTypeConverter
{
    /**
     * @var array
     */
    protected $sourceTypes = ['array'];

    /**
     * @var string
     */
    protected $targetType = File::class;

    /**
     * @var int
     */
    protected $priority = 10;

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected ResourceFactory $resourceFactory;

    /**
     * @var \TYPO3\CMS\Core\Resource\Driver\LocalDriver
     */
    protected LocalDriver $localDriver;

    public function __construct()
    {
        $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        $this->localDriver = GeneralUtility::makeInstance(LocalDriver::class);
    }

    /**
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface|null $configuration
     * @return \TYPO3\CMS\Core\Resource\File
     * @throws \Featdd\Mailer\Property\TypeConverter\Exception\UploadFileException
     */
    public function convertFrom($source, string $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null): ?File
    {
        $this->checkUploadError($source);

        if (UPLOAD_ERR_NO_FILE === $source['error']) {
            return null;
        }

        $fileName = $source['name'];
        $tempFilePath = $source['tmp_name'];
        $uploadFolderIdentifier = $configuration->getConfigurationValue(self::class, 'uploadFolderIdentifier');
        $specificUploadFolderName = $configuration->getConfigurationValue(self::class, 'folderName');

        try {
            $uploadFolder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($uploadFolderIdentifier);
        } catch (ResourceException $exception) {
            throw new UploadFileException($exception->getMessage());
        }

        $specificUploadFolderName = PathUtility::sanitizeFileName(
            $this->replaceVariableWithFormData($specificUploadFolderName)
        );

        if (false === $uploadFolder->hasFolder($specificUploadFolderName)) {
            $specificUploadFolder = $uploadFolder->createFolder($specificUploadFolderName);
        } else {
            $specificUploadFolder = $uploadFolder->getSubfolder($specificUploadFolderName);
        }

        try {
            $file = $specificUploadFolder
                ->getStorage()
                ->addFile(
                    $tempFilePath,
                    $specificUploadFolder,
                    $fileName
                );
        } catch (ResourceException $exception) {
            throw new UploadFileException($exception->getMessage());
        }

        if (!$file instanceof File) {
            throw new UploadFileException('Failed to copy uploaded file');
        }

        return $file;
    }

    /**
     * @param array $source
     * @throws \Featdd\Mailer\Property\TypeConverter\Exception\UploadFileException
     */
    protected function checkUploadError(array $source): void
    {
        if (false === is_array($source)) {
            throw new UploadFileException('Not a valid file upload, don\'t to set enctype="multipart/form-data"!?');
        }

        switch ($source['error']) {
            case UPLOAD_ERR_INI_SIZE:
                throw new UploadFileException('Upload size exceeded the limit, see: upload_max_filesize');
            case UPLOAD_ERR_FORM_SIZE:
                throw new UploadFileException('Upload size exceeded the limit defined in the form');
            case UPLOAD_ERR_PARTIAL:
                throw new UploadFileException('Upload was not finished');
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new UploadFileException('Missing temporary folder to handle uploads');
            case UPLOAD_ERR_CANT_WRITE:
                throw new UploadFileException('Can not write to temporary folder');
            case UPLOAD_ERR_EXTENSION:
                throw new UploadFileException('A PHP extension blocked the uploaded file type');
        }
    }

    /**
     * @param string $variableString
     * @return string
     */
    protected function replaceVariableWithFormData(string $variableString): string
    {
        $parameters = GeneralUtility::_GPmerged('tx_mailer_form');
        $variables = [
            'submitDate' => (new DateTime())->format('Y-m-d'),
            'submitDateTime' => (new DateTime())->format('Y-m-d_H-i-s'),
        ];

        if (true === is_array($parameters['form'])) {
            $variables = array_merge_recursive($parameters['form'], $variables);
        }

        foreach ($variables as $key => $value) {
            $variableString = preg_replace('/{(' . preg_quote($key) . ')}/', $value, $variableString);
        }

        return $variableString;
    }
}
