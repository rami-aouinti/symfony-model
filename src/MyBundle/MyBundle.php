<?php

declare(strict_types=1);

namespace App\MyBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * @package App\MyBundle
 */
class MyBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return __DIR__;
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        // Importez les routes définies dans un fichier YAML ou PHP
        $routes->import(__DIR__ . '/config/routes.yaml');

        // Ou, alternativement :
        // $routes->import(__DIR__.'/config/routes.php');
    }
}
