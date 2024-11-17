<?php

declare(strict_types=1);

/*
 * This file is part of the bjoern-hempel/php-calendar-api project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Calendar\Transport\Controller\Admin;

use App\Calendar\Transport\Controller\Admin\Base\BaseCrudController;
use App\Media\Application\Service\Entity\ImageLoaderService;
use App\Media\Application\Service\ImageService;
use App\Media\Application\Utils\ImageProperty;
use App\Media\Domain\Entity\Image;
use App\Platform\Application\Utils\JsonConverter;
use App\User\Application\Service\Entity\UserLoaderService;
use App\User\Application\Service\IdHashService;
use App\User\Application\Service\SecurityService;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ImageCrudController.
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-12)
 * @package App\Controller\Admin
 */
class ImageCrudController extends BaseCrudController
{
    protected const RAW_SQL_POSITION = <<<SQL
SELECT
    path
FROM
    image
WHERE
    id=%d;
SQL;

    /**
     * @throws Exception
     */
    public function __construct(
        protected ImageProperty $imageProperty,
        protected ImageLoaderService $imageLoaderService,
        protected UserLoaderService $userLoaderService,
        protected RequestStack $requestStack,
        protected ImageService $imageService,
        protected IdHashService $idHashService,
        SecurityService $securityService,
        TranslatorInterface $translator
    ) {
        parent::__construct($securityService, $translator);
    }

    /**
     * Return fqcn of this class.
     */
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    /**
     * Returns the entity of this class.
     */
    #[Pure]
    public function getEntity(): string
    {
        return self::getEntityFqcn();
    }

    /**
     * Overwrite persistEntity method.
     *
     * @param Image $entityInstance
     * @throws Exception
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Image) {
            throw new Exception(sprintf('Unexpected entity class (%s:%d)', __FILE__, __LINE__));
        }

        $image = $entityInstance;

        $this->updateImageProperties($image);

        parent::persistEntity($entityManager, $image);
    }

    /**
     * Overwrite updateEntity method.
     *
     * @param Image $entityInstance
     * @throws Exception
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Image) {
            throw new Exception(sprintf('Unexpected entity class (%s:%d)', __FILE__, __LINE__));
        }

        $image = $entityInstance;

        if ($image->getPath() === null) {
            $this->updateImagePath($entityManager, $image);
        } else {
            $this->updateImageProperties($image);
        }

        parent::updateEntity($entityManager, $image);
    }

    /**
     * Returns the field by given name.
     *
     * @throws Exception
     */
    protected function getField(string $fieldName): FieldInterface
    {
        switch ($fieldName) {
            case 'path':
            case 'pathTarget':
                $idHash = $this->idHashService->getIdHash($this->getEntityInstance());

                /* Create source and target path if needed. */
                $this->imageService->checkPath($idHash);

                return ImageField::new($fieldName)
                    ->setBasePath(sprintf('%s/%s', Image::PATH_DATA, Image::PATH_IMAGES))
                    ->setUploadDir(sprintf('%s/%s/%s/%s', Image::PATH_DATA, Image::PATH_IMAGES, $idHash, Image::PATH_TYPE_SOURCE))
                    ->setUploadedFileNamePattern(
                        fn (UploadedFile $file) => sprintf(
                            '%s/%s/%s.%s',
                            $idHash,
                            Image::PATH_TYPE_SOURCE,
                            substr(md5(sprintf('%s.%s', $file->getClientOriginalName(), random_int(1000, 9999))), 0, 10),
                            $file->getClientOriginalName()
                        )
                    )
                    ->setRequired(false)
                    ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                    ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));

            case 'pathSourcePreview':
            case 'pathTargetPreview':
                return ImageField::new($fieldName)
                    ->setTemplatePath('admin/crud/field/image_preview.html.twig')
                    ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                    ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));

            case 'latitude':
            case 'longitude':
                return NumberField::new($fieldName)
                    ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                    ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName))
                    ->setNumberFormat('%.3f°');

            case 'iso':
                return NumberField::new($fieldName)
                    ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                    ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName))
                    ->setNumberFormat('%d');

            case 'gpsHeight':
                return NumberField::new($fieldName)
                    ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                    ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName))
                    ->setNumberFormat('%d m');

            case 'information':
                return CodeEditorField::new($fieldName)
                    ->setTemplatePath('admin/crud/field/code_editor.html.twig')
                    /* Not called within formulas. */
                    ->formatValue(
                        fn ($json) => (new JsonConverter($json))->getBeautified(2)
                    )
                    ->setLanguage('css')
                    ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                    ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));

            case 'takenAt':
                return DateTimeField::new($fieldName)
                    ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                    ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));
        }

        return parent::getField($fieldName);
    }

    /**
     * Updates the image properties
     *
     * @throws Exception
     */
    protected function updateImageProperties(Image $image): void
    {
        if (!$this->securityService->isGrantedByAnAdmin()) {
            $image->setUser($this->securityService->getUser());
        }

        if ($image->getUser() === null) {
            throw new Exception(sprintf('User expected (%s:%d).', __FILE__, __LINE__));
        }

        $this->imageProperty->init($image->getUser(), $image);
    }

    /**
     * Get original image path.
     *
     * @throws DoctrineDBALException
     * @throws Exception
     */
    protected function getImagePath(EntityManagerInterface $entityManager, Image $image): string
    {
        $connection = $entityManager->getConnection();
        $sqlRaw = sprintf(self::RAW_SQL_POSITION, $image->getId());

        $statement = $connection->prepare($sqlRaw);
        $result = $statement->executeQuery();

        /* Reads all results. */
        if (($row = $result->fetchAssociative()) !== false) {
            return strval($row['path']);
        }

        throw new Exception(sprintf('Unable to find image with id %d (%s:%d).', $image->getId(), __FILE__, __LINE__));
    }

    /**
     * Updates image path from original image.
     *
     * @throws Exception
     */
    protected function updateImagePath(EntityManagerInterface $entityManager, Image $image): void
    {
        $image->setPath($this->getImagePath($entityManager, $image));
    }
}
