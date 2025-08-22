# Languini

![Languini](src/public/image.png)

## pakages used
- https://github.com/openai-php/laravel

## Installation

```shell
composer require libaro/languini --dev
```

You can publish the configuration file using

```shell
php artisan vendor:publish --tag="languini"
```

### AI part

Publish OpenAI config:

```shell
php artisan vendor:publish --provider="OpenAI\Laravel\ServiceProvider"
```

Set .env variables:

```
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=org-...
```

## Usage

To use this package, simply go to your browser and navigate to the `/languini` url. Fill in the missing translations and save!

## Contributing

Package is open for pull requests!

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Robin Rosiers](https://github.com/RosiersRobin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.