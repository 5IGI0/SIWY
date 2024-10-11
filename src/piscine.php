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
$response = $pdo->query("SELECT AVG(final_mark) AS average, AVG(retry) as retry, COUNT(*) as pushers, slug FROM projects WHERE piscine_id='".$_GET["id"]."' GROUP BY slug;", PDO::FETCH_NAMED);
if ($response === false)
	die("db err");

$projects = $response->fetchAll();
$response->closeCursor();

$project_validation = [
	"Shell Projects" => [
		"c-piscine-shell-00" => ["name" => "Shell00", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-shell-01" => ["name" => "Shell01", "avg" => 0, "push" => 0, "retry" => 0 ]
	],
	"C Projects" => [
		"c-piscine-c-00" => ["name" => "C00", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-01" => ["name" => "C01", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-02" => ["name" => "C02", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-03" => ["name" => "C03", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-04" => ["name" => "C04", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-05" => ["name" => "C05", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-06" => ["name" => "C06", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-07" => ["name" => "C07", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-08" => ["name" => "C08", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-09" => ["name" => "C09", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-10" => ["name" => "C10", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-11" => ["name" => "C11", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-12" => ["name" => "C12", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-c-13" => ["name" => "C13", "avg" => 0, "push" => 0, "retry" => 0 ]
	],
	"Rush Projects" => [
		"c-piscine-rush-00" => ["name" => "Rush00", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-rush-01" => ["name" => "Rush01", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-rush-02" => ["name" => "Rush02", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-bsq" => ["name" => "BSQ", "avg" => 0, "push" => 0, "retry" => 0 ],
	],
	"C Exams" => [
		"c-piscine-exam-00" => ["name" => "Exam00", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-exam-01" => ["name" => "Exam01", "avg" => 0, "count" => 0],
		"c-piscine-exam-02" => ["name" => "Exam02", "avg" => 0, "push" => 0, "retry" => 0 ],
		"c-piscine-final-exam" => ["name" => "Final Exam", "avg" => 0, "push" => 0, "retry" => 0 ],
	]
];

foreach ($project_validation as $kcat => $cat) {
	foreach ($projects as $project) {
		if (!key_exists($project["slug"], $cat))
			continue;
		$project_validation[$kcat][$project["slug"]]["avg"] = $project["average"];
		$project_validation[$kcat][$project["slug"]]["push"] = intval($project["pushers"])/count($piscineux)*100;
		$project_validation[$kcat][$project["slug"]]["retry"] = $project["retry"];
	}
}

echo twig_render("piscine", [
	"piscine" => $piscine[0],
	"piscineux" => $piscineux,
	"pushes" => $project_validation
]);