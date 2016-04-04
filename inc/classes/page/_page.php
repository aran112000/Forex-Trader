<?php

namespace page;

/**
 * Class _page
 */
abstract class _page {

    protected $requires_login = true;

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
     * @param array $uri_parts
     */
    public function __controller(array $uri_parts) {
        if ($this->requires_login && !\user::isLoggedIn()) {
            header('location: /?r=' . urlencode(uri));
        }
    }

    /**
     * @return string
     */
    public function getNavigation(): string {
        if (\user::isLoggedIn()) {
            return '<ul class="nav nav-tabs">
  <li role="presentation"' . (uri == '/' ? ' class="active"' : '') . '><a href="/">Dashboard</a></li>
  <li role="presentation"' . (uri == '/trade-history' ? ' class="active"' : '') . '><a href="/trade-history">Trade History</a></li>
  <li role="presentation"' . (uri == '/settings' ? ' class="active"' : '') . '><a href="/settings">Settings</a></li>
  <li role="presentation"' . (uri == '/logout' ? ' class="active"' : '') . '><a href="/logout">Logout</a></li>
</ul>';
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getHeader(): string {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <title>ForexTrader</title>
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css" type="text/css" />
    <link rel="stylesheet" href="/css/styles.css" type="text/css" />
</head>
<body>
    <div class="container">
        <header class="clearfix">
            <a href="/" title="ForexTrader">
                <img src="/images/logo-trans.png" alt="ForexTrader logo" height="80" />            
            </a> 
            ' . $this->getNavigation() . '
        </header>';

        return $html;
    }

    /**
     * @return string
     */
    abstract protected function getBody(): string;

    /**
     * @return string
     */
    protected function getFooter(): string {
        $html = '<footer>';
        $html .= '<small>&copy; ' . date('Y') . ' ForexTrader | Developed by Aran Reeks | All rights reserved.</small>';
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