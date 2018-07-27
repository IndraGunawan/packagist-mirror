<?php declare(strict_types=1);

/*
 * This file is part of IndraGunawan/packagist-mirror.
 * (c) Indra Gunawan <hello@indra.my.id>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$syncEvery   = 'Synchronized every 1 hour';
$countryName = 'Singapore';
$countryCode = 'sg';
$lastSync    = null;

if (file_exists(__DIR__.'/packages.json') && false !== ($lastModified = filemtime(__DIR__.'/packages.json'))) {
    $lastSync = date('r', $lastModified);
}
?>
<!doctype html>
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <title>Packagist Mirror - by Aris Ripandi</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="all, index, follow">
        <meta name="googlebot" content="all, index, follow">
        <meta name="description" content="packagist, php, composer, mirror, aris ripandi">
        <link rel="shortcut icon" href="//packagist.org/favicon.ico?v=1532638634">
        <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Muli:300,400|Fira+Mono">
        <style>
            html { -ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100% }
            body, html { height: 100% }
            body {
                text-align: justify;
                margin: 0;
                padding: 41.88678px;
                box-sizing: border-box;
                font-family: 'Muli', sans-serif;
                font-weight: 300;
                font-size: 12px;
                line-height: 1.5;
                background: #f0efe8;
                color: #474747;
                -webkit-transition: all .3s ease;
                transition: all .3s ease
            }
            body:before {
                position: absolute;
                display: block;
                top: 0;
                left: 0;
                width: 100%;
                height: 9.88875px;
                content: '';
                background: #2f3c4e
            }
            a:link,
            a:visited {
                text-decoration: none;
                background-color: transparent;
                color: #16a085;
                -webkit-transition: color .2s linear;
                transition: color .2s linear
            }
            a:active, a:focus, a:hover { color: #107360 }
            h1 { margin: 0 0 1rem; font-size: 38px; font-weight: 300; line-height: 1.2 }
            h2 {margin-top: 26px}
            strong { font-weight: 400 }
            p { margin: 1rem 0; font-size: 20px }
            .container { display: table; margin: 0 auto; height: 100%; max-width: 752px }
            .content { display: table-cell; vertical-align: middle }
            .email:before {
                content: attr(data-domain) "@" attr(data-local);
                unicode-bidi: bidi-override;
                direction: rtl
            }
            @media (max-width:599px) {
                body { padding: 24px }
                h1 { font-size: 28px }
                p { font-size: 16px }
            }
            @media (max-width:374px) {
                body { padding: 32px 16px }
                strong:before { content: '\a'; white-space: pre }
            }
            ::-moz-selection {
                background: #16a085;
                color: #fff
            }
            ::selection {
                background: #16a085;
                color: #fff
            }
            ul, li { font-size: 12pt; line-height: 20pt;}
            .line {border-style: dashed; border-width: 1px; margin: 30px 0 20px 0;}
            .footer {font-size: 12pt; font-style: italic; text-align: center}
            .text-italic { font-style: italic }
            .footnote {
                text-align: center;
                margin: 30px 0;
                font-size: 13px;
            }
            .float-right {float: right}
            .bash {
                overflow: auto;
                border-radius: 0 .125rem .125rem 0;
                background: #e6e6e6;
                padding: .60rem;
                margin: 0;
                border-radius: 4px;
                border-left: .25rem solid #1565c0;
                font-family: 'Fira Mono', monospace;
                font-style: italic;
                text-align: left;
            }
            .bash > span { font-size: 11pt}
            .img-flag { vertical-align: middle; margin-right: 8px; width: 18px;}
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">

                <h1>Mirrors of the Packagist.org Metadata</h1>
                <h2>This is PHP package repository Packagist.org mirror site.</h2>
                <h3 class="text-italic">
                    <img class="img-flag" src="//cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/flags/4x3/<?=$countryCode;?>.svg" title="<?=$countryName;?>" alt="<?=$countryName;?>"/>
                    <?php echo (null !== $lastSync) ? 'Last sync : ' . $lastSync . ' ('.$syncEvery.')' : $syncEvery; ?>
                </h3>

                <p>
                    Packagist.org tries to provide our own mirrors globally and to scale
                    bandwidth availaibility as required to meet demand from the Composer
                    user base. This mirror unafiliated with Packagist.org, the further
                    you are from the location of the Packagist.org server, the more time
                    is needed to download json files. By using mirror, it will help save
                    the time for downloading because the server location is closer.
                </p>

                <h2>Enable the mirror</h2>
                <div class="bash"> $
                    <span id="enablingStep"></span>
                    <button class="small tertiary ctclipboard float-right" data-clipboard-target="#enablingStep">
                        <img class="clippy" width="13" src="//cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard">
                    </button>
                </div>

                <h2>Disable the mirror</h2>
                <div class="bash">$
                    <span id="disablingStep"></span>
                    <button class="small tertiary ctclipboard float-right" data-clipboard-target="#disablingStep">
                        <img class="clippy" width="13" src="//cdnjs.cloudflare.com/ajax/libs/octicons/4.4.0/svg/clippy.svg" alt="Copy to clipboard">
                    </button>
                </div>

                <h2>Another mirrors</h2>
                <p>
                    This is some other Packagist mirror you can use, please refer to
                    their website to see how to use them:
                </p>
                <ul>
                    <li>Asia, China <a href="//pkg.phpcomposer.com/">pkg.phpcomposer.com</a></li>
                    <li>Asia, Indonesia <a href="//packagist.phpindonesia.id/">packagist.phpindonesia.id</a></li>
                    <li>Asia, Japan <a href="//packagist.jp/">packagist.jp</a></li>
                    <li>South America, Brazil <a href="//packagist.com.br/">packagist.com.br</a></li>
                </ul>

                <h2>Disclaimer</h2>
                <p>
                    This site offers its services free of charge and only as a mirror site.
                    This site only provides package information / metadata with no distribution
                    file of the packages. All packages metadata files are mirrored from
                    <a href="//packagist.org">Packagist.org</a>. We do not modify and/or
                    process the JSON files. If there is something wrong, please disable
                    the setting the disable command above and try to refer to the original
                    Packagist.org.
                </p>

                <div class="line"></div>
                <div class="footer">
                    Licensed under <a href="//github.com/riipandi/packagist-mirror/blob/master/LICENSE">MIT</a>
                    and the code available at <a href="//github.com/riipandi/packagist-mirror">Github</a>. <br/>
                    If you want to get in touch, mailto: <span class="email" data-local="idnapir" data-domain="em.mp"></span>.
                </div>
            </div>
        </div>

        <div class="footnote">
            <a href="//ripandi.id/go/vultr" target="_blank">Hosted at Cloud Virtual Server</a>
        </div>

        <script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>
        <script>
            // anti spam email
            var span = document.querySelector('.email');
            var email = [span.dataset.local.split('').reverse().join(''), span.dataset.domain.split('').reverse().join('')].join('@');
            var link = document.createElement('a');
            var text = document.createTextNode(email);
            link.setAttribute('href', 'mailto:' + email);
            link.appendChild(text);
            span.parentNode.replaceChild(link, span);
            // set text of the command
            document.getElementById('enablingStep').innerText = 'composer config -g repos.packagist composer '+ window.location.origin;
            document.getElementById('disablingStep').innerText = 'composer config -g --unset repos.packagist';
            new ClipboardJS('.ctclipboard');
        </script>
    </body>
</html>
