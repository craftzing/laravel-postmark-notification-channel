Getting started
===

This section will guide you through all necessary and optional installation and configuration steps.

> 💡 Found an issue or is this section missing anything? Feel free to open a 
> [PR](https://github.com/craftzing/:package_name/compare) or 
> [issue](https://github.com/craftzing/:package_name/issues/new).

## ⚒️ Requirements

This package requires:
- [PHP](https://www.php.net/supported-versions.php) 7.4 or 8

Some features may have additional requirements. These will be listed in the according section of the documentation.

## 🧙 Installation

You can install this package using [Composer](https://getcomposer.org) by running the following command:
```bash
composer require craftzing/:package_name
```

We're using [Laravel's package discovery](https://laravel.com/docs/8.x/packages#package-discovery) to automatically
register the service provider, so you don't have to register it yourself.

You can publish the package config file by running the command below, but it's not mandatory:
```bash
php artisan vendor:publish --provider="Craftzing\Laravel\:package_namespace\ServiceProvider" --tag="config"
```

## ⚙️ Configuration

If the package requires any configuration, this is where to document it. If it doesn't, remove this section.

---

[Usage ⏩](usage.md)
