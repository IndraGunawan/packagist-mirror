<?php

declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// used for title and alt for flag
$countryName = 'Indonesia';
// used for flag image
$countryCode = 'id';
$lastSync = null;
if (file_exists(__DIR__.'/packages.json') && false !== ($lastModified = filemtime(__DIR__.'/packages.json'))) {
    $lastSync = date('r', $lastModified);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" />
        <title>Packagist Mirror</title>

        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,300italic,700,700italic" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mini.css/2.3.7/mini-default.min.css" />
        <style>
            body { font-family: 'Roboto', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif; }
            .title { text-align: center}

            @media screen and (min-width: 768px) {
                h1 { font-size: 500% }
                h1 > img { width: 10%; }
            }
            @media screen and (max-width: 768px) {
                h1 { font-size: 300% }
                h1 > img { width: 61px; }
            }
            .bash {
                overflow: auto;
                border-radius: 0 .125rem .125rem 0;
                background: #e6e6e6;
                padding: .75rem;
                margin: .5rem;
                border-left: .25rem solid #1565c0;
                font-family: monospace, monospace;
            }
            .bash > span { font-family: monospace, monospace; }
            .img-valign { vertical-align: middle; }
            mark.default { background: rgba(220,220,220,0.75); color: #212121; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-10 col-lg-offset-1">
                    <div class="title">
                        <h1>Packagist Mirror <img class="img-valign" src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/flags/4x3/<?= $countryCode; ?>.svg" title="<?= $countryName; ?>" alt="<?= $countryName; ?>"/></h1>
                        <?php if (null !== $lastSync): ?>
                            <p>Last sync: <?= $lastSync; ?> (Synchronized every 1 minute)</p>
                        <?php else: ?>
                            <p>Synchronized every 1 minute</p>
                        <?php endif; ?>
                    </div>
                    <p>
                        This is PHP package repository Packagist.org mirror site.
                    </p>
                    <p>
                        If you're using PHP Composer, commands like <mark class="default">create-project</mark>, <mark class="default">require</mark>, <mark class="default">update</mark>, <mark class="default">remove</mark> are often used.
                        When those commands are executed, Composer will download information from the packages that are needed also from dependent packages. The number of json files downloaded depends on the complexity of the packages which are going to be used.
                        The further you are from the location of the packagist.org server, the more time is needed to download json files. By using mirror, it will help save the time for downloading because the server location is closer.
                    </p>
                    <p>
                        Please do the following command to change the PHP Composer config to use this site as default Composer repository.
                    </p>
                    <div class="tabs stacked">
                        <input type="radio" name="accordion" id="enable" checked aria-hidden="true">
                        <label for="enable" aria-hidden="true">Enable</label>
                        <div>
                            <p class="bash" >
                                $ <span id="enablingStep"></span>
                                <button class="small tertiary ctclipboard" data-clipboard-target="#enablingStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard"> Copy</button>
                            </p>
                        </div>
                        <input type="radio" name="accordion" id="disable"aria-hidden="true">
                        <label for="disable" aria-hidden="true">Disable</label>
                        <div>
                            <p class="bash" >
                                $ <span id="disablingStep"></span>
                                <button class="small tertiary ctclipboard" data-clipboard-target="#disablingStep"><img class="clippy" width="13" src="https://cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard"> Copy</button>
                            </p>
                        </div>
                    </div>

                    <h2>Disclaimer</h2>
                    <p>This site offers its services free of charge and only as a mirror site.</p>
                    <p>This site only provides package information / metadata with no distribution file of the packages. All packages metadata files are mirrored from <a href="https://packagist.org">Packagist.org</a>. We do not modify and/or process the JSON files. If there is something wrong, please disable the setting the Disable command above and try to refer to the original packagist.org.</p>
                </div>
            </div>
        </div>
        <footer class="row">
            <div class="col-sm-12 col-md-12 col-lg-10 col-lg-offset-1">
                <p><b>Packagist Mirror</b> was built from Indonesia by <a href="https://github.com/IndraGunawan">IndraGunawan</a>.</p>
                <p>It is licensed under the <a href="https://github.com/IndraGunawan/packagist-mirror/blob/master/LICENSE">MIT License</a>. You can view the project's source code on <a href="https://github.com/IndraGunawan/packagist-mirror">GitHub</a>.</p>
            </div>
        </footer>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>
        <script>
            // set text of the command
            document.getElementById('enablingStep').innerText = 'composer config -g repos.packagist composer '+ window.location.origin;
            document.getElementById('disablingStep').innerText = 'composer config -g --unset repos.packagist';

            new ClipboardJS('.ctclipboard');
        </script>
    </body>
</html>
