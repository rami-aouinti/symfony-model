<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: Valery Maslov
 * Date: 30.08.2018
 * Time: 16:33.
 */

namespace App\Platform\Application\Utils;

use Symfony\Component\String\Slugger\AsciiSlugger;
use voku\helper\ASCII;

use function function_exists;

/**
 * @package App\Platform\Application\Utils
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class Slugger implements SluggerInterface
{
    public static function slugify(string $string): string
    {
        if (!function_exists('transliterator_transliterate')) {
            $string = self::ascii($string);
        }

        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($string)->lower();

        return (string)$slug;
    }

    private static function ascii($value): string
    {
        return ASCII::to_ascii((string)$value, 'en');
    }
}
