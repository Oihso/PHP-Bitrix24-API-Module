PHP Bitrix24 API Module
================

A basic PHP library for the Bitrix24 REST API.
An authentication module is included.

## Bitrix24 Documentation
[Russian documentation](http://dev.1c-bitrix.ru/rest_help/)<br/>
[English documentation](https://training.bitrix24.com/rest_help/)

## Requirements
- php: >=7.0.0
- ext-json: *
- ext-curl: *
- ext-mysqli: *

## Example ##
```php
<?php
use Bitrix24API\Bitrix24;

$Bitrix24 = new Bitrix24(
    array(
        'companyDomain' => 'example.bitrix24.com', //Bitrix24 company URL
        'scope' => 'crm,user,telephony', //Bitrix24 auth scopes. Available variants: https://training.bitrix24.com/rest_help/rest_sum/premissions_scope.php

        //Auth data
        'auth' => array(
            //Bitrix24 User auth data
            'login'    => 'user@bitrix24.com',
            'password' => '1234',

            //Bitrix24 App auth data
            'clientId' => 'local.55a6ca262e8482.12345678',
            'clientSecret' => 'eOk9XtOWbdTjUgQmBL1MYNpKl0Jwt11JLHYHIADX62f3c6PA29'
        ),

        //Database config
        'database' => array(
            'settingsTableName' => 'config',
            'settingsKeyName' => 'key',
            'settingsValueName' => 'value'
        )
    ),
    array(
        '127.0.0.1', //MySQL Host
        'user',	//MySQL Username
        '1234',	//MySQL Password
        'db', //MySQL Database
        3306 //MySQL Port
    ) //Database connection
);

//Gets deal with DEAL_ID = '1234'
$dealData = $Bitrix24->callMethod("crm.deal.get", array('id'=>'1234'));
```
## What is `settingsKeyName` and `settingsValueName` values?
PHP Bitrix24 API Module uses standard key-value pair table to store data.
Here is an [example](http://prntscr.com/m77zsn)<br/>
If you have another names for `key-value` pairs (for example: option `as key` - config `as value`), you need to specify this names in config. Otherwise the library will not work.

## Installation
1. Install library in your project: `composer require oihso/bitrix24-api-module`
2. Create new table (or use an existing one) to store settings. The table must be key-value type. Key and value must be `TEXT` fields
3. Add the row "php_bitrix24_auth_time" = "0" to the table
4. Add the row "php_bitrix24_auth_code" = "code" to the table

## How to configure your Bitrix24
1. Go to `Applications -> Add application -> My account only -> Add` and create app with `Only API` checkmark. Also you need to select all needed scopes
2. Copy `client_id` and `client_secret` and paste it into `Bitrix24` class config
3. On the left-side menu go to `Invite users` and register new user. It will be your "service" account for REST API
4. Create new `group/department` for your new "service" account
5. Set up permissions for this `group/department`. You need to allow administrative rules for this account.
6. Now, you can paste user's credentials into `Bitrix24` class config

## License ##

"PHP Bitrix24 API Module" is licensed under the Apache License - see the `LICENSE.txt` file for details
