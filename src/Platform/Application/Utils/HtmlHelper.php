<?php

declare(strict_types=1);

namespace App\Platform\Application\Utils;

/**
 * @package App\Platform\Application\Utils
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class HtmlHelper
{
    public static function html2Text(string $html): string
    {
        $text = preg_replace('#<br\s*/?>#i', "\n", $html);

        return strip_tags((string)$text);
    }

    public static function text2Html(string $text): string
    {
        return preg_replace("/\r\n|\r|\n/", '<br>', $text);
    }
}
