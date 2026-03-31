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

> Note: The composer source command mentioned in instruction step 1. might not exist. Use one of these alternatives:

#### Option A: Direct Git Clone (Recommended)

1. `cd /wp-content/plugins/`
2. `git clone git@github.com:OpenWebconcept/plugin-prefill-gravity-forms.git`
3. `cd plugin-prefill-gravity-forms`
4. Run `composer install --no-dev` (use `--no-dev` to avoid development dependencies that require additional PHP extensions)

#### Option B: Via Composer (if you have a composer.json in your WordPress root)

1. Add to your `composer.json` repositories section:
```json
{
       "repositories": [
           {
               "type": "vcs",
               "url": "git@github.com:OpenWebconcept/plugin-prefill-gravity-forms.git"
           }
       ]
   }
```

2. In your Wordpress root dir: `composer require plugin/plugin-prefill-gravity-forms`
3. `cd /wp-content/plugins/plugin-prefill-gravity-forms`
4. Run `composer install --no-dev`

**Technical Note:** The plugin uses both a custom autoloader for its own classes and Composer's autoloader for external dependencies. The Composer autoloader has been included before the custom autoloader to ensure all dependencies (like `DI\ContainerBuilder`) are available.

### Setup

1. Go to '/wp-admin/admin.php?page=gf_settings&subview=owc-gravityforms-iconnect' and configure all the required settings.

- 1. Suppliers will provide the needed certificates which need to be selected in order to make prefilling form fields work.
- 2. Suppliers will also provide an API-key, certificates password (if needed) and a base URL.
- 3. [OIN](https://logius.nl/domeinen/toegang/organisatie-identificatienummer/wat-is-het) is a unique number for organizations provided by Logius.

2. Go to the form settings of the form you want to configure.
3. Scroll down and look for the 'iConnect' panel and configure the settings.

### 🔐 Cache Encryption

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
