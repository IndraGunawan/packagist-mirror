<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
