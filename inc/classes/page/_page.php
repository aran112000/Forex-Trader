<?php

namespace page;

/**
 * Class _page
 */
abstract class _page {

    use \seo;

    protected $requires_login = true;

    /**
     * @var array
     */
    private static $js_files = [
        '//ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js',
        '/js/bootstrap.min.js',
        '/js/script.js',
    ];

    /**
     * @var array
     */
    private static $inline_js = [];

    /**
     * @return string
     */
    public function get() {
        return $this->getHeader() . "\n".'<main>' . "\n\t" . $this->getBody() . "\n" .'</main>'."\n" . $this->getFooter();
    }

    /**
     * @param array $uri_parts
     */
    public function __controller(array $uri_parts) {
        if ($this->requires_login && !\user::isLoggedIn()) {
            header('location: /?r=' . urlencode(uri));
        }

        $module_name = ucwords(str_replace(['_', '-', 'page\\'], ' ', get_called_class()));
        $this->setTitleTag($module_name);
    }

    /**
     * @return string
     */
    public function getNavigation(): string {
        $html = '<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">'."\n";
        if (\user::isLoggedIn()) {
            $html .= '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>' . "\n";
        }
        $html .= '<a class="navbar-brand" href="/"><img src="/images/logo-trans.png" alt="ForexTrader logo" height="30" /></a>
    </div>'."\n\n";

        if (\user::isLoggedIn()) {
            $html .= '<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
          <li role="presentation"' . (uri == '/' ? ' class="active"' : '') . '><a href="/"><i class="glyphicon glyphicon-th"></i> Dashboard</a></li>
          <li role="presentation"' . (uri == '/chart' ? ' class="active"' : '') . '><a href="/chart"><i class="glyphicon glyphicon-signal"> </i> Charting</a></li>
          <li role="presentation"' . (uri == '/trade-history' ? ' class="active"' : '') . '><a href="/trade-history"><i class="glyphicon glyphicon-calendar"> </i> History</a></li>
          <li role="presentation"' . (uri == '/settings' ? ' class="active"' : '') . '><a href="/settings"><i class="glyphicon glyphicon-cog"> </i> Settings</a></li>
          <li role="presentation"' . (uri == '/logout' ? ' class="active"' : '') . '><a href="/logout"><i class="glyphicon glyphicon-lock"> </i> Logout</a></li>
        </ul>
    </div>'."\n";
        }

        $html .= '</div>
</nav>';

        return $html;
    }

    /**
     * @return string
     */
    protected function getHeader(): string {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <title>' . $this->getTitleTag() . '</title>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <link rel="manifest" href="/manifest.json">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="/css/bootstrap-theme.min.css" type="text/css" />
    <link rel="stylesheet" href="/css/styles.css" type="text/css" />
</head>
<body>
    <div class="container">
        <header class="clearfix">
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
        $html = '<footer class="text-center">';
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