<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Definition;

$definition = new Definition();
$definition
    ->setAutowired(true)
    ->setAutoconfigured(true)
    ->setPublic(false)
    ->setBindings([
        '$buildDir' => $container->getParameter('app.project_dir').'/build',
        '$publicDir' => $container->getParameter('app.project_dir').'/public',
    ])
;
$this->registerClasses($definition, 'App\\', '../src/*');

$container->registerForAutoconfiguration(Command::class)->addTag('console.command');
