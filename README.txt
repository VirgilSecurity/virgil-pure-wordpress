=== Virgil Pure ===
Contributors: virgilsecuritycom
Donate link: (none)
Tags: password-hardened-encryption, aead, pure, cryptography, protect-database
Requires at least: 5.0
Tested up to: 5.2
Stable tag: trunk
Requires PHP: 7.2
License: BSD 3-Clause License
License URI: https://github.com/VirgilSecurity/virgil-cli/blob/master/LICENSE

Free tool that protects user passwords from data breaches and both online and offline attacks, and renders stolen passwords useless even if your database has been compromised.

== Description ==

Virgil Pure WordPress Plugin is a free tool that protects user passwords from data breaches and both online and offline
attacks, and renders stolen passwords useless even if your database has been compromised.

Virgil Pure is based on a powerful and revolutionary cryptographic technology that provides stronger and more modern
security and can be used within any database or login system that uses a password, so it's accessible for business of any industry or size.

Learn more about the Pure technology [here](https://virgilsecurity.com/announcing-purekit)

== Installation ==

Currently the plugin is available only for PHP7.2 and PHP7.3!

In order to install the Pure Plugin you need to go through the following steps:
* add the crypto extensions into your server
* and then install the Plugin from the [WordPress Plugin Directory](https://wordpress.org/plugins/virgil-pure/) or from the [official GitHub repository](https://github.com/VirgilSecurity/virgil-pure-wordpress)

= Step #1. Add the crypto extensions into your server before using the Plugin =
[How to add the crypto extensions](https://github.com/VirgilSecurity/virgil-pure-wordpress#step-1-add-the-crypto-extensions-into-your-server-before-using-the-plugin)

= Step #2. Install Virgil Pure WordPress Plugin =

From the WordPress Plugin Directory:
* Navigate to the WordPress Dashboard
* Open "Plugins → Add New" tab
* Find "Virgil Pure" on the WordPress Plugin Directory
* Install and activate the Plugin

Or from the official GitHub repository:
* [Download the virgil_pure.zip file](https://github.com/VirgilSecurity/virgil-pure-wordpress/releases)
* Navigate to the WordPress Dashboard
* Open "Plugins → Add New" tab
* Upload the virgil-pure.zip file
* Install and activate the Plugin

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

(none)

== Changelog ==

= 0.2.0 =
* Add recovery feature
* Minor fixes

= 0.1.2 =
* Fix activation error
* Minor fixes

= 0.1.1 =
* Fix namespaces

= 0.1.0 =
* Init plugin

== Upgrade Notice ==

(none)
