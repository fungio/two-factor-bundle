Getting Started With TwoFAS/TwoFactorBundle
===========================================

In this guide you will learn how install and configure the bundle to be used in your Symfony application.

Prerequisites
-------------

Before installation make sure that your application satisfies the requirements in README.md, the *Symfony Security Component* is installed,
and you have a Login form (more information in [Login Form Setup](http://symfony.com/doc/current/security/form_login_setup.html))

Installation
------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require twofas/two-factor-bundle
```

This command requires you to have Composer installed, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.


### Step 2: Enable the bundle

Enable the bundle in the kernel

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new TwoFAS\TwoFactorBundle\TwoFASTwoFactorBundle(),
        // ...
    );
}
```

> **Note**
>
> We use **FOSJsRoutingBundle** to serve routes in JS files, so you may enable this bundle too (more information in the [FOSJsRoutingBundle documentation](https://github.com/FriendsOfSymfony/FOSJsRoutingBundle)).
> If you have one, you have to only dump new routes (at the end of installation process).
> If you don't want to use this bundle you have to write static routes in JS files (more info in [Overriding templates](templates.md))

### Step 3: Register the Routes

Load the routing definition of the bundle in the application (usually in the
`app/config/routing.yml` file):

```yaml
# app/config/routing.yml
two_fas_two_factor:
    resource: "@TwoFASTwoFactorBundle/Resources/config/routing.xml"
    prefix:   /2fas
```

### Step 4: Configure your application

In order to use this bundle you have to write some configuration.

Below is a minimal example of the configuration necessary to use the TwoFAS/TwoFactorBundle
in your application:

```yaml
# app/config/config.yml
two_fas_two_factor:
    account_name: ~
    db_driver: orm
    encryption_key: ~
    firewalls: ["Your firewall name you are using in security.yml"]

# app/config/security.yml
access_control:
    - { path: ^/2fas, role: IS_AUTHENTICATED_REMEMBERED }
```
### Step 5: Install assets

Execute the following command to publish the assets required by the bundle:

```bash
# Symfony 2.8
$ php app/console assets:install

# Symfony 3.x
$ php bin/console assets:install
```

### Step 6: Expose the Routes

If you are using **FOSJsRoutingBundle** with precompiled *fos_js_routes.js* file you have to run:

```bash
# Symfony 2.8
$ php app/console fos:js-routing:dump

# Symfony 3.x
$ php bin/console fos:js-routing:dump
```

### Step 7: Update your database schema

Now that the bundle is configured, you need to update your database schema:

```bash
# Symfony 2.8
$ php app/console doctrine:schema:update --force

# Symfony 3.x
$ php bin/console doctrine:schema:update --force
```

### Next Steps

We also recommend that you clear your Symfony cache after installation.
 
Now that you have completed the basic installation and configuration of the TwoFAS/TwoFactorBundle, 
you need to create an account in 2FAS and configure it with your authentication mobile app.

[**Index**](index.md) | [**Configuration >>**](configuration.md)