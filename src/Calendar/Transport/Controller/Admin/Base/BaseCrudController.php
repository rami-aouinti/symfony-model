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

namespace App\Calendar\Transport\Controller\Admin\Base;

use App\Calendar\Domain\Entity\Calendar;
use App\Calendar\Domain\Entity\CalendarImage;
use App\Calendar\Domain\Entity\CalendarStyle;
use App\Calendar\Domain\Entity\Holiday;
use App\Calendar\Domain\Entity\HolidayGroup;
use App\Calendar\Transport\Field\CollectionCalendarImageField;
use App\Calendar\Transport\Field\CollectionHolidayField;
use App\Event\Domain\Entity\Event;
use App\Media\Application\Utils\SizeConverter;
use App\Media\Domain\Entity\Image;
use App\Media\Transport\Field\PathImageField;
use App\Platform\Application\Utils\EasyAdminField;
use App\Platform\Application\Utils\JsonConverter;
use App\User\Application\Service\SecurityService;
use App\User\Domain\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class BaseCrudController.
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 1.0 (2022-02-12)
 * @package App\Controller\Admin\Base
 */
abstract class BaseCrudController extends AbstractCrudController
{
    protected const string CRUD_FIELDS_ADMIN = 'CRUD_FIELDS_ADMIN';

    protected string $crudName;

    protected EasyAdminField $easyAdminField;

    /**
     * @throws Exception
     */
    public function __construct(
        protected SecurityService $securityService,
        protected TranslatorInterface $translator
    ) {
        $this->easyAdminField = new EasyAdminField($this->getCrudName());
    }
    abstract public function getEntity(): string;

    /**
     * Returns the entity of this class.
     *
     * @throws Exception
     */
    public function getCrudName(?string $entity = null, bool $doNotUseCache = false): string
    {
        if ($entity === null && isset($this->crudName) && !$doNotUseCache) {
            return $this->crudName;
        }

        $split = preg_split('~\\\\~', $entity ?? $this->getEntity());

        if ($split === false) {
            throw new Exception(sprintf('Unable to split string (%s:%d)', __FILE__, __LINE__));
        }

        $crudName = lcfirst($split[count($split) - 1]);

        if ($entity === null) {
            $this->crudName = $crudName;
        }

        return $crudName;
    }

    /**
     * Returns the constant from fqcn entity.
     *
     * @return string[]
     * @throws Exception
     */
    public function getConstant(string $name): array
    {
        $constant = $this->getConstantRaw($name);

        /* Check given constant */
        if (
            !in_array(serialize($constant), array_unique([
                serialize(Calendar::CRUD_FIELDS_ADMIN),
                serialize(Calendar::CRUD_FIELDS_REGISTERED),
                serialize(Calendar::CRUD_FIELDS_INDEX),
                serialize(Calendar::CRUD_FIELDS_NEW),
                serialize(Calendar::CRUD_FIELDS_EDIT),
                serialize(Calendar::CRUD_FIELDS_DETAIL),
                serialize(Calendar::CRUD_FIELDS_FILTER),

                serialize(CalendarImage::CRUD_FIELDS_ADMIN),
                serialize(CalendarImage::CRUD_FIELDS_REGISTERED),
                serialize(CalendarImage::CRUD_FIELDS_INDEX),
                serialize(CalendarImage::CRUD_FIELDS_NEW),
                serialize(CalendarImage::CRUD_FIELDS_EDIT),
                serialize(CalendarImage::CRUD_FIELDS_DETAIL),
                serialize(CalendarImage::CRUD_FIELDS_FILTER),

                serialize(CalendarStyle::CRUD_FIELDS_ADMIN),
                serialize(CalendarStyle::CRUD_FIELDS_REGISTERED),
                serialize(CalendarStyle::CRUD_FIELDS_INDEX),
                serialize(CalendarStyle::CRUD_FIELDS_NEW),
                serialize(CalendarStyle::CRUD_FIELDS_EDIT),
                serialize(CalendarStyle::CRUD_FIELDS_DETAIL),
                serialize(CalendarStyle::CRUD_FIELDS_FILTER),

                serialize(Event::CRUD_FIELDS_ADMIN),
                serialize(Event::CRUD_FIELDS_REGISTERED),
                serialize(Event::CRUD_FIELDS_INDEX),
                serialize(Event::CRUD_FIELDS_NEW),
                serialize(Event::CRUD_FIELDS_EDIT),
                serialize(Event::CRUD_FIELDS_DETAIL),
                serialize(Event::CRUD_FIELDS_FILTER),

                serialize(Holiday::CRUD_FIELDS_ADMIN),
                serialize(Holiday::CRUD_FIELDS_REGISTERED),
                serialize(Holiday::CRUD_FIELDS_INDEX),
                serialize(Holiday::CRUD_FIELDS_NEW),
                serialize(Holiday::CRUD_FIELDS_EDIT),
                serialize(Holiday::CRUD_FIELDS_DETAIL),
                serialize(Holiday::CRUD_FIELDS_FILTER),

                serialize(HolidayGroup::CRUD_FIELDS_ADMIN),
                serialize(HolidayGroup::CRUD_FIELDS_REGISTERED),
                serialize(HolidayGroup::CRUD_FIELDS_INDEX),
                serialize(HolidayGroup::CRUD_FIELDS_NEW),
                serialize(HolidayGroup::CRUD_FIELDS_EDIT),
                serialize(HolidayGroup::CRUD_FIELDS_DETAIL),
                serialize(HolidayGroup::CRUD_FIELDS_FILTER),

                serialize(Image::CRUD_FIELDS_ADMIN),
                serialize(Image::CRUD_FIELDS_REGISTERED),
                serialize(Image::CRUD_FIELDS_INDEX),
                serialize(Image::CRUD_FIELDS_NEW),
                serialize(Image::CRUD_FIELDS_EDIT),
                serialize(Image::CRUD_FIELDS_DETAIL),
                serialize(Image::CRUD_FIELDS_FILTER),

                serialize(User::CRUD_FIELDS_ADMIN),
                serialize(User::CRUD_FIELDS_REGISTERED),
                serialize(User::CRUD_FIELDS_INDEX),
                serialize(User::CRUD_FIELDS_NEW),
                serialize(User::CRUD_FIELDS_EDIT),
                serialize(User::CRUD_FIELDS_DETAIL),
                serialize(User::CRUD_FIELDS_FILTER),
            ]))
        ) {
            throw new Exception(sprintf('Unsupported constant (%s:%d).', __FILE__, __LINE__));
        }

        if ($this->securityService->isGrantedByAnAdmin()) {
            return $constant;
        }

        return array_diff($constant, $this->getConstantRaw(self::CRUD_FIELDS_ADMIN));
    }

    /**
     * Configure actions.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE]);
    }

    /**
     * Configures the fields to be displayed (index page)
     *
     * @return FieldInterface[]
     * @throws Exception
     */
    public function configureFieldsIndex(): iterable
    {
        foreach ($this->getConstant('CRUD_FIELDS_INDEX') as $fieldName) {
            yield $this->getField($fieldName);
        }
    }

    /**
     * Configures the fields to be displayed (new page)
     *
     * @return FieldInterface[]
     * @throws Exception
     */
    public function configureFieldsNew(): iterable
    {
        foreach ($this->getConstant('CRUD_FIELDS_NEW') as $fieldName) {
            yield $this->getField($fieldName);
        }
    }

    /**
     * Configures the fields to be displayed (edit page)
     *
     * @return FieldInterface[]
     * @throws Exception
     */
    public function configureFieldsEdit(): iterable
    {
        foreach ($this->getConstant('CRUD_FIELDS_EDIT') as $fieldName) {
            yield $this->getField($fieldName);
        }
    }

    /**
     * Configures the fields to be displayed (detail page)
     *
     * @return FieldInterface[]
     * @throws Exception
     */
    public function configureFieldsDetail(): iterable
    {
        foreach ($this->getConstant('CRUD_FIELDS_DETAIL') as $fieldName) {
            yield $this->getField($fieldName);
        }
    }

    /**
     * Configures the fields to be displayed.
     *
     * @return FieldInterface[]
     * @throws Exception
     */
    public function configureFields(string $pageName): iterable
    {
        return match ($pageName) {
            Crud::PAGE_NEW => $this->configureFieldsNew(),
            Crud::PAGE_EDIT => $this->configureFieldsEdit(),
            Crud::PAGE_DETAIL => $this->configureFieldsDetail(),
            default => $this->configureFieldsIndex(),
        };
    }

    /**
     * Configures crud.
     *
     * @throws Exception
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(sprintf('admin.%s.singular', $this->getCrudName()))
            ->setEntityLabelInPlural(sprintf('admin.%s.plural', $this->getCrudName()))
            ->overrideTemplate('crud/detail', 'admin/crud/detail.html.twig')
            ->overrideTemplate('crud/index', 'admin/crud/index.html.twig');
    }

    /**
     * Configures assets.
     */
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            // adds the CSS and JS assets associated to the given Webpack Encore entry
            // it's equivalent to adding these inside the <head> element:
            // {{ encore_entry_link_tags('...') }} and {{ encore_entry_script_tags('...') }}
            //->addWebpackEncoreEntry('admin-app')

            // it's equivalent to adding this inside the <head> element:
            // <link rel="stylesheet" href="{{ asset('...') }}">
            //->addCssFile('build/admin.css')
            //->addCssFile('https://example.org/css/admin2.css')

            // it's equivalent to adding this inside the <head> element:
            // <script src="{{ asset('...') }}"></script>
            ->addJsFile('js/easyadmin.js')
            //->addJsFile('https://example.org/js/admin2.js')

            // use these generic methods to add any code before </head> or </body>
            // the contents are included "as is" in the rendered page (without escaping them)
            //->addHtmlContentToHead('<link rel="dns-prefetch" href="https://assets.example.com">')
            //->addHtmlContentToBody('<script> ... </script>')
            //->addHtmlContentToBody('<!-- generated at '.time().' -->')
        ;
    }

    /**
     * Filters list by roles.
     *
     * @throws Exception
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return match ($this->getEntity()) {
            /* Filter classes by user */
            Calendar::class, CalendarImage::class, Event::class, Image::class => $this->addUserFilter($qb),
            User::class => $this->addUserFilter($qb, true),

            /* Disable classes for user */
            CalendarStyle::class, Holiday::class, HolidayGroup::class => $this->checkPermissions($qb, $this->getEntity()),

            /* Do not filter */
            default => $qb,
        };
    }

    /**
     * Configures filters.
     *
     * @throws Exception
     */
    public function configureFilters(Filters $filters): Filters
    {
        $filterFields = $this->getConstant('CRUD_FIELDS_FILTER');

        foreach ($filterFields as $filterField) {
            $filters->add($filterField);
        }

        return $filters;
    }

    /**
     * Returns the entity instance if possible.
     */
    protected function getEntityInstance(): ?object
    {
        return $this->getContext()?->getEntity()->getInstance();
    }

    /**
     * Returns the year selection.
     *
     * @return int[]
     */
    protected function getYearSelection(): array
    {
        $years = range(intval(date('Y')) - 3, intval(date('Y') + 10));

        return array_combine($years, $years);
    }

    /**
     * Returns month selection.
     *
     * @return int[]
     */
    protected function getMonthSelection(): array
    {
        return [
            'title' => 0,
            'january' => 1,
            'february' => 2,
            'march' => 3,
            'april' => 4,
            'may' => 5,
            'june' => 6,
            'july' => 7,
            'august' => 8,
            'september' => 9,
            'october' => 10,
            'november' => 11,
            'december' => 12,
        ];
    }

    /**
     * Returns the field by given name.
     *
     * @throws Exception
     */
    protected function getField(string $fieldName): FieldInterface
    {
        /* Check if given field name is a registered name. */
        if (!in_array($fieldName, $this->getConstant('CRUD_FIELDS_REGISTERED'))) {
            throw new Exception(sprintf('Unknown FieldInterface "%s" (%s:%d).', $fieldName, __FILE__, __LINE__));
        }

        /* Special crud names. */
        switch ($this->getCrudName()) {
            /* Calendar */
            case $this->getCrudName(Calendar::class):
                switch ($fieldName) {
                    /* Association field. */
                    case 'user':
                    case 'holidayGroup':
                    case 'calendarStyle':
                        return AssociationField::new($fieldName)
                            ->setRequired(true)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));

                        /* Collection fields */
                    case 'calendarImages':
                        return CollectionCalendarImageField::new($fieldName)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));

                        /* Choice field */
                    case 'defaultYear':
                        return $this->easyAdminField->getChoiceField($fieldName, $this->getYearSelection());
                }
                break;
                /* CalendarImage */
            case $this->getCrudName(CalendarImage::class):
                switch ($fieldName) {
                    case 'year':
                        return $this->easyAdminField->getChoiceField($fieldName, $this->getYearSelection());
                    case 'month':
                        return $this->easyAdminField->getChoiceField($fieldName, $this->getMonthSelection(), true);
                    case 'pathSource':
                    case 'pathTarget':
                        return ImageField::new($fieldName)
                            //->setBasePath(sprintf('%s/%s', Image::PATH_DATA, Image::PATH_IMAGES))
                            ->setTemplatePath('admin/crud/field/image_preview.html.twig')
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));

                    case 'pathSourcePreview':
                    case 'pathTargetPreview':
                        return ImageField::new($fieldName)
                            ->setTemplatePath('admin/crud/field/image_preview.html.twig')
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));
                }
                break;
                /* Event */
            case $this->getCrudName(Event::class):
                switch ($fieldName) {
                    /* Association field. */
                    case 'user':
                        return AssociationField::new($fieldName)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName))
                            ->setRequired(true);

                        /* Field type */
                    case 'type':
                        return ChoiceField::new($fieldName)
                            ->setChoices([
                                sprintf('admin.%s.fields.type.entries.entry%d', $this->getCrudName(), 0) => 0,
                                sprintf('admin.%s.fields.type.entries.entry%d', $this->getCrudName(), 1) => 1,
                                sprintf('admin.%s.fields.type.entries.entry%d', $this->getCrudName(), 2) => 2,
                            ])
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));
                }
                break;
                /* Holiday */
            case $this->getCrudName(Holiday::class):
                switch ($fieldName) {
                    /* Association field. */
                    case 'holidayGroup':
                        return AssociationField::new($fieldName)
                            ->setRequired(true)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));

                        /* Field type */
                    case 'type':
                        return ChoiceField::new($fieldName)
                            ->setChoices([
                                sprintf('admin.%s.fields.type.entries.entry%d', $this->getCrudName(), 0) => 0,
                                sprintf('admin.%s.fields.type.entries.entry%d', $this->getCrudName(), 1) => 1,
                            ])
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));
                }
                break;
                /* HolidayGroup */
            case $this->getCrudName(HolidayGroup::class):
                switch ($fieldName) {
                    /* Collection fields */
                    case 'holidays':
                    case 'holidaysGrouped':
                        return CollectionHolidayField::new($fieldName)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));
                }
                break;
                /* Image */
            case $this->getCrudName(Image::class):
                switch ($fieldName) {
                    /* Association field. */
                    case 'user':
                        return AssociationField::new($fieldName)
                            ->setRequired(true)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));

                        /* Property fields */
                    case 'name':
                        return TextField::new($fieldName)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));

                        /* Dimension fields. */
                    case 'width':
                    case 'height':
                        return IntegerField::new($fieldName)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName))
                            ->formatValue(fn ($value) => sprintf('%d px', $value));

                        /* Size field */
                    case 'size':
                        return IntegerField::new($fieldName)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName))
                            ->formatValue(fn ($value) => SizeConverter::getHumanReadableSize($value));

                        /* Full path fields */
                    case 'pathSourceFull':
                    case 'pathTargetFull':
                        return PathImageField::new($fieldName)
                            ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                            ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName));
                }
                break;
                /* User */
            case $this->getCrudName(User::class):
                switch ($fieldName) {
                    /* Email field */
                    case 'email':
                        return $this->easyAdminField->getEmailField($fieldName);
                        /* Password field */
                    case 'plainPassword':
                    case 'password':
                        return $this->easyAdminField->getTextField($fieldName)
                            ->setFormType(PasswordType::class);

                        /* Field roles */
                    case 'roles':
                        return $this->easyAdminField->getChoiceField($fieldName, [
                            'roleUser' => User::ROLE_USER,
                            'roleAdmin' => User::ROLE_ADMIN,
                            'roleSuperAdmin' => User::ROLE_SUPER_ADMIN,
                        ])->allowMultipleChoices(true)->renderExpanded();
                }
        }

        /* All other crud names (default fields for all other entities) */
        return match ($fieldName) {
            /* Field id */
            'id' => IdField::new($fieldName)
                ->hideOnForm()
                ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName)),

            /* Field configJson */
            'configJson' => CodeEditorField::new($fieldName)
                /* Not called within formulas. */
                ->formatValue(
                    fn ($json) => (new JsonConverter($json))->getBeautified(2)
                )
                ->setLanguage('css')
                ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName)),

            /* DateTime fields. */
            'date' => DateField::new($fieldName)
                ->setFormat('yyyy-MM-dd')
                ->setTemplatePath('admin/crud/field/date_event.html.twig')
                ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName)),

            /* DateTime fields. */
            'updatedAt', 'createdAt' => DateTimeField::new($fieldName)
                ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName)),

            /* Boolean fields. */
            'published', 'yearly' => BooleanField::new($fieldName)
                ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName)),

            /* All other fields. */
            default => $this->easyAdminField->getTextField($fieldName)
                ->setLabel(sprintf('admin.%s.fields.%s.label', $this->getCrudName(), $fieldName))
                ->setHelp(sprintf('admin.%s.fields.%s.help', $this->getCrudName(), $fieldName)),
        };
    }

    /**
     * Returns the constant from fqcn entity (raw).
     *
     * @param string[]|null $default
     * @return string[]
     * @throws Exception
     */
    protected function getConstantRaw(string $name, ?array $default = null): array
    {
        $constantName = sprintf('%s::%s', $this->getEntity(), $name);

        if (!defined($constantName) && $default !== null) {
            return $default;
        }

        if (!defined($constantName)) {
            throw new Exception(sprintf('The constant "%s" does not exist (%s:%d).', $constantName, __FILE__, __LINE__));
        }

        $value = constant($constantName);

        if (!is_array($value)) {
            throw new Exception(sprintf('Unexpected constant returned (%s:%d).', __FILE__, __LINE__));
        }

        return $value;
    }

    /**
     * Adds user filter.
     */
    protected function addUserFilter(QueryBuilder $qb, bool $own = false): QueryBuilder
    {
        /* These roles are allowed to see all entities. */
        if ($this->securityService->isGrantedByAnAdmin()) {
            return $qb;
        }

        /* Filter by user */
        $qb->andWhere($own ? 'entity.id = :user' : 'entity.user = :user');
        $qb->setParameter('user', $this->getUser());

        return $qb;
    }

    /**
     * Check permissions.
     *
     * @throws Exception
     */
    protected function checkPermissions(QueryBuilder $qb, string $entityName): QueryBuilder
    {
        /* These roles are allowed to see all entities. */
        if ($this->securityService->isGrantedByAnAdmin()) {
            return $qb;
        }

        /* Every list will be empty -> If a non-permitted class is called anyway */
        throw new Exception(sprintf('You do not have permission to call the "%s" entity (%s:%d).', $entityName, __FILE__, __LINE__));
    }

    /**
     * Set icon name.
     */
    protected function setIcon(Actions $actions, string $pageName, string $actionName, string $icon): void
    {
        $actions->getAsDto($pageName)->getAction($pageName, $actionName)?->setIcon($icon);
    }
}
