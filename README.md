# Virgil WordPress Plugin Pure PHP

[Introduction](#introduction) | [Features](#features) | [Installation](#installation) | [How To Use Plugin](#how-to-use-plugin) | [License](#license) | [Support](#support)

## Introduction
<img src="https://cdn.virgilsecurity.com/assets/images/github/logos/pure_grey_logo.png" align="left" hspace="0" vspace="0"></a>[Virgil Security](https://virgilsecurity.com) introduces an implementation of the [Password-Hardened Encryption (PHE) protocol](https://virgilsecurity.com/wp-content/uploads/2018/11/PHE-Whitepaper-2018.pdf) – a powerful and revolutionary cryptographic technology that provides stronger and more modern security, that secures users' data and lessens the security risks associated with weak passwords.

Virgil WordPress Pure plugin allows developers to interact with Virgil PHE Service to protect users' passwords in a WordPress database from offline/online attacks and makes stolen passwords/data useless if your database has been compromised. Neither Virgil nor attackers know anything about users' passwords/data.

This technology can be used within any database or login system that uses a password, so it’s accessible for a company of any industry or size.

**Authors of the PHE protocol**: Russell W. F. Lai, Christoph Egger, Manuel Reinert, Sherman S. M. Chow, Matteo Maffei and Dominique Schroder.

## Features
- Zero knowledge of users' passwords
- Passwords & data protection from online attacks
- Passwords & data protection from offline attacks
- Instant invalidation of stolen database
- User data encryption with a personal key


## Installation

### Install WordPress Pure Plugin

- [Download the WordPress Virgil Pure plugin](https://github.com/VirgilSecurity/virgil-pure-wordpress/archive/develop.zip) repository .zip
- Navigate to WordPress Dashboard
- Open "Plugins" tab
- Upload the .zip WordPress Virgil Pure plugin file

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


## How To Use Plugin

### Set the credentials
To start working with the plugin, at the plugin tab at your WordPress dashboard you'll need to place some credentials in corresponding fields. In order to do that, go through the following steps:
- create an account at [Virgil Dashboard](https://dashboard.virgilsecurity.com/),
- create Pure application
- copy your Pure application's credentials such as: `APP_TOKEN`, `APP_SECRET_KEY`, `SERVICE_PUBLIC_KEY`
- paste them into the corresponding fields

### Migration

Migration is a phase during which the plugin requests cryptographic data from Virgil server to associate users' passwords or their hash (or whatever you use) with cryptographic enrollments provided by the server. Then enrollment records are created and stored in your database instead of users' passwords.

> Warning! The plugin replaces (and therefore removes) the default passwords hashes.

Simply click the "Start migration" button to start migration.

### Records update (optional)

This function allows you to use a special `update_token` to update all of the enrollment records in your database. This action doesn't requite changing users' passwords or modifying the scheme of the existing table.

Navigate to your Pure application at [Virgil Dashboard](https://dashboard.virgilsecurity.com/), get your update token and insert it into the field at the Virgil Pure plugin tab. 

## License
See [LICENSE](https://github.com/VirgilSecurity/virgil-cli/tree/master/LICENSE) for details.

## Support
Our developer support team is here to help you. Find out more information on our [Help Center](https://help.virgilsecurity.com/).

You can find us on [Twitter](https://twitter.com/VirgilSecurity) or send us email support@VirgilSecurity.com.

Also, get extra help from our support team on [Slack](https://virgilsecurity.com/join-community).