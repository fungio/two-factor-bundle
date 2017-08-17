TwoFAS/TwoFactorBundle
=====================

![Badge Symfony Version](https://img.shields.io/badge/Symfony-2.8%20and%203.x-green.svg)

TwoFAS/TwoFactorBundle adds support for two-factor authentication and increases security on your website by adding the second step in the login process.
This Bundle uses an external API to serve the authentication process, so You have to create an account to use it.
This can be done quickly and easily in one of our `console` commands.
 
Currently it supports only **TOTP** (Time-Based One-Time Password Algorithm) authentication method, but in the future there will be next authentication methods introduced: (text/voice/e-mail messages) and more new features like log in through sockets,
offline codes etc.
 
To use the TOTP authentication method, users must have a mobile application that can generate TOTP tokens from the secret key
(usually placed in QR Code).

You can use for example:

- 2FAS Auth
- Google Authenticator
- Microsoft Authenticator
- Authy
- FreeOTP
- and many others…

**Requirements:** 

- PHP >= 5.5
- Symfony 2.8 | 3.x
- JQuery (or another JS framework or plain javascript, but you have to make some changes in the template - more info in documentation)
- Doctrine ORM (Doctrine ODM, CouchDB and Propel is not supported for now)
- Supports only for "Form" login method (your own login form or [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle))

Documentation
-------------
The documentation can be found in the [Resources/doc](Resources/doc/index.md) directory.

About
-----
For more information check out our website at [https://2fas.com](https://2fas.com)

Licence
-------------
This bundle is available under the [MIT license](LICENSE).