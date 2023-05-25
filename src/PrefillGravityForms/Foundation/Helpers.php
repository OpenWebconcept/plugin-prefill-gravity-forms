<?php

namespace OWC\PrefillGravityForms\Foundation\Helpers;

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
    } catch(\Exception $e) {
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
    } catch(\Exception $e) {
        $decrypted = '';
    }

    return $decrypted ?: '';
}

/**
 * @param string $setting
 * @param string $default
 *
 * @return string
 */
function config(string $setting, string $default = ''): ?string
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
