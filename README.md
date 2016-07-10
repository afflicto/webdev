# What's this?
*webdev* is a CLI tool written in PHP that automates much of the ground-work your your web development projects require - so you can get to work asap.

Imagine typing a single command like and having the following done in milliseconds:

1. Creates a directory like `/var/www/new-site` or `C:\wamp64\www\new-site` or `D:/websites/new-site`
2. Creates an appropriate configuration for your web server (virtualhost directives for apache etc)
3. Adds these lines to your `hosts` file:
4.  - `127.0.0.1 new-site.dev`
5.  - `127.0.0.1 www.new-site.dev`
6.  Creates a database on your MySQL Server
7.  Perhaps creates a folder for your non-code stuff `/home/John/Dropbox/Projects/new-site`.

## Changelog
- Now generates virtualhost directives for WAMP.

## Requirements
- [Composer](http://getcomposer.org)
- Your composer vendor directory in your PATH

## Install & Use
Before you use it, remember that this tool is in alpha and isn't TDD'ed or anything. Don't blame me if you accidentally delete your projects!

Grab it via packagist.org:
```bash
composer global require "afflicto/webdev=dev-master"
```

The first time you run it, it should automatically do the `init` command, which is interactively sets up **webdev** in your particular environment.

## Commands
**legend:**
| [abc]  | Argument
|-----|-
| [abc?] | Optional argument

##### webdev config [key] [value?]

Examples:
- `webdev config web.providers.wamp.web_root C:/wamp/www` sets the web root directory where your new projects will be created when using the "apache" web server.
- `webdev config database.default` tells you the current value of the default database provider.
- `webdev config web` prints the value of all the keys and values in that array (If it is an array. In this case, it is).
- `webdev config mysql.username "root"` sets the mysql username

## About & Contributing
It's written in PHP, built on Composer and leverages Symfony/Console as well as Illuminate/Config.
The configuration is stored as a PHP array in the installation directory (composers's global vendor dir).

feel free to send a pull request. It would be nice to clean up the code a bit, add support for more things and make better abstractions and maybe also support a more modular approach.

## Licens
MIT. See LICENSE file.