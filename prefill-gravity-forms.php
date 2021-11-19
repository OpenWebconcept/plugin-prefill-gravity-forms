<?php

/**
 * Plugin Name:       Yard | Prefill GravityForms
 * Plugin URI:        https://www.openwebconcept.nl/
 * Description:       Prefill GravityForms fields, based on the dutch BSN number. Retrieve personal information and place these values in the corrensponding fields.
 * Version:           1.0.1
 * Author:            Yard | Digital Agency
 * Author URI:        https://www.yard.nl/
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       prefill-gravity-forms
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, abort.
 */
if (!defined('WPINC')) {
    die;
}

define('PG_VERSION', '1.0.3');
define('PG_DIR', basename(__DIR__));
define('PG_ROOT_PATH', __DIR__);

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
