<?php
ob_start();
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>The Myth - Install</title>
</head>
<body>';
if (!file_exists("conf.php") && !isset($_POST['DATABASE'])) {
	echo '<form method="post">
	Database Settings:<br>
	DB_HOST: <input type="text" name="DB_HOST" value="localhost"><br>
	DB_USER: <input type="text" name="DB_USER" value="mysql_user"><br>
	DB_PASS: <input type="text" name="DB_PASS" value="mysql_password"><br>
	DATABASE: <input type="text" name="DATABASE" value="mysql_database"><br>
	<br>
	API Keys:<br>
	RT_API: <input type="text" name="RT_API"><br>
	TMDb_API: <input type="text" name="TMDb_API"><br>
	<br>
	Admin Password: <input type="text" name="PASS" value="YourSecretPassword"><br>
	<input type="submit" value="Install">
	</form>
	NOTE: apache must be able to write to the directory.
	';
} elseif (isset($_POST['DATABASE'])) {
	file_put_contents("conf.php", '<?php
	define("DB_HOST", "'.$_POST['DB_HOST'].'");
	define("DB_USER", "'.$_POST['DB_USER'].'");
	define("DB_PASS", "'.$_POST['DB_PASS'].'");
	define("DATABASE", "'.$_POST['DATABASE'].'");

	setlocale(LC_MONETARY, "en_US");

	define("RT_API", "'.$_POST['RT_API'].'");
	define("TMDb_API", "'.$_POST['TMDb_API'].'");


	define("PASS", "'.$_POST['PASS'].'");
?>
' );
}
if (file_exists("conf.php")) {
	include("conf.php");
	ini_set('display_errors', '0');
	$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);


	if (!$db) {
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db(DATABASE);



	mysql_query ('CREATE TABLE IF NOT EXISTS `cast` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`imdb_number` int(11) DEFAULT NULL,
		`name` varchar(128) DEFAULT NULL,
		`role` varchar(128) DEFAULT NULL,
		`rt_celeb_number` int(11) DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;');

	mysql_query ('CREATE TABLE IF NOT EXISTS `country` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`imdb_number` int(11) DEFAULT NULL,
		`name` varchar(128) DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;');

	mysql_query ('CREATE TABLE IF NOT EXISTS `director` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`imdb_number` int(11) DEFAULT NULL,
		`name` varchar(128) DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;');

	mysql_query ('CREATE TABLE IF NOT EXISTS `files` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`filename` varchar(256) DEFAULT NULL,
		`resolution` varchar(11) DEFAULT NULL,
		`imdb_number` int(11) DEFAULT NULL,
		`tmdb_number` int(11) DEFAULT NULL,
		`rt_number` int(11) DEFAULT NULL,
		`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`subtitle` int(1) NOT NULL DEFAULT \'0\',
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;');

	mysql_query ('CREATE TABLE IF NOT EXISTS `genre` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`imdb_number` int(11) DEFAULT NULL,
		`name` varchar(128) DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;');

	mysql_query ('CREATE TABLE IF NOT EXISTS `info_rt` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`imdb_number` int(11) DEFAULT NULL,
		`title` varchar(128) DEFAULT NULL,
		`mpaa` varchar(11) DEFAULT NULL,
		`runtime` int(11) DEFAULT NULL,
		`release_theater` date DEFAULT NULL,
		`release_dvd` date DEFAULT NULL,
		`consensus` text,
		`rating_critics` int(11) DEFAULT NULL,
		`rating_audience` int(11) DEFAULT NULL,
		`studio` varchar(128) DEFAULT NULL,
		`rt_link` varchar(128) DEFAULT NULL,
		`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`date_updated` timestamp NULL DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;');

	mysql_query ('CREATE TABLE IF NOT EXISTS `info_tmdb` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`imdb_number` int(11) DEFAULT NULL,
		`tagline` tinytext,
		`overview` text,
		`revenue` int(11) DEFAULT NULL,
		`budget` int(11) DEFAULT NULL,
		`date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`date_updated` timestamp NULL DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;');

	mysql_query ('CREATE TABLE IF NOT EXISTS `producer` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`imdb_number` int(11) DEFAULT NULL,
		`name` varchar(128) DEFAULT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;');

	# the DB has been set up, redirect to admin so they can add files
	ob_end_clean();
	header("Location: admin");
	exit;
}
mysql_close($db);
ob_end_flush();
?>
</body>
</html>