# Yard | BRP Prefill GravityForms

## Description

Prefill GravityForms fields, based on the dutch BSN number. Retrieve personal information and place these values in the corrensponding fields.

## Dependencies

In order to use this plug-in there are two required plug-ins:

- GravityForms (premium)
- Yard | GravityForms DigiD (private repo, contact [Yard | Digital Agency](https://www.yard.nl/) for access)

See [here](https://github.com/OpenWebconcept/plugin-prefill-gravity-forms/blob/main/config/core.php) for more details.

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

## License

The source code is made available under the [EUPL 1.2 license](https://github.com/OpenWebconcept/plugin-prefill-gravity-forms/blob/main/LICENSE.md). Some of the dependencies are licensed differently, with the BSD or MIT license, for example.

## Hooks

#### Provide Custom Mapping Options from a Theme Directory

This plugin includes supplier-specific mapping option files. In version 1 of the "HaalCentraal API", all available fields were returned, even when only a subset was needed.

Since version 2 of HaalCentraal, this has changed: the goal binding (doelbinding) now determines which fields are returned. This results in a more concise dataset that contains only the necessary fields. Because each municipality (gemeente) can define its own unique goal bindings and corresponding fields, this plugin cannot include all possible mapping configurations by default.

```php
add_filter('owc_prefill_gravity_forms_theme_dir_mapping_options', function ($value) {
    return __DIR__ . '/templates/owc-prefill/';
}, 10, 1);
```
