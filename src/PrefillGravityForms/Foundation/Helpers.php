<?php

namespace OWC\PrefillGravityForms\Foundation\Helpers;

use Exception;
use OWC\PrefillGravityForms\Foundation\Plugin;

function app(): Plugin
{
    return resolve('app');
}

function make($name, $container)
{
    return \Yard\DigiD\Foundation\Plugin::getInstance()->getContainer()->set($name, $container);
}

function storage_path(string $path = ''): string
{
    return \ABSPATH . '../../storage/' . $path;
}

function resolve($container, $arguments = [])
{
    return \OWC\PrefillGravityForms\Foundation\Plugin::getInstance()->getContainer()->get($container, $arguments);
}

/**
 * Encrypt a string.
 */
function encrypt($string): string
{
    try {
        $encrypted = resolve(\OWC\PrefillGravityForms\Foundation\Cryptor::class)->encrypt($string);
    } catch (Exception $e) {
        $encrypted = '';
    }

    return $encrypted;
}

/**
 * Decrypt a string.
 */
function decrypt($string): string
{
    try {
        $decrypted = resolve(\OWC\PrefillGravityForms\Foundation\Cryptor::class)->decrypt($string);
    } catch (Exception $e) {
        $decrypted = '';
    }

    return $decrypted ?: '';
}

function config(string $setting, $default = '')
{
    return resolve('config')->get($setting, $default);
}

function view(string $template, array $vars = []): string
{
    $view = resolve(\OWC\PrefillGravityForms\Foundation\View::class);

    if (! $view->exists($template)) {
        return '';
    }

    return $view->render($template, $vars);
}

/**
 * Get the current selected supplier on a per form basis.
 * Returns label as default, use parameter $getKey to return the key from the config array.
 */
function get_supplier(array $form, bool $getKey = false): string
{
    $allowed = config('suppliers', []);
    $supplier = $form[sprintf('%s-form-setting-supplier', 'owc')] ?? '';

    if (! is_array($allowed) || empty($allowed) || empty($supplier)) {
        return '';
    }

    if (! in_array($supplier, array_keys($allowed))) {
        return '';
    }

    if ($getKey) {
        return $supplier;
    }

    return $allowed[$supplier] ?? '';
}
