<?php

declare(strict_types=1);

namespace App\Twig;

use Closure;
use LogicException;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionObject;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\ErrorHandler\ErrorRenderer\FileLinkFormatter;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TemplateWrapper;
use Twig\TwigFunction;

use function array_slice;
use function count;
use function in_array;
use function is_array;
use function is_object;
use function sprintf;
use function Symfony\Component\String\u;

use const ENT_COMPAT;
use const ENT_SUBSTITUTE;

/**
 * @package App\Twig
 * @author Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
final class SourceCodeExtension extends AbstractExtension
{
    /**
     * @var callable|null
     */
    private $controller;

    public function __construct(
        private readonly FileLinkFormatter $fileLinkFormat,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
        $this->projectDir = str_replace('\\', '/', $projectDir) . '/';
    }

    public function setController(?callable $controller): void
    {
        $this->controller = $controller;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'link_source_file',
                $this->linkSourceFile(...),
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
            new TwigFunction(
                'show_source_code',
                $this->showSourceCode(...),
                [
                    'is_safe' => ['html'],
                    'needs_environment' => true,
                ]
            ),
        ];
    }

    /**
     * Render a link to a source file.
     */
    public function linkSourceFile(Environment $twig, string $file, int $line): string
    {
        $text = str_replace('\\', '/', $file);

        if (str_starts_with($text, $this->projectDir)) {
            $text = mb_substr($text, mb_strlen($this->projectDir));
        }

        $link = $this->fileLinkFormat->format($file, $line);
        if ($link === false) {
            return '';
        }

        return sprintf(
            '<a href="%s" title="Click to open this file" class="file_link">%s</a> at line %d',
            htmlspecialchars($link, ENT_COMPAT | ENT_SUBSTITUTE, $twig->getCharset()),
            htmlspecialchars($text, ENT_COMPAT | ENT_SUBSTITUTE, $twig->getCharset()),
            $line,
        );
    }

    /**
     * @throws LoaderError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function showSourceCode(Environment $twig, string|TemplateWrapper $template): string
    {
        return $twig->render('debug/source_code.html.twig', [
            'controller' => $this->getController(),
            'template' => $this->getTemplateSource($twig->resolveTemplate($template)),
        ]);
    }

    /**
     * @throws ReflectionException
     * @return array{file_path: string, starting_line: int|false, source_code: string}|null
     */
    private function getController(): ?array
    {
        // this happens for example for exceptions (404 errors, etc.)
        if ($this->controller === null) {
            return null;
        }

        $method = $this->getCallableReflector($this->controller);

        /** @var string $fileName */
        $fileName = $method->getFileName();

        $classCode = file($fileName);
        if ($classCode === false) {
            throw new LogicException(
                sprintf('There was an error while trying to read the contents of the "%s" file.', $fileName)
            );
        }

        $startLine = $method->getStartLine() - 1;
        $endLine = $method->getEndLine();

        while ($startLine > 0) {
            $line = trim($classCode[$startLine - 1]);

            if (in_array($line, ['{', '}', ''], true)) {
                break;
            }

            $startLine--;
        }

        $controllerCode = implode('', array_slice($classCode, $startLine, $endLine - $startLine));

        return [
            'file_path' => $fileName,
            'starting_line' => $method->getStartLine(),
            'source_code' => $this->unindentCode($controllerCode),
        ];
    }

    /**
     * Gets a reflector for a callable.
     *
     * This logic is copied from Symfony\Component\HttpKernel\Controller\ControllerResolver::getArguments
     *
     * @throws ReflectionException
     */
    private function getCallableReflector(callable $callable): ReflectionFunctionAbstract
    {
        if (is_array($callable)) {
            return new ReflectionMethod($callable[0], $callable[1]);
        }

        if (is_object($callable) && !$callable instanceof Closure) {
            $r = new ReflectionObject((object)$callable);

            return $r->getMethod('__invoke');
        }

        return new ReflectionFunction($callable);
    }

    /**
     * @return array{file_path: string|false, starting_line: int, source_code: string}
     */
    private function getTemplateSource(TemplateWrapper $template): array
    {
        $templateSource = $template->getSourceContext();

        return [
            'file_path' => $templateSource->getPath(),
            'starting_line' => 1,
            'source_code' => $templateSource->getCode(),
        ];
    }

    /**
     * Utility method that "unindents" the given $code when all its lines start
     * with a tabulation of four white spaces.
     */
    private function unindentCode(string $code): string
    {
        $codeLines = u($code)->split("\n");

        $indentedOrBlankLines = array_filter($codeLines, static function ($lineOfCode) {
            return u((string)$lineOfCode)->isEmpty() || u((string)$lineOfCode)->startsWith('    ');
        });

        $codeIsIndented = count($indentedOrBlankLines) === count($codeLines);

        if ($codeIsIndented) {
            $unindentedLines = array_map(static function ($lineOfCode) {
                return u((string)$lineOfCode)->after('    ');
            }, $codeLines);
            $code = u("\n")->join($unindentedLines)->toString();
        }

        return $code;
    }
}
