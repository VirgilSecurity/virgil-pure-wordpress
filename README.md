# Virgil Pure WordPress Plugin

[Introduction](#introduction) | [Features](#features) | [Installation](#installation) | [How To Use Plugin](#how-to-use-plugin) | [F.A.Q](#faq) | [License](#license) | [Support](#support)

## Introduction
<p><img src="https://cdn.virgilsecurity.com/assets/images/github/logos/pure_plugin.png" align="left" hspace="0" vspace="0"></p>

[Virgil Pure Wordpress Plugin](https://wordpress.org/plugins/virgil-pure) is a free tool that protects user passwords from data breaches and both online and 
offline attacks, and renders stolen passwords useless if the database is compromised. 

Virgil Pure is based on a powerful and revolutionary cryptographic technology that provides stronger and more advanced security than salting and hashing, and it can be used within any database or login system that uses a password, so it's accessible for businesses of any industry or size.

Learn more about the Pure technology [here](https://virgilsecurity.com/announcing-purekit).

## Features

#### Available
- Zero knowledge of users' passwords
- Protects passwords from online and offline attacks
- Instant invalidation of stolen database records
- Plugin deactivation and restoration of the previous user authorization system

#### Coming soon
- User data encryption with a personal key

## Installation

Currently the plugin is available only for **PHP7.2** and **PHP7.3**! 

To install the Pure Plugin you need to go through the following steps:
- add the crypto extensions into your server
- install the Plugin from the [WordPress Plugin Directory](#from-the-wordpress-plugin-directory) or from [this repository](#from-this-repository)

### Step #1. Add the crypto extensions into your server before using the Plugin

- [Download](https://github.com/VirgilSecurity/virgil-pure-wordpress/releases) *virgil-test.zip*, unzip it and execute
 on your server [virgil-test.php](/_help/virgil-test.php) file.

- [Download](https://github.com/VirgilSecurity/virgil-pure-wordpress/releases) and unzip *%YOUR_OS%_extensions.zip* 
archive according to your server operating system and PHP version.

- Make sure you have access to edit the php.ini file (for example, use *root* for the Linux/Darwin or run *cmd* under 
administrator for the Windows).
- Copy extension files to the extensions directory.
    - For Linux/Darwin:
    ```
     $ path="%PATH_TO_EXTENSIONS_DIR%" && cp vsce_phe_php.so $path && cp virgil_crypto_php.so $path
    ```
    - For Windows:
    ```
     $ set path=%PATH_TO_EXTENSIONS_DIR% && copy vsce_phe_php.dll %path% && copy virgil_crypto_php.dll %path%
    ```
- Add the extensions into the php.ini file 
    ```
    $ echo -e "extension=vsce_phe_php\nextension=virgil_crypto_php” >> %PATH_TO_PHP.INI%
    ```
    
- Restart your server or php-fpm service

#### Extension installation example

Our web stack is: *Linux, nginx, php7.2-fpm*

- Execute the [virgil-test.php](/_help/virgil-test.php) to find your path to the extensions directory and path to 
the php.ini file:
    <p><img src="https://raw.githubusercontent.com/VirgilSecurity/virgil-pure-wordpress/master/_help/s-1.png" 
    width="60%"></p> 

- Then, go to the command line interface (CLI) to specify the paths you identified in the previous step:
    <p><img src="https://raw.githubusercontent.com/VirgilSecurity/virgil-pure-wordpress/master/_help/s-2.png" 
    width="60%"></p>

- Reload the page in your browser to see that the extension is loaded (`IS_VSCE_PHE_PHP_EXTENSION_LOADED => true` and 
`IS_VIRGIL_CRYPTO_PHP_EXTENSION_LOADED => true`):
    <p><img src="https://raw.githubusercontent.com/VirgilSecurity/virgil-pure-wordpress/master/_help/s-3.png" 
    width="60%"></p>
    
Now it's time to add the Virgil Pure Plugin to your WordPress project.

### Step #2. Install Virgil Pure WordPress Plugin

- #### From the WordPress Plugin Directory

    - Navigate to the WordPress Dashboard
    - Open "Plugins → Add New" tab
    - Find "Virgil Pure" in the WordPress Plugin Directory
    - Install and activate the Plugin

- #### From this repository

    - [Download the virgil-pure.zip file](https://github.com/VirgilSecurity/virgil-pure-wordpress/releases)
    - Navigate to the WordPress Dashboard
    - Open "Plugins → Add New" tab
    - Upload the virgil-pure.zip file
    - Install and activate the Plugin
    
The Pure Plugin should now be activated:
<p><img src="https://raw.githubusercontent.com/VirgilSecurity/virgil-pure-wordpress/master/_help/s-4.png" width="70%"></p>

## How To Use Plugin

### Set up Credentials
To start working with the plugin, in the plugin tab on your WordPress dashboard, you'll need to input some credentials in the corresponding fields via the following steps:
- create an account at [Virgil Dashboard](https://dashboard.virgilsecurity.com/)
- create a Pure application
- copy your Pure application's credentials from the config file or Virgil Security dashboard: `APP_TOKEN`, `APP_SECRET_KEY`, `SERVICE_PUBLIC_KEY`
- paste them into the corresponding fields

### Generate Recovery Keys

You’ll need to generate a recovery key so that the password hashes that are currently in your database can be 
recovered if you ever need to deactivate the Pure plugin. Your recovery key will encrypt the original password hashes and 
will store the encrypted values in a (wp_usermeta) table in your database. 

The recovery key utilizes a public and private key pair. The public key will be stored in your database and the private key must be stored by you securely on another external device. Please read our FAQ section for best practices and more information. 

### Migration

Migration is the process by which the plugin requests cryptographic data from the Virgil server to associate user 
passwords (user_pass) with cryptographic enrollments provided by the server. New enrollment records are then created and 
stored in your database (wp_usermeta) place of the of user passwords.

Once the Pure plugin is configured in your system, simply click the "Start migration" button to start the migration process.

### Records Update (optional)

This function allows you to use a special `UPDATE_TOKEN` to update all of the enrollment records in your database. This action doesn't require changing user passwords or modifying the scheme of the existing table.

Navigate to your Pure application panel at [Virgil Dashboard](https://dashboard.virgilsecurity.com/), press "BEGIN 
ROTATION PROCESS", then “SHOW UPDATE TOKEN” button to get the `UPDATE_TOKEN`. Insert the `UPDATE_TOKEN` into the field at the Virgil Pure plugin tab.

This can be used when a database is known to be breached. For security reasons, we recommend proactively updating records every one week.

### Recovery (optional)

When you need to deactivate the Pure plugin, you can go through the Recovery process via the Wordpress dashboard and 
use the recovery key to restore the original password hashes in place of the cryptographic values generated by the Pure plugin.

## F.A.Q.

#### - Do users have to change their passwords if the database has been compromised? 
If a database has been stolen, users do not need to change their original passwords. However, you will need to rotate all user records in your database. This will use cryptography to disconnect the compromised Pure records from the original passwords, leaving any unauthorized party empty handed.

#### - How does the Recovery Key work?
Recovery Key is a key pair that allows you to recover the original user password hashes if you ever need to deactivate the Pure plugin. The Recovery Key encrypts the password hashes, and stores the encrypted values into the wp_usermeta table in your database.

The Recovery Key utilizes a public and private key pair. The public key is stored in the wp_option table and
the Private Key must be stored by you securely on an external device.

#### - How much does it cost? 
Pure is a FREE toolkit. All libraries are open source and can be found on GitHub, where they are available for free to any user.

#### - What if an App Private Key gets lost?
There is no way to restore the `APP_SECRET_KEY`. The database records become inaccessible and therefore useless. So, it is highly recommended that you immediately create a backup of the key in a secure location to avoid losing it.

## License
See [LICENSE](https://github.com/VirgilSecurity/virgil-pure-wordpress/tree/master/LICENSE) for details.

## Support
Our developer support team is here to help you. Find out more information on our [Help Center](https://help.virgilsecurity.com/).

You can find us on [Twitter](https://twitter.com/VirgilSecurity) or via email at support@VirgilSecurity.com.

Also, get extra help from our support team on [Slack](https://virgilsecurity.com/join-community).
