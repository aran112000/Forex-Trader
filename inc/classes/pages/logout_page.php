<?php
final class logout_page extends _page {

    public function __controller(array $uri_parts) {
        if (session::stop()) {
            header('location: /');
        }
    }

    /**
     * @return string
     */
    protected function getBody(): string { }
}