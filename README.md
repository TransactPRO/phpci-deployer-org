###PHPCI Plugin for deployer.org integration

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cf0654ee-b09e-4010-8cc0-a4f5fcdd6167/big.png)](https://insight.sensiolabs.com/projects/cf0654ee-b09e-4010-8cc0-a4f5fcdd6167)

[![Latest Version](https://img.shields.io/packagist/v/transactpro/phpci-deployer-org.svg?style=flat-square)](https://github.com/transactpro/phpci-deployer-org/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/transactpro/phpci-deployer-org.svg?style=flat-square)](https://packagist.org/packages/transactpro/phpci-deployer-org)

## Prerequisites
- Up and running [`deployer.org`](http://deployer.org/) tool *`dep`* and endpoint configuration done like in docs. (You can get it [here](http://deployer.org/docs/installation))

## Install
First of all - `composer require transactpro/phpci-deployer-org`

To avoid any security problems, this plugin stores **three** different [recipe parts](https://github.com/TransactPRO/phpci-deployer-org/tree/master/examples):

1. `common.deploy.php` - some predefinitions. In other words, just extending default `recipe/common.php`.
2. `project.deploy.php` - main deploy configuration. You can store here even server credentials (pure password or ssh-key password).
3. `deploy.php` - rest part of deploy configuration. Mainly used for defining writable/shared directories.

## Usage
Let's store recipe parts:
- `common.deploy.php` => `/var/www/deploy/recipes/common.deploy.php`
- `project.deploy.php` => `/var/www/deploy/recipes/project.deploy.php`
- `deploy.php` => `/your/project/root/deploy.php`

========================

`phpci.yml` example:
```yml
build_settings:
    verbose: true

success:
    deployer:
        master:
            stage: prod
            file: /var/www/deploy/recipes/project.deploy.php
```

That's all folks!

=========================================
## TODO
I'm planning to add wildcard for branches, so we can deploy master to production, bugfixes to staging and features to test/staging environments.
