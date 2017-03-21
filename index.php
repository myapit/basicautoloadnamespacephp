<?php
require_once "app/bootstart.php";

$app = new Libraries\Application();
echo "<h1>".$app->app_name."</h1>";
$sample = new Models\SampleModel();

$sample->connect();