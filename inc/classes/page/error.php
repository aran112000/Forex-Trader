<?php

namespace page;

/**
 * Class error
 */
class error extends _page {

    /**
     * @return string
     */
    function getBody(): string {
        return '<h1>404 - Page not found</h1>
            <p>Sorry, the page you\'ve requested can\'t be found. Please check the URL and try again.</p>';
    }
}