=== Plugin Name ===
Contributors: virgilsecuritycom
Donate link:
Tags: password, crypto, security
Requires at least: 5.0.0
Tested up to: 5.1.1
Stable tag:
License: GPLv2 or later
License URI: https://github.com/VirgilSecurity/virgil-cli/blob/master/LICENSE

Free tool that protects user passwords from data breaches and both online and offline attacks, and renders stolen passwords useless even if your database has been compromised.

== Description ==

Virgil Pure Wordpress Plugin is a free tool that protects user passwords from data breaches and both online and offline attacks, and renders stolen passwords useless even if your database has been compromised.

The Pure based on a powerful and revolutionary cryptographic technology that provides stronger and more modern security and can be used within any database or login system that uses a password, so it's accessible for business of any industry or size.

Learn more about the Pure technology here: https://virgilsecurity.com/announcing-purekit/

== Installation ==

The package is available for PHP version 7.2.

= Add the vsce_phe_php extension before using the plugin =
* [Download `virgil-crypto-c-{latest version}`](https://cdn.virgilsecurity.com/virgil-crypto-c/php/) archive from the CDN according to your server operating system
* Place the vsce_phe_php.{so/dll} file from the archive (/lib folder) into the directory with extensions
* Add the `extension=vsce_phe_php` string in to the php.ini file
* Restart your web-service (apache or nginx): `sudo service {apache2/nginx} restart`

Tips:
* PHP version: `php --version`
* php.ini: `php --ini | grep "Loaded Configuration File"`
* Extension dir: `php -i | grep extension_dir`

= Add plugin =
* [Download the WordPress Virgil_Pure plugin](https://github.com/VirgilSecurity/virgil-pure-wordpress/releases)
* Navigate to WordPress Dashboard
* Open "Plugins → Add New" tab
* Upload the Virgil_Pure.zip file

== Frequently Asked Questions ==

= What is Demo mode? =

Demo mode is a mode in which no data in your database will be altered. To demonstrate how Virgil Pure works, a new column will be created to hold the newly protected password data. When you're ready to go live, your password hashes will be translated into cryptographically protected data.

It is required to migrate all users before switching demo mod off.

= Do users have to change their passwords if the database has been compromised? =

If a database has been stolen, users do not need to change their original passwords. However, you need to rotate all user records in your database. This will use cryptography to disconnect the compromised Pure records from the original passwords, leaving any unauthorized party empty handed.

= How much does it cost? =

Pure is a FREE toolkit. All libraries are open source and can be found on GitHub, where they are available for free to any user.

= What if an App Private Key gets lost? =

There is no way to restore the APP_SECRET_KEY. The database becomes inaccessible and therefore useless. So, it makes sense to immediately make a backup of the key in any convenient form.

== Screenshots ==

== Changelog ==

= 1.0.0 =
* Init plugin

== Upgrade Notice ==
