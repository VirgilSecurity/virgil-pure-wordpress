# Virgil WordPress Plugin PHE PHP

[Introduction](#introduction) | [Features](#features) | [Register Your Account](#register-your-account) | [Installation](#installation) | [Prepare Your Database](#prepare-your-database) | [Usage Examples](#usage-examples) | [Docs](#docs) | [Support](#support)

## Introduction
<img src="https://cdn.virgilsecurity.com/assets/images/github/logos/pure_grey_logo.png" align="left" hspace="0" vspace="0"></a>[Virgil Security](https://virgilsecurity.com) introduces an implementation of the [Password-Hardened Encryption (PHE) protocol](https://virgilsecurity.com/wp-content/uploads/2018/11/PHE-Whitepaper-2018.pdf) – a powerful and revolutionary cryptographic technology that provides stronger and more modern security, that secures users' data and lessens the security risks associated with weak passwords.

Virgil WordPress PHE plugin allows developers to interact with Virgil PHE Service to protect users' passwords in a WordPress database from offline/online attacks and makes stolen passwords/data useless if your database has been compromised. Neither Virgil nor attackers know anything about users' passwords/data.

This technology can be used within any database or login system that uses a password, so it’s accessible for a company of any industry or size.

**Authors of the PHE protocol**: Russell W. F. Lai, Christoph Egger, Manuel Reinert, Sherman S. M. Chow, Matteo Maffei and Dominique Schroder.

## Features
- Zero knowledge of users' passwords
- Passwords & data protection from online attacks
- Passwords & data protection from offline attacks
- Instant invalidation of stolen database
- User data encryption with a personal key


## Installation

### Install WordPress plugin

- [Download the WordPress Virgil PHE plugin](https://github.com/VirgilSecurity/virgil-phe-wordpress-plugin-php/archive/develop.zip) .zip
- Navigate to WordPress Dashboard
- Open "Plugins" tab
- Updload the .zip WordPress Virgil PHE plugin file

### Install PureKit Package

The PureKit is provided as a package named `virgil/purekit`. The package is distributed via Composer. The package is available for PHP version 7.2.

#### Add the vsce_phe_php extension before using the plugin

* [Download virgil-crypto-c-{latest version} archive from the CDN](https://cdn.virgilsecurity.com/virgil-crypto-c/php/) according to your server operating system
* Place the *vsce_phe_php.{so/dll}* file from the archive (/lib folder) into the directory with extensions
* Add the *extension=vsce_phe_php* string in to the php.ini file
* Restart your web-service (apache or nginx): *sudo service {apache2 / nginx} restart*

##### Tips:

* PHP version: *phpversion() / php --version*
* OS Version: *PHP_OS*
* php.ini and extensions directory: *phpinfo() / php -i / php-config --extension_dir*

Also, you can launch the *extension/helper.php* file to get information about a version and extensions.

Now, install PureKit library with the following code:
```bash
composer require virgil/purekit
```

## Register Your Account
In order to use the plugin, you'll need to:
- create an account at [Virgil Dashboard](https://dashboard.virgilsecurity.com/),
- create PURE application
- get your PURE application's credentials such as: `APP_TOKEN`, `APP_SECRET_KEY`, `SERVICE_PUBLIC_KEY`