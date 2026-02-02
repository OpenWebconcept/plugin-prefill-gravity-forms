# Yard | BRP Prefill GravityForms

## Description

Prefill GravityForms fields, based on the Dutch BSN number. Retrieve personal information and place these values in the corrensponding fields.

## Dependencies

To use this plug-in, the following dependencies are required:

- GravityForms (premium)

In addition, at least one of the following plug-ins must be installed to enable authentication by BSN:

- Yard | GravityForms DigiD (<https://github.com/yardinternet/owc-gravityforms-digid>)
- OWC Signicat OpenID (<https://github.com/yardinternet/plugin-owc-signicat-openid>)

## Features

- Map GravityForms fields to attributes retrieved from the configured supplier.
- Mapped fields will be prefilled while rendering a form.
- Gutenberg block for displaying personal data of the current logged in user.

## Installation

### Manual installation

1. Upload the 'prefill-gravity-forms' folder in to the `/wp-content/plugins/` directory.
2. `cd /wp-content/plugins/prefill-gravity-forms`
3. Run `composer install, NPM asset build is in version control already.
4. Activate the plugin in via the WordPress admin.

### Composer installation

1. `composer source git@github.com:OpenWebconcept/plugin-prefill-gravity-forms.git`
2. `composer require plugin/prefill-gravity-forms`
3. `cd /wp-content/plugins/prefill-gravity-forms`
4. Run `composer install`, NPM asset build is in version control already.

### Setup

1. Go to '/wp-admin/admin.php?page=gf_settings&subview=owc-gravityforms-iconnect' and configure all the required settings.

- 1. Suppliers will provide the needed certificates which need to be selected in order to make prefilling form fields work.
- 2. Suppliers will also provide an API-key, certificates password (if needed) and a base URL.
- 3. [OIN](https://logius.nl/domeinen/toegang/organisatie-identificatienummer/wat-is-het) is a unique number for organizations provided by Logius.

2. Go to the form settings of the form you want to configure.
3. Scroll down and look for the 'iConnect' panel and configure the settings.

### Cache Encryption

To enable secure caching of sensitive data, you **must define an encryption key** in your `wp-config.php` file. This key is used to encrypt and decrypt the cached data and should be kept secret at all times.

Add the following line to your `wp-config.php`:

```php
// Prefill Gravity Forms – Cache Encryption Key
define('PG_CACHE_ENCRYPTION_KEY', 'your-unique-32-character-key');
```

Important:

- Use a randomly generated, 32-character key for strong AES-256 encryption.
- Never store this key in the database.
- Keep it secret and secure — anyone with access to this key can decrypt cached data.

## License

The source code is made available under the [EUPL 1.2 license](https://github.com/OpenWebconcept/plugin-prefill-gravity-forms/blob/main/LICENSE.md). Some of the dependencies are licensed differently, with the BSD or MIT license, for example.

## User model

The `UserModel` provides a simple way to access BRP (Basisregistratie Personen) data that has been retrieved after a valid DigiD login.
It automatically detects which data supplier is configured (in the add-on settings), loads the correct controller, and exposes a small set of helper methods for use in templates or form-prefill logic.

Before accessing any user attributes, always check whether the user is authenticated using DigiD.

### Usage

```php
$user = new \OWC\PrefillGravityForms\Models\UserModel();

if ( $user->isLoggedIn() ) {
    $bsn  = $user->bsn();
    $age  = $user->age();
}
```

This model does not handle authentication itself, it only exposes data retrieved by the underlying BRP supplier controller.
If a controller fails to load (e.g., misconfiguration or missing supplier), the model gracefully returns default values.

To use this model, make sure it is enabled in the settings available at '/wp-admin/admin.php?page=gf_settings&subview=owc-gravityforms-iconnect'.
Otherwise, the object will be instantiated but will not contain any data.

## Logging

Enable logging to monitor errors during communication with the BRP suppliers.

- Logs are written daily to `pg-log{-date}.json` in the WordPress webroot directory.
- A rotating file handler keeps up to 7 log files by default, deleting the oldest as needed.
- You can change the maximum number of log files using the filter described below.

## Hooks

### Change the maximum number of log files

Use the following filter to alter the rotating file handler's max files setting:

```php
apply_filters('pg::logger/rotating_filer_handler_max_files', PG_LOGGER_DEFAULT_MAX_FILES)
```

### Intercept exceptions for custom handling

You can intercept exceptions caught by the plugin for additional processing or custom logging using this filter:

```php
do_action('pg::exception/intercept', $exception, $method)
```

The `$exception` parameter contains the caught exception object.

### Provide Custom Mapping Options from a Theme Directory

This plugin includes supplier-specific mapping option files. In version 1 of the "HaalCentraal API", all available fields were returned, even when only a subset was needed.

Since version 2 of HaalCentraal, this has changed: the goal binding (doelbinding) now determines which fields are returned. This results in a more concise dataset that contains only the necessary fields. Because each municipality (gemeente) can define its own unique goal bindings and corresponding fields, this plugin cannot include all possible mapping configurations by default.

```php
add_filter('pg::theme/dir_mapping_options', function ($value) {
    return __DIR__ . '/templates/owc-prefill/';
}, 10, 1);
```
