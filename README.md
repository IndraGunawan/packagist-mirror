# Packagist Mirror

Creates your own packagist.org mirror site.

## Requirements
- PHP ^7.1.3

## Installation
1. Clone the repository
2. Install dependencies: `php composer.phar install`
3. Make a VirtualHost with DocumentRoot pointing to `public/`
4. If you are using like `deployer` to deploy the project, make sure that you add `build` directory to the shared directory config of your deploy script and run `bin/console app:metadata:symlink` after the deployment is successful.
5. Change the value of the `$countryName` and `$countryCode` variable at `public/index.php` to make it easier for users to identifyyour mirror site location.

You should now be able to access the site.

## Day-to-Day Operation
There is only one command you should run periodically (ideally set up a cron job running every minute):
```
bin/console app:metadata:dump
```

## License

This project is under the MIT license. See the complete [license](LICENSE)