# Virgil Pure WordPress Plugin PHP

[Introduction](#introduction) | [Features](#features) | [Installation](#installation) | [How To Use Plugin](#how-to-use-plugin) | [F.A.Q](#faq) | [License](#license) | [Support](#support)

## Introduction
<img src="https://cdn.virgilsecurity.com/assets/images/github/logos/pure_plugin.png" align="left" hspace="0" vspace="0"></a>
Virgil Pure Wordpress Plugin is a free tool that protects user passwords from data breaches and both online and offline attacks, and renders stolen passwords useless even if your database has been compromised. 

The Pure based on a powerful and revolutionary cryptographic technology that provides stronger and more modern security and can be used within any database or login system that uses a password, so it's accessible for business of any industry or size.

Learn more about the Pure technology [here](https://virgilsecurity.com/announcing-purekit/).


## Features

### Available
- Zero knowledge of users' passwords
- Passwords protection from online and offline attacks
- Instant invalidation of stolen database

### Coming soon
- User data encryption with a personal key
- Plugin deactivation and restoration of the previous authorization system

## Installation

The plugin is currently unavailable at the WP Store but you can get it by downloading it from the official repository.

### Install Virgil Pure WordPress Plugin using GitHub

The package is available for PHP version 7.2.

#### Add the vsce_phe_php extension before using the plugin

* [Download virgil-crypto-c-{latest version} archive from the CDN](https://cdn.virgilsecurity.com/virgil-crypto-c/php/) according to your server operating system
* Place the *vsce_phe_php.{so/dll}* file from the archive (/lib folder) into the directory with extensions
* Add the *extension=vsce_phe_php* string in to the php.ini file
* Restart your web-service (apache or nginx): *sudo service {apache2/nginx} restart*

##### Tips:

* PHP version: *php --version*
* php.ini: *php --ini | grep "Loaded Configuration File"*
* Extension dir: *php -i | grep extension_dir*

#### Add plugin

- [Download the WordPress Virgil_Pure plugin](https://github.com/VirgilSecurity/virgil-pure-wordpress/releases)
- Navigate to WordPress Dashboard
- Open "Plugins → Add New" tab
- Upload the Virgil_Pure.zip file
- Activate the plugin

## How To Use Plugin

### Setup Credentials
To start working with the plugin, at the plugin tab at your WordPress dashboard you'll need to place some credentials in corresponding fields. In order to do that, go through the following steps:
- create an account at [Virgil Dashboard](https://dashboard.virgilsecurity.com/)
- create Pure application
- copy your Pure application's credentials such as: `APP_TOKEN`, `APP_SECRET_KEY`, `SERVICE_PUBLIC_KEY`
- paste them into the corresponding fields

### Migration

Migration is a phase during which the plugin requests cryptographic data from Virgil server to associate users' 
passwords (user_pass) with cryptographic enrollments provided by the server. Then enrollment records are created and 
stored in your database (wp_usermeta) instead of users' passwords.

> Warning! The plugin removes the default passwords hashes (it will be blank).

Simply click the "Start migration" button to start migration.

### Records Update (optional)

This function allows you to use a special `UPDATE_TOKEN` to update all of the enrollment records in your database. This action doesn't requite changing users' passwords or modifying the scheme of the existing table.

Navigate to your Pure application panel at [Virgil Dashboard](https://dashboard.virgilsecurity.com/), press "BEGIN 
ROTATION PROCESS", then “SHOW UPDATE TOKEN” button to get the `UPDATE_TOKEN`. Insert the `UPDATE_TOKEN` into the field at the Virgil Pure plugin tab. 

## F.A.Q.

#### What is Demo mode?

Demo mode is a mode in which no data in your database will be altered. To demonstrate how Virgil Pure works, a new column will be created to hold the newly protected password data. When you're ready to go live, your password hashes will be translated into cryptographically protected data.

It is required to migrate all users before switching demo mod off.

#### Do users have to change their passwords if the database has been compromised? 
If a database has been stolen, users do not need to change their original passwords. However, you need to rotate all user records in your database. This will use cryptography to disconnect the compromised Pure records from the original passwords, leaving any unauthorized party empty handed.

#### How much does it cost? 
Pure is a FREE toolkit. All libraries are open source and can be found on GitHub, where they are available for free to any user.

#### What if an App Private Key gets lost?
There is no way to restore the `APP_SECRET_KEY`. The database becomes inaccessible and therefore useless. So, it makes sense to immediately make a backup of the key in any convenient form.

## License
See [LICENSE](https://github.com/VirgilSecurity/virgil-cli/tree/master/LICENSE) for details.

## Support
Our developer support team is here to help you. Find out more information on our [Help Center](https://help.virgilsecurity.com/).

You can find us on [Twitter](https://twitter.com/VirgilSecurity) or send us email support@VirgilSecurity.com.

Also, get extra help from our support team on [Slack](https://virgilsecurity.com/join-community).