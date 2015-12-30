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
    } else if (is_readable(root . '/tests/analysis/' . $class_name . '.php')) {
        require(root . '/tests/analysis/' . $class_name . '.php');
    } else if (is_readable(root . '/inc/charting/' . $class_name . '.php')) {
        require(root . '/inc/charting/' . $class_name . '.php');
    }
});