CREATE TABLE `piscines` (
	`piscine_id` char(16),
	`campus` TEXT,
	`year` SMALLINT,
	`month` TINYINT,
	`begin_at` INT UNSIGNED,
	`end_at` INT UNSIGNED,
	PRIMARY KEY(`piscine_id`)
);

CREATE TABLE `users` (
	`piscine_id` char(16),
	`login` varchar(15) UNIQUE,
	`user_id` INT UNSIGNED UNIQUE,
	`correction_point` INT,
	`update_from` INT UNSIGNED default 0,
	`updated_at` INT UNSIGNED,
	`update_until` INT UNSIGNED
);

CREATE TABLE `projects` (
	`piscine_id` char(16),
	`login` TEXT,
	`user_id` INT UNSIGNED,
	`retry` INT UNSIGNED,
	`final_mark` INT,
	`marked_at` INT UNSIGNED,
	`slug` TEXT,
	`name` TEXT
);