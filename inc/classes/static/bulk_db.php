<?php

/**
 * Class bulk_db
 */
class bulk_db extends db {

    /**
     * @var array
     */
    public static $construct_queries = [];
    /**
     * @var array
     * This is fun BEFORE the final core __destruct query is hit, if you need it before ALL queries are run, increase
     * $query_queue_limit_before_auto_process
     */
    public static $destruct_queries = [];
    /**
     * @var array
     * This is run AFTER the final core __destruct query is hit regardless of the outcome of other queries
     */
    public static $pre_queries = [];
    /**
     * @var array
     */
    public static $post_queries = [];
    /**
     * @var bool
     */
    public static $test_mode = false;
    /**
     * @var bool
     */
    public static $allow_update = true;
    /**
     * @var int
     */
    public static $query_queue_limit_before_auto_process = 750;
    /**
     * @var array
     */
    private static $bulk_queries_to_process = [];

    /**
     * @param string $table                      | DB table name
     * @param array  $insert_field_parameters    | These must have the DB column name as the key
     * @param array  $fields_to_ignore_on_update | When the row already exists and we're processing an update, certain
     *                                           fields may want to be bypassed
     */
    public static function add_query($table, array $insert_field_parameters = [], array $fields_to_ignore_on_update = []) {
        $group = self::get_query_hash($table, $insert_field_parameters, $fields_to_ignore_on_update);
        if (!isset(self::$bulk_queries_to_process[$group])) {
            self::$bulk_queries_to_process[$group] = [];
        }
        if (!isset(self::$bulk_queries_to_process[$group][$table])) {
            self::$bulk_queries_to_process[$group][$table] = [];
        } else if (count(self::$bulk_queries_to_process[$group][$table]) >= self::$query_queue_limit_before_auto_process) {
            self::do_process_queries($group, $table);
        }

        self::$bulk_queries_to_process[$group][$table][] = [
            'insert_fields' => $insert_field_parameters,
            'ignore_update_fields' => $fields_to_ignore_on_update,
        ];
    }

    /**
     * Used to create a unique key for the insert queries because multiple queries with different fields may exist and
     * this would error on INSERT
     *
     * @param string $table
     * @param array  $insert_field_parameters
     * @param array  $fields_to_ignore_on_update
     *
     * @return string
     */
    protected static function get_query_hash($table, array $insert_field_parameters, array $fields_to_ignore_on_update) {
        $hash = $table;
        foreach ($insert_field_parameters as $key => $value) {
            $hash .= $key;
        }
        foreach ($fields_to_ignore_on_update as $key => $value) {
            $hash .= 'ignore_' . $key;
        }

        return md5($hash);
    }

    /**
     * Don't call directly outside of either core_extend::__destruct() or this class
     *
     * @param string $group
     * @param string $table
     */
    public static function do_process_queries($group = '', $table = '') {
        foreach (self::$construct_queries as $sql => $parameters) {
            db::query($sql, $parameters);
        }
        if (!empty($group) && !empty($table)) {
            self::do_process_specific_group($group, $table);
        } else {
            foreach (self::$bulk_queries_to_process as $group_name => $tables) {
                foreach ($tables as $table => $queries) {
                    self::do_process_specific_group($group_name, $table);
                }
            }
        }
        foreach (self::$destruct_queries as $sql => $parameters) {
            db::query($sql, $parameters);
        }
    }

    /**
     * @param $group
     * @param $table
     */
    private static function do_process_specific_group($group, $table) {
        if (!empty(self::$bulk_queries_to_process[$group][$table])) {
            $sqls = $params = $keys = [];
            $sql_table = str_replace('.', '`.`', $table);
            $sql = 'INSERT INTO `' . $sql_table . '` ';
            $i = 0;
            foreach (self::$bulk_queries_to_process[$group][$table] as $options) {
                $i++;
                $field_no = 0;
                $insert_fields = '';
                foreach ($options['insert_fields'] as $key => $value) {
                    $field_no++;
                    if ($i == 1) {
                        $keys[] = $key;
                    }
                    if (self::$test_mode) {
                        $insert_fields .= ($field_no > 1 ? ',' : '') . (is_int($value) ? $value : db::esc($value));
                    } else {
                        $param_key = $field_no . '_' . $i;
                        $params[$param_key] = $value;
                        $insert_fields .= ($field_no > 1 ? ',' : '') . ':' . $param_key;
                    }
                }
                if ($i == 1) {
                    $sql .= '(`' . implode('`,`', $keys) . '`) ';
                }
                if (!empty($insert_fields)) {
                    $sqls[] = $insert_fields;
                }
            }

            $sql = $sql . ' VALUES (' . implode('),(', $sqls) . ')';
            if (self::$allow_update) {
                $sql .= ' ON DUPLICATE KEY UPDATE ';
                $fnum = 0;

                $first_query = self::$bulk_queries_to_process[$group][$table][0];

                foreach ($first_query['insert_fields'] as $field => $value) {
                    if (!in_array($field, $first_query['ignore_update_fields'])) {
                        $fnum++;
                        $sql .= ($fnum > 1 ? ',' : '') . '`' . $field . '`=VALUES(`' . $field . '`)';
                    }
                }
            }
            $sql .= ';';
            if (self::$test_mode) {
                echo '<p>' . $sql . '</p>';
            } else {
                if (self::do_pre_process($table)) {
                    if (self::query($sql, $params)) {
                        self::do_post_process($table);
                    } else {
                        trigger_error('Failed to process bulk query. Exiting at this point');
                        die();
                    }
                }
            }
            self::$bulk_queries_to_process[$group][$table] = [];
        }
    }

    /**
     * @param $table
     *
     * @return bool
     */
    public static function do_pre_process($table) {
        if (isset(self::$pre_queries[$table])) {
            foreach (self::$pre_queries[$table] as $sql => $parameters) {
                if (!db::query($sql, $parameters)) {
                    trigger_error('bulk_db pre query: ' . $sql . ' failed to run');

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $table
     *
     * @return bool
     */
    public static function do_post_process($table) {
        if (isset(self::$post_queries[$table])) {
            foreach (self::$post_queries[$table] as $sql => $parameters) {
                if (!db::query($sql, $parameters)) {
                    trigger_error('bulk_db post query: ' . $sql . ' failed to run');

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param       $table
     * @param       $sql
     * @param array $parameters
     */
    public static function do_add_pre_process_query($table, $sql, array $parameters = []) {
        if (!isset(self::$pre_queries[$table])) {
            self::$pre_queries[$table] = [];
        }
        self::$pre_queries[$table][$sql] = $parameters;
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @internal param $table
     */
    public static function do_add_construct_query($sql, array $parameters = []) {
        self::$construct_queries[$sql] = $parameters;
    }

    /**
     * @param       $sql
     * @param array $parameters
     *
     * @internal param $table
     */
    public static function do_add_destruct_query($sql, array $parameters = []) {
        self::$destruct_queries[$sql] = $parameters;
    }

    /**
     * @param       $table
     * @param       $sql
     * @param array $parameters
     */
    public static function do_add_post_process_query($table, $sql, array $parameters = []) {
        if (!isset(self::$pre_queries[$table])) {
            self::$post_queries[$table] = [];
        }
        self::$post_queries[$table][$sql] = $parameters;
    }
}