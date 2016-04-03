<?php
require('inc/bootstrap.php');

$page_controller = new controller();
echo $page_controller->doLoadPageModule();