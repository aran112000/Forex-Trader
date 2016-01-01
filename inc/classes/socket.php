<?php

/**
 * Class socket
 */
class socket {

    const SERVER = 'http://localhost:8081';

    /**
     * @var ElephantIO\Client|null
     */
    private static $server = null;

    /**
     *
     */
    private static function init() {
        if (self::$server === null) {
            require(root . '/inc/external/elephant.io/src/Client.php');
            require(root . '/inc/external/elephant.io/src/EngineInterface.php');
            require(root . '/inc/external/elephant.io/src/Engine/AbstractSocketIO.php');
            require(root . '/inc/external/elephant.io/src/Engine/SocketIO/Version1X.php');
            require(root . '/inc/external/elephant.io/src/Engine/SocketIO/Session.php');
            require(root . '/inc/external/elephant.io/src/AbstractPayload.php');
            require(root . '/inc/external/elephant.io/src/Payload/Encoder.php');
            require(root . '/inc/external/elephant.io/src/Payload/Decoder.php');

            self::$server = new ElephantIO\Client(new ElephantIO\Engine\SocketIO\Version1X(self::SERVER));
            self::$server->initialize();
        }
    }

    /**
     * @param string $event
     * @param array  $data
     *
     * @return array
     */
    private static function getPayload(string $event, array $data): array {
        return array_merge(['__event' => $event], $data);
    }

    /**
     * @param string $event
     * @param array $data
     */
    public static function send(string $event, array $data) {
        self::init();

        self::$server->emit('appEvent', self::getPayload($event, $data));
    }

    /**
     *
     */
    public static function close() {
        self::$server->close();
        self::$server = null;
    }
}