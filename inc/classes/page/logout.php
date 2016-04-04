<?php

namespace page;

/**
 * Class logout
 * @package page
 */
class logout extends _page {

    /**
     * @param array $uri_parts
     */
    public function __controller(array $uri_parts) {
        if (\session::stop()) {
            header('location: /');
        }
    }

    /**
     * @return string
     */
    protected function getBody(): string { }
}