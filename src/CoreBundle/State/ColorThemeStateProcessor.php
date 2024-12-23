<?php

/* For licensing terms, see /license.txt */

declare(strict_types=1);

namespace App\CoreBundle\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Access\Domain\Entity\AccessUrlRelColorTheme;
use App\CoreBundle\ServiceHelper\AccessUrlHelper;
use App\Platform\Domain\Entity\ColorTheme;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

final class ColorThemeStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly AccessUrlHelper $accessUrlHelper,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'oneup_flysystem.themes_filesystem')]
        private readonly FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @param mixed $data
     *
     * @throws FilesystemException
     */
    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        \assert($data instanceof ColorTheme);

        /** @var ColorTheme $colorTheme */
        $colorTheme = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        if ($colorTheme) {
            $accessUrl = $this->accessUrlHelper->getCurrent();

            $accessUrlRelColorTheme = $accessUrl->getColorThemeByTheme($colorTheme);

            if (!$accessUrlRelColorTheme) {
                $accessUrlRelColorTheme = (new AccessUrlRelColorTheme())->setColorTheme($colorTheme);

                $this->accessUrlHelper->getCurrent()->addColorTheme($accessUrlRelColorTheme);
            }

            $this->entityManager->flush();

            $contentParts = [];
            $contentParts[] = ':root {';

            foreach ($colorTheme->getVariables() as $variable => $value) {
                $contentParts[] = "  {$variable}: {$value};";
            }

            $contentParts[] = '}';

            $this->filesystem->createDirectory($colorTheme->getSlug());
            $this->filesystem->write(
                $colorTheme->getSlug() . DIRECTORY_SEPARATOR . 'colors.css',
                implode(PHP_EOL, $contentParts)
            );
        }

        return $colorTheme;
    }
}
