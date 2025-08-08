# Languini

## pakages used
- https://github.com/openai-php/laravel

## Installation

```shell
composer require libaro-io/languini --dev
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