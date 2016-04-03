<?php

/**
 * Class _page
 */
abstract class _page {

    /**
     * @var array
     */
    private static $js_files = [];

    /**
     * @var array
     */
    private static $inline_js = [];

    /**
     * @return string
     */
    public function get() {
        return $this->getHeader() . $this->getBody() . $this->getFooter();
    }

    /**
     * @return string
     */
    protected function getHeader(): string {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forex Trader</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css" type="text/css" />
    <link rel="stylesheet" href="/css/styles.css" type="text/css" />
</head>
<body>
    <div class="container">
        <header>
    
        </header>';

        return $html;
    }

    /**
     * @return string
     */
    abstract function getBody(): string;

    /**
     * @return string
     */
    protected function getFooter(): string {
        $html = '<footer>';
        $html .= '<small>&copy; ' . date('Y') . ' Forex Trader | Developed by Aran Reeks | All rights reserved.</small>';
        $html .= '</footer>';
        $html .= '</div>';
        if (!empty(self::$js_files)) {
            foreach (self::$js_files as $js_file) {
                $html .= '<script src="' . $js_file . '"></script>'."\n";
            }
        }
        if (!empty(self::$inline_js)) {
            $html .= '<script>';
            $html .= implode("\n", self::$inline_js);
            $html .= '</script>';
        }
        $html .= '</body>
</html>';

        return $html;
    }

    /**
     * @param string $javascript
     */
    public static function setInlineJs(string $javascript) {
        if (!empty($javascript)) {
            self::$inline_js[md5($javascript)] = rtrim($javascript, ';') . ';';
        }
    }

    /**
     * @param string $js_file
     */
    public static function setJsFiles(string $js_file) {
        if (!empty($js_file)) {
            self::$js_files[$js_file] = $js_file;
        }
    }
}