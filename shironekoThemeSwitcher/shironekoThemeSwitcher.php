<?php

namespace Shironeko;

/*
    Plugin Name: Shironeko Theme Switcher
    Description: A simple theme switcher.
    Author: Shironeko
    Version: 0.0.1
    Requires PHP: 7.0
*/

if (!defined('ABSPATH')) {
    exit;
}

class ThemeSwitcher {
    const REQUIRED_PHP_VERSION = 7.0;

    /**
     * Set up WP hooks for this plugin.
     */
    public static function init() {
        // Check dependencies on plugin activation
        register_activation_hook(__FILE__, [get_class(), 'activationHook']);

        // Load the user-selected theme
        add_action('setup_theme', [get_class(), 'setupTheme']);

        // Add the theme selector as a shortcode
        add_shortcode(
            'shironeko_theme_switcher_selector',
            [get_class(), 'shironekoThemeSwitcherSelector']
        );

        // Load the JS code for the theme selector
        add_action(
            'wp_enqueue_scripts',
            [get_class(), 'shironekoThemeSwitcherSelectorScript']
        );
    }

    /**
     * Plugin activation hook.
     */
    public static function activationHook() {
        // PHP version check
        $actualPhpVersion = phpversion();
        if (version_compare($actualPhpVersion, self::REQUIRED_PHP_VERSION, '<')) {
            exit('This plugin requires PHP version ' . self::REQUIRED_PHP_VERSION . '.'
                . ' (Current version: ' . htmlspecialchars($actualPhpVersion) . ')'
            );
        }
    }

    /**
     * Hook for the setupTheme action.
     */
    public static function setupTheme() {
        $allThemes = self::getThemes();
        $themeToSet = null;

        if (isset($_GET['theme'])
            && in_array($_GET['theme'], $allThemes, true)
        ) {
            // Set theme from URL
            $themeToSet = $_GET['theme'];

            // Set cookie to persist theme selection
            setcookie('ShironekoThemeSwitcher', $themeToSet);
        } elseif (isset($_COOKIE['ShironekoThemeSwitcher'])
            && in_array($_COOKIE['ShironekoThemeSwitcher'], $allThemes, true)
        ) {
            // Set theme from cookie
            $themeToSet = $_COOKIE['ShironekoThemeSwitcher'];
        }

        if (isset($themeToSet)) {
            add_filter(
                'stylesheet',
                function() use ($themeToSet) { return $themeToSet; }
            );
            add_filter(
                'template',
                function() use ($themeToSet) { return $themeToSet; }
            );
        }
    }

    /**
     * Shortcode to select themes. `[shironeko_theme_switcher_selector]`.
     *
     * @param Array $atts WP shortcode attributes.
     * @return String WP shortcode output.
     */
    public static function shironekoThemeSwitcherSelector($atts) {
        $currentTheme = get_template();
        $allThemes = self::getThemes();

        $shortcodeOutput = '<select data-id="shironekoThemeSwitcherSelector">';

        foreach ($allThemes as $theme) {
            $shortcodeOutput .= '<option'
                . ' val="' . htmlspecialchars($theme) . '"'
                . ($theme === $currentTheme ? ' selected' : '')
                . '>'
                    . htmlspecialchars($theme)
                . '</option>';
        }

        $shortcodeOutput .= '</select>';

        return $shortcodeOutput;
    }

    /**
     * Includes the script for the shironekoThemeSwitcherSelector shortcode.
     */
    public static function shironekoThemeSwitcherSelectorScript() {
        // Enqueue jQuery
        wp_enqueue_script('jquery');

        // Enqueue selector logic
        wp_enqueue_script(
            'shironekoThemeSwitcherSelector',
            plugins_url('shironekoThemeSwitcherSelector.js', __FILE__)
        );
    }

    /**
     * Get all available themes.
     *
     * @return Array Theme folder names.
     */
    private static function getThemes() {
        return array_map(
            function($theme) { return $theme->template; },
            wp_get_themes()
        );
    }
}

ThemeSwitcher::init();
