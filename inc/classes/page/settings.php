<?php

namespace page;

/**
 * Class settings
 */
class settings extends _page {

    /**
     * @return string
     */
    function getBody(): string {
        $settings = new \settings_form();

        return $settings->getHtml();
    }
}