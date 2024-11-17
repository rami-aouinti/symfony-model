<?php

declare(strict_types=1);

namespace App\Platform\Transport\Twig;

use Symfony\Component\Intl\Locales;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @package App\Twig
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class AppExtension extends AbstractExtension
{
    /**
     * @var list<array{code: string, name: string}>|null
     */
    private ?array $locales = null;

    // The $locales argument is injected thanks to the service container.
    // See https://symfony.com/doc/current/service_container.html#binding-arguments-by-name-or-type
    public function __construct(
        private readonly TranslatorInterface $translator,
        /** @var string[] */
        private readonly array $enabledLocales,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('locales', $this->getLocales(...)),
        ];
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.).
     *
     * @return array<int, array<string, string>>
     */
    public function getLocales(): array
    {
        if ($this->locales !== null) {
            return $this->locales;
        }

        $this->locales = [];

        foreach ($this->enabledLocales as $localeCode) {
            $this->locales[] = [
                'code' => $localeCode,
                'name' => Locales::getName($localeCode, $localeCode),
            ];
        }

        return $this->locales;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('page', $this->showPageNumber(...)),
        ];
    }

    /**
     * @param int $number
     *
     * @return string
     */
    public function showPageNumber(int $number = 1): string
    {
        return ($number > 1) ? ' - '.$this->translator->trans('page').' '.$number : '';
    }
}
