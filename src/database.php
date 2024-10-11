<?php

function connect_db() {
	return new PDO("mysql:dbname=test;localhost", "test", "test");
}