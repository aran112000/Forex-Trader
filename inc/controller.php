<?php

/**
 * Class controller
 */
final class controller {

    private $uri_parts = null;

    /**
     * controller constructor.
     */
    public function __construct() {
        if (ajax) {
            $module = $_REQUEST['module'];
            $method = $_REQUEST['action'];

            $class = new $module();
            $class->{$method}();

            ajax::doServe();
        }

        $this->setUriParts();
    }

    /**
     *
     */
    private function setUriParts() {
        $uri_parts = explode('?', uri, 2); // Remove any query string from the URI
        $this->uri_parts = explode('/', trim(str_replace('-', '_', $uri_parts[0]), '/ '));
    }

    /**
     * @return string
     */
    public function doLoadPageModule(): string {
        $page_class = null;
        if (isset($this->uri_parts[0]) && $this->uri_parts[0] !== '') {
            // Module
            if (class_exists('page\\' . $this->uri_parts[0])) {
                $page_class = 'page\\' . $this->uri_parts[0];
            }
        } else {
            if (user::isLoggedIn()) {
                $page_class = 'page\\dashboard';
            } else {
                $page_class = 'page\\login';
            }
        }

        if ($page_class === null) {
            $page_class = 'page\\error';
        }

        /**@var page\_page $class*/
        $class = new $page_class();
        $class->__controller($this->uri_parts);

        return $class->get();
    }
}