<?php

/**
 * Class ajax
 */
final class ajax {

    /**
     * @var array
     */
    private static $inject_script = [];
    /**
     * @var array
     */
    private static $inject_html = [];
    /**
     * @var array
     */
    private static $update_html = [];
    /**
     * @var array
     */
    private static $delete_element = [];
    /**
     * @var string|null
     */
    private static $redirect = null;

    /**
     * @param string $script
     */
    public static function addInjectScript(string $script) {
        self::$inject_script[] = trim($script, ' ;') . ';';
    }

    /**
     * @param string $html
     * @param string $selector
     * @param string $position
     */
    public static function addInjectHtml(string $html, string $selector, string $position = 'pre') {
        self::$inject_html[] = [
            'selector' => $selector,
            'html' => $html,
            'position' => $position
        ];
    }

    /**
     * @param string $html
     * @param string $selector
     */
    public static function addUpdateHtml(string $html, string $selector = null) {
        if ($selector === null) {
            $selector = $_REQUEST['origin'];
        }
        self::$update_html[] = [
            'selector' => $selector,
            'html' => $html
        ];
    }

    /**
     * @param string $url
     */
    public static function setRedirectUrl(string $url) {
        self::$redirect = $url;
    }

    /**
     * @param string $selector
     */
    public static function doDeleteElement(string $selector) {
        self::$delete_element[] = $selector;
    }

    /**
     *
     */
    public static function doServe() {
        header('Content-Type: application/json', true);

        die(json_encode([
            'redirect' => self::$redirect,
            'update_html' => self::$update_html,
            //'inject_script' => self::$inject_script, // TODO
            //'inject_html' => self::$inject_html, // TODO
            //'delete' => self::$delete_element, // TODO
        ], JSON_PRETTY_PRINT));
    }
}