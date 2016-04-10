<?php
spl_autoload_register(function($class_name) {
    if (is_readable(root . '/inc/' . $class_name . '.php')) {
        require(root . '/inc/' . $class_name . '.php');
    } else if (is_readable(root . '/inc/classes/' . $class_name . '.php')) {
        require(root . '/inc/classes/' . $class_name . '.php');
    } else if (is_readable(root . '/tests/classes/' . $class_name . '.php')) {
        require(root . '/tests/classes/' . $class_name . '.php');
    } else if (is_readable(root . '/inc/classes/static/' . $class_name . '.php')) {
        require(root . '/inc/classes/static/' . $class_name . '.php');
    } else if (is_readable(root . '/inc/classes/pairs/' . $class_name . '.php')) {
        require(root . '/inc/classes/pairs/' . $class_name . '.php');
    } else if (is_readable(root . '/inc/classes/analysis/' . $class_name . '.php')) {
        require(root . '/inc/classes/analysis/' . $class_name . '.php');
    } else if (is_readable(root . '/tests/classes/analysis/' . $class_name . '.php')) {
        require(root . '/tests/classes/analysis/' . $class_name . '.php');
    } else if (is_readable(root . '/tests/classes/' . $class_name . '.php')) {
        require(root . '/tests/classes/' . $class_name . '.php');
    } else if (is_readable(root . '/inc/classes/signals/' . $class_name . '.php')) {
        require(root . '/inc/classes/signals/' . $class_name . '.php');
    } else if (is_readable(root . '/inc/classes/form/' . $class_name . '.php')) {
        require(root . '/inc/classes/form/' . $class_name . '.php');
    } else if (is_readable(root . '/inc/classes/pages/' . $class_name . '.php')) {
        require(root . '/inc/classes/pages/' . $class_name . '.php');
    } else {
        // Namespace
        $file_path = str_replace('\\', DIRECTORY_SEPARATOR, $class_name);
        if (is_readable(root . '/inc/classes/' . $file_path . '.php')) {
            require(root . '/inc/classes/' . $file_path . '.php');
        }
    }
});