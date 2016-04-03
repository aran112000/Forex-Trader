<?php
final class controller {

    private $uri_parts = null;

    public function __construct() {
        $this->setUriParts();
    }

    private function setUriParts() {
        $this->uri_parts = explode('/', trim(uri, '/ '));
    }

    public function doLoadPageModule(): string {
        $page_class = null;
        if (isset($this->uri_parts[0]) && $this->uri_parts[0] !== '') {
            // Module
            if (class_exists($this->uri_parts[0] . '_page')) {
                $page_class = $this->uri_parts[0] . '_page';
            }
        } else {
            $page_class = 'login_page';
        }

        if ($page_class === null) {
            $page_class = 'error_page';
        }

        /**@var _page $class*/
        $class = new $page_class();
        $class->__controller($this->uri_parts);

        return $class->get();
    }
}