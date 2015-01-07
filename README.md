# Enhanced Laravel 5 Maintenance Mode

This package is a drop-in replacement for Laravel 5's maintenance mode. It includes:
 - Allowing custom maintenance messages to be shown to users
 - Including a timestamp of when the application went down
 - Exempting select users via custom exemption classes
 
## Table of Contents

  1. [Installation](#installation)
  1. [Usage](#usage)
  1. [Configuration](#configuration)
    1. [Default Configuration](#default-configuration)    
    1. [Overriding Defaults](#overriding-defaults)    
  1. [Exemptions](#exemptions)
    1. [Default Exemptions](#default-exemptions)
      1. [IP Whitelist](#ip-whitelist)
      1. [Environment Whitelist](#environment-whitelist)
    1. [Creating a New Exemption](#creating-a-new-exemption)
  1. [Views](#views)
    1. [Application Down](#application-down)
    1. [Maintenance Notification](#maintenance-notification)

## Installation

Within `composer.json` add the following line to the end of the `require` section:

```json
"misterphilip/maintenance-mode": "dev-development"
```

Next, run the Composer update command:

```bash
$ composer update
```

Add `'MisterPhilip\MaintenanceMode\MaintenanceModeServiceProvider',` and 
`'MisterPhilip\MaintenanceMode\MaintenanceCommandServiceProvider',` to the end of the 
`$providers` array in your `config/app.php`:

```php
'providers' => [

    /*
     * Application Service Providers...
     */
    'App\Providers\AppServiceProvider',
    'App\Providers\EventServiceProvider',
    'App\Providers\RouteServiceProvider',

    ...

    'MisterPhilip\MaintenanceMode\MaintenanceModeServiceProvider',
    'MisterPhilip\MaintenanceMode\MaintenanceCommandServiceProvider',
],
```

Finally, in `app/Http/Kernel.php` replace

```php
'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
```

with

```php
'MisterPhilip\MaintenanceMode\Http\Middleware\CheckForMaintenanceMode',
```

## Usage

This package uses the same syntax as the default `artisan down` command, except with 1 optional argument:

```bash
$ php artisan down [message]
```

For example,

```bash
$ php artisan down "We're currently upgrading our system! Please check back later."
```

would show users a message of "We're doing some routine maintenance! Be back soon!". If you don't pass in a 
message, the default "We're currently working on the site, please try again later" will
display to the users. Of course this default is configurable via a language string.

To bring your application back online, run the normal app up command:

```bash
$ php artisan up
```

## Configuration

This package is a drop-in replacement for the default maintenance mode provided with Laravel 5. This means 
that you do not have to do any configuration out-of-the-box. However, if you'd like to tweak some of the 
settings, there are a number of configuration values that are available to make this package a better fit 
for your application. 

### Default Configuration

Below are the default configuration options and a short description on each. Don't worry, all of this 
information is within the configuration file too!

  - `view` (string)
    - The view to show to users when maintenance mode is currently enabled
    - Defaults to `maintenancemode::app-down`
  - `notification-styles` (boolean)
    - Include CSS styling with the optional [maintenance notification](#maintenance-notification) view
    - Defaults to `true`
  - `inject.global` (boolean)
    - Enable or disable global visibility to maintenance mode variables (accessible in all views)
    - Defaults to `true`
  - `inject.prefix` (string)
    - Prefix the maintenance mode variables to prevent view variable name collisions
    - Defaults to `MaintenanceMode`
  - `language-path` (string)
    - The path to the maintenance mode language strings.
    - Defaults to `maintenancemode::defaults`
  - `exempt-ips` (string array)
    - An array of IP address that will always be exempt from the application down page
    - Defaults to `['127.0.0.1']`
  - `exempt-ips-proxy` (boolean)
    - Use [proxies](http://symfony.com/doc/current/components/http_foundation/trusting_proxies.html) 
    to get the user's IP address
    - Defaults to `false`
  - `exempt-environments` (string array)
    - An array of enviornment names that will always be exempt from the application down page
    - Defaults to `['local']`
  - `exemptions` (string array)
    - A list of the exemption classes to execute. *See [Exemptions](#exemptions)*
    - Defaults to: 
```php
    '\MisterPhilip\MaintenanceMode\Exemptions\IPWhitelist',
    '\MisterPhilip\MaintenanceMode\Exemptions\EnvironmentWhitelist',
```

### Overriding Defaults

If you need to override the default configuration values, run the following command:

```bash
$ php artisan publish:config misterphilip/maintenance-mode
```

Now you can edit the values at `config/packages/misterphilip/maintenancemode/config.php`. Additionally, 
if you need to have environment-specific configurations, you can create a new folder each o the 
environments (e.g. `config/packages/misterphilip/maintenancemode/[environment]/config.php`).

## Exemptions

Exemptions allow for select users to continue to use the application like normal based on a specific
set of rules. Each of these rule sets are defined via a class which is then executed against.

### Default Exemptions

By default, an IP whitelist and an application environment whitelist are included with this package
to get you off the ground running. Additionally, more examples are provided for various types of
exemptions that might be useful to your application.

##### IP Whitelist

This exemption allows you to check the user's IP address against a whitelist. This is useful for
always including your office IP(s) so that your staff doesn't see the maintenance page.

Configuration values included with this exemption are:

  - `exempt-ips` - An array of IP addresses that will not see the maintenance page
  - `exempt-ips-proxy` - Set to `true` if you have IP proxies setup

##### Environment Whitelist

This exemption allows you to check if the current environment matches against a whitelist. This is
useful for local development where you might not want to see the maintenance page.

Configuration values included with this exemption are:

  - `exempt-environments` - An array of environments that will not display the maintenance page
 
### Creating a new exemption

Setting up a new exemption is simple:

  1. Create a new class and extend `MisterPhilip\MaintenanceMode\Exemptions\MaintenanceModeExemption`.
  You might consider creating these files in `app\Exemptions` or `app\Infrastructure\Maintenance`, but 
  you're free to place them where you want.
  2. This class must include an `isExempt` method. This method should return `true` if the user should 
  not see the maintenance page. If this returns `false`, it indicates that the user does not match 
  your ruleset and other exceptions should be checked.
  3. Add the full class name to the `exemptions` array in the configuration file.

Below is an template to use for a new exemption class `SampleExemption`:

```php
<?php namespace App\Exemptions;

use MisterPhilip\MaintenanceMode\Exemptions\MaintenanceModeExemption;

class SampleExemption extends MaintenanceModeExemption
{
    /**
     * Execute the exemption check
     *
     * @return bool
     */
    public function isExempt()
    {
        return true; // or false
    }
}
```

## Views

There are 2 views included with this package: an "application down" page that replaces the current "Be 
right back!" page, and a "maintenance notification" which is a notification bar that tells exempted users 
that the application is in maintenance mode.

### Application Down

![Default maintenance page](examples/screenshots/maintenance-mode-page.png "Default maintenance page")
*The default maintenance page, `maintenancemode::app-down`*

Included is a default view that displays your custom message and a timestamp for your users. To change this page, 
update the `view` configuration value to point to the new view. The following variables are available for 
you to use:

  - `$MaintenanceModeEnabled` - Check to see if the maintenance mode is enabled
  - `$MaintenanceModeMessage` - The message that should be displayed to users (either the one passed via 
  the command call, or the default from the language file)
  - `$MaintenanceModeTimestamp` - The timestamp from when the application went into maintenance mode

**NOTE**: If you've changed the `inject.prefix` configuration value, you'll need to reflect this change in the 
variable names above. For example, if `inject.prefix = "Foobar"`, your view variables would be `$FoobarEnabled`, 
`$FoobarMessage`, and `$FoobarTimestamp`. 

**NOTE**: By default, these variables are available in all views. To disable this functionality and have it 
only inject variables on the maintenance page, change the `inject.global` configuration value to `false`.

### Maintenance Notification

![Maintenance notification](examples/screenshots/maintenance-mode-notification.png "Maintenance notification")
*The optional maintenance notification, `maintenancemode::notification`*

We've included a maintenance notification for users that want to include a notice to those that are exempt 
from seeing the maintenance page. We've found that as an admin, it's helpful to know when your application 
is in maintenance mode in the event that you've forgotten to disable it or it was turned on automatically. 

You can enable this notification by placing the following code within your main blade layouts file(s):

```php
@include('maintenancemode::notification')
```