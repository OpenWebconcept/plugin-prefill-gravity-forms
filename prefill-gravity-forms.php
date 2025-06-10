<?php

/**
 * Plugin Name:       Yard | BRP Prefill GravityForms
 * Plugin URI:        https://www.openwebconcept.nl/
 * Description:       Prefill GravityForms fields, based on a (Dutch) BSN number. Retrieve personal information and place these values in the corresponding fields.
 * Version:           1.7.6
 * Author:            Yard | Digital Agency
 * Author URI:        https://www.yard.nl/
 * License:           EUPL-1.2
 * License URI:       https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * Text Domain:       prefill-gravity-forms
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, abort.
 */
if (! defined('WPINC')) {
    die;
}

define('PG_VERSION', '1.7.6');
define('PG_DIR', basename(__DIR__));
define('PG_ROOT_PATH', __DIR__);
define('PG_PLUGIN_SLUG', 'prefill-gravity-forms');

/**
 * Manual loaded file: the autoloader.
 */
require_once __DIR__ . '/autoloader.php';
$autoloader = new OWC\PrefillGravityForms\Autoloader();

/**
 * Begin execution of the plugin
 *
 * This hook is called once any activated plugins have been loaded. Is generally used for immediate filter setup, or
 * plugin overrides. The plugins_loaded action hook fires early, and precedes the setup_theme, after_setup_theme, init
 * and wp_loaded action hooks.
 */
\add_action('plugins_loaded', function () {
    $plugin = \OWC\PrefillGravityForms\Foundation\Plugin::getInstance(__DIR__)->boot();
}, 10);
