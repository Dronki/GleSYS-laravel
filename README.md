# GleSYS-API wrapper for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dronki/glesys-laravel.svg?style=flat-square)](https://packagist.org/packages/dronki/glesys-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/dronki/glesys-laravel.svg?style=flat-square)](https://packagist.org/packages/dronki/glesys-laravel)

A simple wrapper for the GleSYS API.

## Installation

You can install the package via composer:

```bash
composer require dronki/glesys-laravel
php artisan vendor:publish --provider="Dronki\GlesysLaravel\GlesysLaravelServiceProvider"
```

Register the service-provider in your `config/app.php` file:
```php
'providers' => [
    ...
    Dronki\GlesysLaravel\GlesysLaravelServiceProvider::class,
],
```


## Usage
The wrapper is written in such a way that it can be used in any Laravel application.  
By using the facade, you can access the API in a simple way:
```php
GleSYS::punyEncode( 'www.example.com' );
```

## Available methos
```php
#General
GleSYS::getResponse(); // Gets the response from the last request
GleSYS::punyEncode( $string ); // Puny-encodes a string
GleSYS::punyDecode( $string ); // Puny-decodes a string

# Email
GleSYS::emailOverview(); // Gets a list of all email-accounts and aliases
GleSYS::emailsByDomain( $domain, $filter = '', $objects = false ); // Gets a list of all email-accounts and aliases for a domain, optionally filtered by $filter, and optionally return objects instead of arrays
GleSYS::emailCreateAccount( $email, $password, $settings = [] ); // Creates a new email-account
GleSYS::emailEditAccount( $email, $settings = [] ); // Edits an existing email-account
GleSYS::emailDeleteAccount( $email ); // Deletes an email-account
GleSYS::emailAccountQuota( $email ); // Gets the quota of an email-account

# Aliases for email-account
GleSYS::emailCreateAlias( $alias, $recipient ); // Creates a new alias for an email-account
GleSYS::emailEditAlias( $alias, $recipient ); // Edits an existing alias for an email-account
GleSYS::emailDeleteAlias( $alias ); // Deletes an alias for an email-account

```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email rickard@ahlstedt.xyz instead of using the issue tracker.

## Credits

-   [Rickard Ahlstedt](https://github.com/dronki)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Donations
If you like this package and would like to support my work, consider donating.
    
[![Donate to Dronki](https://img.shields.io/badge/donate-paypal-blue.svg?style=flat-square)](https://www.paypal.me/dronki)
[![Donate with crypto](https://img.shields.io/badge/donate-crypto-yellow)](https://ahlstedt.xyz/donations/)