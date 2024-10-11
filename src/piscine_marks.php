<?php

require_once(__DIR__ . "/vendor/autoload.php");
require_once(__DIR__ . "/database.php");
require_once(__DIR__ . "/twiggy.php");

if (key_exists("id", $_GET) === false || strlen($_GET["id"]) != 16 || ctype_xdigit($_GET["id"]) === False)
	die("(valid) id please");

$pdo = connect_db();

/* piscine */
$response = $pdo->query("SELECT * FROM piscines WHERE piscine_id='".$_GET["id"]."' LIMIT 1;", PDO::FETCH_NAMED);
if ($response === false)
	die("db err");

$piscine = $response->fetchAll();
$response->closeCursor();
if (count($piscine) == 0)
	die("piscine not found");

/* piscineux */
$response = $pdo->query("SELECT * FROM users WHERE piscine_id='".$_GET["id"]."';", PDO::FETCH_NAMED);
if ($response === false)
	die("db err");

$piscineux = $response->fetchAll();
$response->closeCursor();
if (count($piscine) == 0)
	die("piscineux not found");

/* projects */
$response = $pdo->query("SELECT * FROM projects WHERE piscine_id='".$_GET["id"]."' ORDER BY `marked_at` DESC;", PDO::FETCH_NAMED);
if ($response === false)
	die("db err");

$projects = $response->fetchAll();
$response->closeCursor();

function get_project_mark($login, $project_slug, $projects) {

	foreach ($projects as $project) {
		if ($project["login"] == $login && $project["slug"] == $project_slug) {
			return $project["final_mark"];
		}
	}

	return null;
}

for ($i=0; $i < count($piscineux); $i++) { 
	$piscineux[$i]["projects_marks"] = [
		"shell00" => get_project_mark($piscineux[$i]["login"], "c-piscine-shell-00", $projects)
	];
}

echo twig_render("piscine", [
	"piscine" => $piscine[0],
	"piscineux" => $piscineux,
	"projects" => $projects
]);