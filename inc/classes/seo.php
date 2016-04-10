<?php

/**
 * Class seo
 */
trait seo {

    /**
     * @var string
     */
    private $base_title_tag = 'ForexTrader';
    /**
     * @var string
     */
    private $title_tag_seperator = '|';

    /**
     * @var null
     */
    private $title_tag = null;

    /**
     * @param string $title_tag
     */
    protected function setTitleTag(string $title_tag) {
        $this->title_tag = trim($title_tag);
    }

    /**
     * @return string
     */
    protected function getTitleTag(): string {
        if ($this->title_tag !== null) {
            return $this->title_tag . ' ' . $this->title_tag_seperator . ' ' . $this->base_title_tag;
        }

        return $this->base_title_tag;
    }
}