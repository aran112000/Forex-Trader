<?php

/**
 * Class reversals
 */
class reversals extends _base_analysis {

    const REQUIRED_CONFLUENCE = 2;

    /**
     * @var int
     */
    protected $data_fetch_size = 250;

    /**
     * @var bool
     */
    protected $enabled = true;

    private $signals = [
        'long' => [
            'low_test',
            'doji',
            //'inside_bar',  // TODO
            //'tweezer_bottoms', // TODO
        ],
        'short' => [
            'high_test',
            'doji',
            //'inside_bar', // TODO
            //'tweezer_tops', // TODO
        ]
    ];

    /**
     * @param \_pair $currency_pair
     */
    public function setPair(_pair $currency_pair) {
        parent::setPair($currency_pair);

        $this->currency_pair->data_fetch_time = '1d';
    }

    /**
     * @return bool
     */
    protected function isLongEntry(): bool {
        if ($this->getChoppinessIndex() < 60) {
            if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {

                $data = $this->getData();

                $confluence_factors = 0;
                foreach ($this->signals['long'] as $signal) {
                    /**@var _signal $signal */
                    if ($signal::isValidSignal($data)) {
                        $confluence_factors++;
                    }
                }

                if ($confluence_factors >= self::REQUIRED_CONFLUENCE) {
                    echo '<p>' . $data[0]->pair->getPairName() . ' - Confluence: ' . $confluence_factors . '</p>' . "\n";

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isShortEntry() {
        if ($this->getChoppinessIndex() < 60) {
            if ($this->getAtrDirection() === 'down' || $this->getAtrDirection() === 'sideways') {

                $data = $this->getData();

                $confluence_factors = 0;
                foreach ($this->signals['short'] as $signal) {
                    /**@var _signal $signal */
                    if ($signal::isValidSignal($data)) {
                        $confluence_factors++;
                    }
                }

                if ($confluence_factors >= self::REQUIRED_CONFLUENCE) {
                    echo '<p>' . $data[0]->pair->getPairName() . ' - Confluence: ' . $confluence_factors . '</p>' . "\n";

                    return true;
                }
            }
        }

        return false;
    }
}