<?php

require_once(__DIR__ . "/vendor/autoload.php");

function twig_render($renderer_name, $ctx) {
	$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . "/templates/");
	$twig = new \Twig\Environment($loader);//, [
		//'cache' => '/path/to/compilation_cache',
	//]);
	if (key_exists("renderer", $_GET) && $_GET["renderer"] == "json") {
		header("Content-Type: application/json");
		return json_encode($ctx);
	} else
		$tmp = $twig->load($renderer_name.".html");
	return $tmp->render($ctx);
}