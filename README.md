CCV
=========

## Features
- Package manager
- Banking system
- Ticket system

## Installation
1. Upload all files to your web server running PHP
1. Create a VirtualHost for this website pointing to DIRECTORY/web (if you have a shared hosting environment, see Shared web server and then proceed to the next step).
1. Execute `php composer.phar install`.
1. Make the directories `cache`, `config` and `logs` writable for the web server (if they don't exist, create them and then make them writable).
1. Run the check script at check.php by pointing your browser to YOUR_URL/check.php and fix any problems you find there.
1. Create a database for CCV and import `ccv.sql`.
1. Go into the configuration and edit it to suit your needs.
1. You can now point your browser to YOUR_URL and you will see the homepage of your own CCV installation!
1. Remove `check.php` (and if you are using a Shared web server, also remove `composer.json`, `composer.lock` and `ccv.sql` if they exist).

### Shared web server
If you have a shared web server, you will have to move everything from web one level up. You will also have to go into the files `index.php` and `check.php` and find a line line that contains this `require __DIR__.'/../src`. Then remove the portion `../`. Save and upload it and you should be ready to go!

## Pastebin
If you want users to be able to install CCV without having to type over a script, you can put your script on Pastebin:

1. Point your browser to YOUR_URL/scripts/install
1. Copy everything and paste it in Pastebin.
1. Look at the URL bar. Copy the part behind `http://pastebin.com/` and add it to the config under pastebin. For example, you may add the following line to the configuration: `pastebin_id: YOUR_PASTEBIN_ID`.
1. Now your users will be able to install the script via Pastebin! Look under the tab 'Installation' for information how to.

## Default scripts
- `main`: This script contains the actual script, which actually makes it possible to upgrade your main script.
- `install`: This script makes it possible to install the actual script.
- `bankapi`: This script is used to communicate with CCV's integrated bank.
- `ticketapi`: This script is used to communicate with CCV's integrated ticket system.

## Translations
If you want to translate CCV in your own language, add the file to `config/locales`. Then set the locale configuration option to the filename omitting the extension. All files must be in the same format as the original English format. If a translation is not found in the translated version, it will fall back to the English translation. For example, say you want to translate it to German. You follow the following steps:

1. Create the file `config/locales/de.yml` and translate everything following the same format as `config/locales/en.yml`.
1. Set the locale configuration option to `de`. This could be the following line in the config: `locale: de`.
1. That is it. Point your browser to YOUR_URL and it should show up in the language your translated it into, in this case German.
1. But now, let's say you forgot to translate `script_update_success`. In this case, if this message is displayed, it will just display the default instead of the German version. In that way, you can also _translate_ it to your own site. If you like Update more than Save, you could just translate that one sentence and put it in a separate file called 'config/locales/custom.yml`. Now only that will be different, the rest will just be the same.


## Configuration
### Default:

```
database:
    driver: pdo_mysql # the database driver, 'pdo_mysql' for MySQL
    host: localhost # the host for the database
    port: 3306 # the port for the database
    name: ccv # the name of the database
    user: root # the username used to access the database
    password: '' # the password used to access the database
debug: false # whether debug mode is enabled. If debug mode is enabled, your users will see all details of the errors, which is probably a bad user experience.
features:
    bank: true # whether the banking system is enabled
    tickets: true # whether the ticket system is enabled, the banking system must be enabled for this to work.
log_transactions: true # whether to log transactions, this can fill up your database rather quickly if you make extensive use of banking
pastebin_id: null # the ID of the pastebin which contains your install script.
locale: en # the language of the site
bank:
    initial_balance: 100 # the initial balance that users get when their account is created
    daily_amount: 20 # the amount that users can get daily
site_name: CCV # the name of the site that is shown to users
executable_name: ccv # the recommended name of the executable
script:
    verbs: # verbs used in the command as first parameter
        get: get
        put: put
        list: list
```