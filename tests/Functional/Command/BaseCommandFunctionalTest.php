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

namespace App\Tests\Functional\Command;

use App\Container\File;
use App\Exception\ClassNotFoundException;
use App\Exception\ClassNotSetException;
use App\Exception\ClassUnexpectedException;
use App\Exception\KeyNotFoundException;
use App\Exception\TypeInvalidException;
use App\Utils\Checker\Checker;
use App\Utils\Command\CommandHelper;
use App\Utils\Constants\CommandData;
use App\Utils\Db\Entity;
use App\Utils\Db\Repository;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2022-11-23)
 * @since 0.1.0 (2022-11-23) First version.
 */
abstract class BaseCommandFunctionalTest extends WebTestCase
{
    final public const MESSAGE_JSON_RESPONSE_INVALID = 'The returned json command value does not match with the given schema.';

    final public const MESSAGE_JSON_RESPONSE_VALID = 'The returned json command value unexpectedly matches the specified scheme.';

    final protected const PATH_SQLITE_DB = 'var/app.db';

    final protected const NAME_KERNEL_PROJECT_DIR = 'kernel.project_dir';

    protected CommandTester $commandTester;

    protected ParameterBagInterface $parameterBag;

    protected Entity $entity;

    protected Repository $repository;

    protected CommandHelper $commandHelper;

    protected bool $useKernel = false;

    protected bool $useCommand = false;

    protected bool $useDb = false;

    protected bool $useParameterBag = false;

    protected bool $loadFixtures = false;

    protected bool $forceLoadFixtures = false;

    protected string $commandName;

    /**
     * @var class-string
     */
    protected string $commandClass;

    protected ?Closure $commandClassParameterClosure = null;

    /**
     * Sets up the test case.
     *
     * @throws ClassNotFoundException
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->doConfig();

        if ($this->useKernel) {
            self::bootKernel();
        }

        if ($this->useParameterBag) {
            $this->createServiceParameterBagInterface();
        }

        if ($this->useDb) {
            $this->createServiceEntity();
            $this->createServiceRepository();
        }

        if ($this->loadFixtures) {
            $this->createServiceCommandHelper();
            $this->loadFixtures();
        }

        if ($this->useCommand) {
            $this->createCommand($this->commandName, $this->commandClass, $this->commandClassParameterClosure);
        }
    }

    /**
     * Tidy up the test case.
     */
    protected function tearDown(): void
    {
        unset($this->commandTester);
    }

    abstract public function doConfig(): void;

    protected function setConfigUseKernel(): self
    {
        $this->useKernel = true;

        return $this;
    }

    /**
     * @param class-string $commandClass
     */
    protected function setConfigUseCommand(string $commandName, string $commandClass, ?Closure $commandClassParameterClosure = null): self
    {
        $this->setConfigUseKernel();
        $this->useCommand = true;
        $this->commandName = $commandName;
        $this->commandClass = $commandClass;
        $this->commandClassParameterClosure = $commandClassParameterClosure;

        return $this;
    }

    protected function setConfigUseDb(): self
    {
        $this->setConfigUseKernel();
        $this->useDb = true;

        return $this;
    }

    protected function setConfigUseParameterBag(): self
    {
        $this->setConfigUseKernel();
        $this->useParameterBag = true;

        return $this;
    }

    protected function setConfigLoadFixtures(): self
    {
        $this->setConfigUseDb();
        $this->loadFixtures = true;

        return $this;
    }

    protected function setConfigForceLoadFixtures(): self
    {
        $this->forceLoadFixtures = true;

        return $this;
    }

    /**
     * @throws ClassNotFoundException
     * @throws Exception
     */
    protected function createServiceParameterBagInterface(): void
    {
        $container = self::getContainer();

        $parameterBag = $container->get(ParameterBagInterface::class);

        if (!$parameterBag instanceof ParameterBagInterface) {
            throw new ClassNotFoundException(ParameterBagInterface::class);
        }

        $this->parameterBag = $parameterBag;
    }

    /**
     * @throws ClassNotFoundException
     * @throws Exception
     */
    protected function createServiceEntity(): void
    {
        $container = self::getContainer();

        $entity = $container->get(Entity::class);

        if (!$entity instanceof Entity) {
            throw new ClassNotFoundException(Entity::class);
        }

        $this->entity = $entity;
    }

    /**
     * @throws ClassNotFoundException
     * @throws Exception
     */
    protected function createServiceRepository(): void
    {
        $container = self::getContainer();

        $repository = $container->get(Repository::class);

        if (!$repository instanceof Repository) {
            throw new ClassNotFoundException(Repository::class);
        }

        $this->repository = $repository;
    }

    /**
     * @throws ClassNotFoundException
     * @throws Exception
     */
    protected function createServiceCommandHelper(): void
    {
        $container = self::getContainer();

        $commandHelper = $container->get(CommandHelper::class);

        if (!$commandHelper instanceof CommandHelper) {
            throw new ClassNotFoundException(CommandHelper::class);
        }

        $this->commandHelper = $commandHelper;
    }

    /**
     * @throws Exception
     */
    protected function loadFixtures(): void
    {
        $pathSqlite = new File(self::PATH_SQLITE_DB);

        /* Skip if db already exists */
        if (!$this->forceLoadFixtures && $pathSqlite->exist() && $pathSqlite->getFileSize() > 0) {
            return;
        }

        /* Empty test table */
        $this->commandHelper->printAndExecuteCommands([
            '/* Drop schema */' => CommandData::COMMAND_SCHEMA_DROP,
            '/* Create schema */' => CommandData::COMMAND_SCHEMA_CREATE,
            '/* Load fixtures */' => CommandData::COMMAND_LOAD_FIXTURES,
        ]);
    }

    /**
     * Creates the command.
     *
     * @param class-string $commandClass
     * @throws TypeInvalidException
     * @throws ReflectionException
     * @throws ClassUnexpectedException
     */
    protected function createCommand(string $commandName, string $commandClass, ?Closure $commandClassParameterClosure): void
    {
        $application = new Application();

        $reflectionClass = new ReflectionClass($commandClass);

        $commandClassParameter = [];

        if ($commandClassParameterClosure !== null) {
            $commandClassParameter = (new Checker($commandClassParameterClosure->call($this)))->checkArraySimple();
        }

        $keyCommand = $reflectionClass->newInstanceArgs($commandClassParameter);

        if (!$keyCommand instanceof Command) {
            throw new ClassUnexpectedException($keyCommand::class, Command::class);
        }

        $application->add($keyCommand);
        $command = $application->find($commandName);

        $this->commandTester = new CommandTester($command);
    }

    /**
     * Returns the project dir.
     *
     * @throws ClassNotSetException
     * @throws KeyNotFoundException
     */
    protected function getProjectDir(): string
    {
        if (!isset($this->parameterBag)) {
            throw new ClassNotSetException('$this->parameterBag', 'Use $this->setConfigUseParameterBag()');
        }

        if (!$this->parameterBag->has(self::NAME_KERNEL_PROJECT_DIR)) {
            throw new KeyNotFoundException(self::NAME_KERNEL_PROJECT_DIR);
        }

        return strval($this->parameterBag->get(self::NAME_KERNEL_PROJECT_DIR));
    }
}
