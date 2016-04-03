<?php

/**
 * Class db
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class db {

    static public $usr = [];
    static public $global_var = 'db';
    static public $db_name;

    static private $connection;
    static private $has_table_cache = [];

    /**
     *
     */
    public static function connect() {
        $username = 'root';
        $password = 'Issj598*';

        if (live) {
            $database = 'trader';
            $server = 'trader.c23njnxbttms.eu-west-1.rds.amazonaws.com';
        } else {
            $database = 'forex';
            $server = 'localhost';
        }
        $port = 3306;
        $die_on_fail = true;

        $global_var = 'db';
        static::$global_var = $global_var;

        static::$usr[static::$global_var] = $username; // Used for detecting CMS login
        static::$db_name = $database;

        try {
            self::$connection = new PDO('mysql:host=' . $server . ';port=' . $port . ';dbname=' . $database, $username, $password, [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::FETCH_PROPS_LATE => true,
            ]);
        } catch (PDOException $e) {
            if ($die_on_fail) {
                trigger_error('<p>Site failed to connect to master database, please try again shortly.</p><p>' . $e->getMessage() . '</p>');
                die();
            } else {
                throw($e);
            }
        }
    }

    /**
     *
     */
    public static function disable_triggers() {
        static::query('SET @TRIGGER_CHECKS = false');
    }

    /**
     * @param        $sql
     * @param array  $fields
     *
     * @return bool|PDOStatement
     */
    public static function query($sql, array $fields = []) {
        if (!is_object(self::$connection)) {
            // Attempt to connect / re-connect
            self::connect();
            if (!is_object(self::$connection)) {
                trigger_error('Selected DB is not instantiated. <br/> QUERY: ' . self::get_query($sql), E_USER_ERROR);

                return false;
            }
        }
        $fields_original = $fields;
        if (count($fields) > 0) {
            /** @var PDOStatement $statement */
            $statement = self::$connection->prepare($sql);
            foreach ($fields as $key => $val) {
                $key = self::prep($key);
                unset($fields[$key]);
                if (is_float($val)) {
                    $val = get::int_float_from_string($val);
                }
                if (is_int($val)) {
                    $statement->bindValue(':' . $key, $val, PDO::PARAM_INT);
                } else if (is_bool($val)) {
                    $statement->bindValue(':' . $key, $val ? 1 : 0, PDO::PARAM_INT);
                } else if (is_null($val)) {
                    $statement->bindValue(':' . $key, $val, PDO::PARAM_NULL);
                } else {
                    $statement->bindValue(':' . $key, $val);
                }
            }
            try {
                $exe = $statement->execute();
                if ($exe) {
                    return $statement;
                } else {
                    trigger_error('Database execute error: ' . self::get_query($sql, $fields_original));
                }
            } catch (PDOException $e) {
                trigger_error($e);

                return static::catch_query_error($e, func_get_args(), $sql, $fields_original);
            }
        } else {
            try {
                $statement = self::$connection->query($sql);
                if ($statement) {
                    return $statement;
                }
            } catch (PDOException $e) {
                trigger_error($e);

                return static::catch_query_error($e, func_get_args(), $sql, $fields_original);
            }
        }

        return false;
    }

    /**
     * @param       $sql
     * @param array $params
     *
     * @return mixed
     */
    public static function get_query($sql, array $params = []) {
        $keys = [];
        $values = [];
        // sort params into length order so :title won't replace :titlelonger
        $allkeys = array_keys($params);
        $array_keys = array_map('strlen', array_keys($params));
        array_multisort($array_keys, SORT_DESC, $params);
        $sorted_params = [];
        foreach ($allkeys as $key) {
            $sorted_params[$key] = $params[$key];
        }

        # build a regular expression for each parameter
        foreach ($sorted_params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . str_replace('/', '\\/', $key) . '/';
            } else {
                $keys[] = '/[?]/';
            }
            if (is_numeric($value)) {
                if (((int) $value) == $value) {
                    $values[] = intval($value);
                } else {
                    $values[] = floatval($value);
                }
            } else {
                $values[] = '\'' . self::esc($value) . '\'';
            }
        }

        $sql = preg_replace($keys, $values, $sql, -1, $count);

        return $sql;
    }

    /**
     * @param $str
     *
     * @return string
     */
    public static function esc($str) {
        if (isset($GLOBALS['mysqli'])) {
            return $GLOBALS['mysqli']->real_escape_string($str);
        }

        return addcslashes($str, "\\\000\n\r'\"\032%_");
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    public static function prep($string) {
        $string = str_replace([
            ':',
            '-',
            '!',
            '.'
        ], [
            '',
            '_',
            '',
            '_'
        ], $string);

        return $string;
    }

    /**
     * @param Exception $e
     * @param           $args
     * @param           $sql
     * @param array     $fields_original
     *
     * @internal param $
     * @return bool|PDOStatement
     */
    protected static function catch_query_error(Exception $e, $args, $sql, $fields_original = []) {
        $err_msg = $e->getMessage();
        if ($err_msg == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away') {
            if ($restore_attempt = db::do_restore_connection($args, 10, get::last_method())) {
                return $restore_attempt;
            }
        }
        trigger_error('Database error: ' . $err_msg . '<p><strong>' . self::get_query($sql, $fields_original) . '</strong></p>');
        if (defined('dev') && dev) {
            echo '<h2>Error running search query</h2>' . "\n";
            echo '<p>' . $e->getMessage() . '</p>' . "\n";
            $called_from = debug_backtrace();
            $cnt = 0;
            while ($cnt < 10 && isset($called_from[$cnt])) {
                echo '<p>Query run from: ' . $called_from[$cnt]['file'] . '(' . $called_from[$cnt]['line'] . ')</p>' . "\n";
                $cnt++;
            }
            echo '<p>Full Query:</p>' . "\n";
            echo str_replace("\t", '', self::get_query($sql, $fields_original));
        }

        return false;
    }

    /**
     * @param      $args
     * @param int  $wait_timeout_seconds
     * @param null $method
     *
     * @return mixed
     */
    public static function do_restore_connection($args, $wait_timeout_seconds = 10, $method = null) {
        $calling_method = $method ? : get::last_method();
        $global_var = (isset($args['global_var']) ? $args['global_var'] : 'db');

        // Completely close last connection
        if (isset(self::$connection)) {
            self::$connection = null;
        }
        if (isset($GLOBALS[$global_var]['slave'])) {
            $GLOBALS[$global_var]['slave'] = null;
        }
        static::$global_var = null;
        $GLOBALS[$global_var] = null;

        // Re-call the previous method to re-attempt the last query (after waiting a small amount of time for the connection to be present again)
        if ($wait_timeout_seconds > 0) {
            sleep($wait_timeout_seconds);
        }

        trigger_error('Connection closed... Attempting to re-execute query');

        $db_class = new ReflectionClass('db');

        return $db_class->getMethod($calling_method)->invokeArgs(null, $args);
    }

    /**
     *
     */
    public static function enable_triggers() {
        static::query('SET @TRIGGER_CHECKS = true');
    }

    /**
     * @param array $array
     * @param       $name
     *
     * @return array
     */
    public static function get_in_array(array $array, $name) {
        $new_array = [];
        if (count($array) > 0) {
            foreach ($array as $key => $value) {
                $new_array[$name . '_' . $key] = $value;
            }
        }

        return $new_array;
    }

    /**
     * @param array $array
     * @param       $name
     *
     * @return string
     */
    public static function get_in_sql(array $array, $name) {
        $in = '';
        if (count($array) > 0) {
            foreach ($array as $key => $value) {
                $in .= ':' . $name . '_' . $key . ', ';
            }
            $in = rtrim($in, ', ');
        }

        return $in;
    }

    /**
     * @param PDO $connection
     *
     * @return array|bool
     */
    public static function get_error_details(PDO $connection) {
        if ($err = $connection->errorInfo()) {
            return [
                'sql_state_error_code' => $err[0],
                'error_code' => $err[1],
                'error_msg' => $err[2],
            ];
        }

        return false;
    }

    /**
     * @param string $global_var
     *
     * @return int
     */
    public static function insert_id($global_var = 'db') {
        return (int) self::$connection->lastInsertId();
    }

    /**
     *
     * Use this when processing multiple queries if you want to rollback should any single query fail for any reason
     * Will return true on success or throw exception on error
     *
     * @param (array) $queries
     *
     * Example:
     * $queries = array (
     *     'sql' => 'UPDATE prod SET deleted = 0' => 'param' => array('@param'),
     *      'sql' => 'UPDATE cart SET deleted = 0' => 'param' => array('@param'),
     *      'sql' => 'UPDATE orders SET deleted = 0' => 'param' => array('@param'),
     *      ...
     *      );
     *
     * @param array $queries
     *
     * @return bool
     * @throws Exception
     */
    public static function transaction(array $queries) {
        if (!empty($queries)) {
            self::begin_transaction(); // Turn off autocommit
            foreach ($queries as $query) {
                if (empty($query['params'])) {
                    $query['params'] = [];
                }
                $res = self::query($query['sql'], $query['params']);

                if (!$res) {
                    // Error detected whilst trying to process a query
                    self::rollback();
                    trigger_error('Error processing query: <em>' . $query['sql'] . '</em> - Beginning rollback');
                    break;
                }
            }

            // No errors detected - Commit changes
            self::commit();

            return true;
        } else {
            trigger_error('Please supply a valid array of queries to: ' . __CLASS__ . '::' . __METHOD__ . ' in ' . __FILE__ . '(' . __LINE__ . ')');
        }
    }

    /**
     * @param $global_var
     */
    public static function begin_transaction() {
        self::$connection->beginTransaction();
    }

    /**
     * @param $global_var
     */
    public static function rollback() {
        self::$connection->rollBack();
    }

    /**
     * @param $global_var
     */
    public static function commit() {
        self::$connection->commit();
    }

    /**
     * @param PDOStatement $res
     * @param bool         $object
     *
     * @return mixed
     */
    public static function fetch_all(&$res, $object = false) {
        $return = $res->fetchAll(($object ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC));
        $res->closeCursor();

        return $return;
    }

    /**
     * @param PDOStatement $res
     * @param              $class_name
     *
     * @return mixed
     */
    public static function fetch_classes(&$res, $class_name) {
        $return = $res->fetchAll(PDO::FETCH_CLASS, $class_name);
        $res->closeCursor();

        return $return;
    }

    /**
     * @param string $table_name
     *
     * @return bool|int
     */
    public static function get_current_auto_increment_value($table_name) {
        if ($tres = db::query('SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=:db AND TABLE_NAME=:table', [
            'table' => $table_name,
            'db' => self::$db_name
        ])
        ) {
            if (db::num($tres) > 0) {
                return (int) db::result($tres);
            }
        }

        return false;
    }

    /**
     * @param PDOStatement $statement
     *
     * @return int
     */
    public static function num($statement) {
        if ($statement) {
            if (is_array($statement)) {
                return count($statement);
            }

            /**@param PDOStatement $statement */

            return $statement->rowCount();
        }

        return 0;
    }

    /**
     * @param PDOStatement $statement
     * @param string       $field
     *
     * @return bool|mixed|null
     */
    public static function result($statement, $field = '') {
        if ($statement) {
            if (is_array($statement)) {
                $item = array_shift($statement);
                if (!is_array($item)) {
                    return false;
                }

                return array_shift($item);
            }
            if (db::num($statement) > 0) {
                $row = $statement->fetch();
                if (!empty($row)) {
                    reset($row);
                    if (isset($row[$field])) {
                        return $row[$field];
                    } else {
                        return $row[key($row)];
                    }
                } else {
                    return null;
                }
            }
        }

        return false;
    }

    /**
     * @param        $table
     * @param        $idname
     * @param        $id
     * @param string $title_name
     *
     * @return bool|mixed|null
     */
    public static function get_title($table, $idname, $id, $title_name = 'title') {
        $res = self::query('SELECT `' . $title_name . '` FROM `' . $table . '` WHERE `' . $idname . '` = :id', ['id' => $id]);

        return ($res ? self::result($res) : false);
    }

    /**
     * @param string $global_var
     */
    public static function close($global_var = 'db') {
        if (isset(self::$connection)) {
            self::$connection = null;
        }
        if (isset($GLOBALS[$global_var]['slave'])) {
            $GLOBALS[$global_var]['slave'] = null;
        }
    }

    /**
     * @return mixed
     */
    public static function error() {
        return $GLOBALS['db']['master']->error;
    }

    /**
     * @param      $table
     * @param      $field
     * @param bool $db
     *
     * @return bool
     */
    public static function has_column($table, $field, $db = false) {
        $sql_table = ($db ? $db . '.' : '') . $table;
        $dbres = self::query('SHOW COLUMNS FROM ' . static::alias($sql_table, false) . ' LIKE \'' . db::esc($field) . '\'', [], [
            'mysql_table_dependencies' => [
                'cms_module',
                $sql_table
            ]
        ]); // Doesn't work with parameterized queries
        return ($dbres && db::num($dbres) == 1);
    }

    /**
     * @param      $table
     * @param null $alias
     *
     * @return string
     */
    public static function alias($table, $alias = null) {
        if (strpos($table, '.') !== false) {
            return $table;
        }
        $aliases = get::conf('mysql', 'aliases', []);
        if (isset($aliases[$table])) {
            $res = '`' . $aliases[$table] . '`.`' . $table . '`';
            $alias = $alias ? $alias : ($alias !== false ? $table : null);
        } else {
            $res = '`' . $table . '`';
        }
        if ($alias) {
            $res .= ' `' . $alias . '`';
        }

        return $res;
    }

    /**
     * @param string $table
     *
     * @return bool
     */
    public static function has_table($table) {
        if (isset(self::$has_table_cache[$table])) {
            return self::$has_table_cache[$table];
        }

        $dbres = db::query('SHOW TABLES LIKE \'' . $table . '\''); // Doesn't work with parametrized queries
        self::$has_table_cache[$table] = ($dbres && db::num($dbres) == 1);

        return self::$has_table_cache[$table];
    }

    /**
     * @param PDOStatement $result_set
     * @param string       $type
     * @param callable     $function
     */
    public static function iterate($result_set, $type, $function) {
        $count = 0;
        while ($row = ($type != 'array' ? db::fetch_class($result_set, $type) : db::fetch($result_set, false))) {
            call_user_func_array($function, [
                $row,
                $count++
            ]);
        }
    }

    /**
     * @param PDOStatement $res
     * @param              $class_name
     * @param array        $args
     *
     * @return mixed
     */
    public static function fetch_class(&$res, $class_name, $args = []) {
        return $res->fetchObject($class_name, $args);
    }

    /**
     * @param PDOStatement $res
     * @param bool         $object
     *
     * @return bool|mixed
     */
    public static function fetch(&$res, $object = false) {
        if (is_array($res)) {
            $item = array_shift($res);

            return ($item === null) ? false : $item;
        }
        $row = $res->fetch(($object ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC) | PDO::FETCH_PROPS_LATE);

        return ($row ? $row : false);
    }

    /**
     * @return bool|mixed|null
     */
    public static function get_current_db_name() {
        if ($res = db::query('SELECT DATABASE()')) {
            return db::result($res);
        }

        return false;
    }
}

/**
 * Class array_iterator
 */
class array_iterator extends ArrayIterator {

    /**
     * @param bool $object
     * @param string $class
     *
     * @return array
     */
    public function fetchAll($object = false, $class = '') {
        if ($object == PDO::FETCH_CLASS && $class && class_exists($class)) {
            $this->rewind();
            $rows = [];
            if (!empty($class)) {
                while ($row = $this->current()) {
                    /** @var table $obj */
                    $obj = new $class();
                    $obj->set_from_sql_row($row);
                    $rows[] = $obj;
                    $this->next();
                }

                return $rows;
            }
        } else if ($object == PDO::FETCH_OBJ) {
            $this->rewind();
            $rows = [];
            while ($row = $this->current()) {
                $rows[] = (object) $row;
                $this->next();
            }

            return $rows;
        } else {
            return $this->getArrayCopy();
        }
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    public function fetchObject($class = '') {
        $row = $this->current();
        if ($row) {
            $this->next();
            /** @var table $obj */
            $obj = new $class();
            $obj->set_from_sql_row($row);

            return $obj;
        }

        return false;
    }

    /**
     * @return $this
     */
    public function closeCursor() {
        return $this;
    }

    /**
     * @param bool $object
     *
     * @return mixed|object
     */
    public function fetch($object = false) {
        $row = $this->current();
        $this->next();
        if (!isset($row)) {
            return null;
        }
        if ($object == (PDO::FETCH_OBJ | PDO::FETCH_PROPS_LATE)) {
            return (object) $row;
        } else {
            return $row;
        }
    }

    /**
     * @return int
     */
    public function rowCount() {
        return $this->count();
    }
}