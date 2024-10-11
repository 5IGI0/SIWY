<?php

require_once(__DIR__ . "/vendor/autoload.php");
require_once(__DIR__ . "/database.php");
require_once(__DIR__ . "/twiggy.php");

$pdo = connect_db();
$response = $pdo->query("SELECT * FROM piscines ORDER BY `year` DESC, `month` DESC, `end_at` DESC;", PDO::FETCH_NAMED);

if ($response === false)
	die("mdr");

echo twig_render("piscine_list", ["piscines" => $response->fetchAll()]);