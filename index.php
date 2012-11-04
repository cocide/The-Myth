<?php
ob_start();
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<script type="text/javascript" src="js/jquery.js"></script> 
	<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
	<script type="text/javascript">
		$(function() {
			$("#Movies").tablesorter();
	});	
	</script>	
	<script type="text/javascript">
		$(document).ready(function() {
			$("tr").children("td.studio, td.cost, td.producer, td.consensus, td.files, td.cast, td.overview").css({"display" : "block"});
			$("tr").children("td.studio, td.cost, td.producer, td.consensus, td.files, td.cast, td.overview").hide();
			
			$("td.title").click(function () { 
				$(this).parent("tr").children("td.detail").toggle();
				$(this).parent("tr").children("td.smallTagline").toggle();
			});
		});
	</script>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>The Myth</title>
</head>
<body>';

if (!file_exists("conf.php")) {
	ob_end_clean();
	header("Location: install.php");
	exit;
}
ob_end_flush();
include("conf.php");
ini_set('display_errors', '0');
$db = mysql_connect(DB_HOST, DB_USER, DB_PASS);


if (!$db) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db(DATABASE);


$red = "9D1E15";
$green = "61CE3C";
$ColorSteps = "100";

#make the color gradient
$FromRGB['r'] = hexdec(substr($red, 0, 2));
$FromRGB['g'] = hexdec(substr($red, 2, 2));
$FromRGB['b'] = hexdec(substr($red, 4, 2));

$ToRGB['r'] = hexdec(substr($green, 0, 2));
$ToRGB['g'] = hexdec(substr($green, 2, 2));
$ToRGB['b'] = hexdec(substr($green, 4, 2));

$StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps);
$StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps);
$StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps);

$GradientColors['color'] = array();

for($i = 0; $i <= $ColorSteps; $i++)
{
	$RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
	$RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
	$RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));
	
	$HexRGB['color']['r'] = sprintf('%02x', ($RGB['r']));
	$HexRGB['color']['g'] = sprintf('%02x', ($RGB['g']));
	$HexRGB['color']['b'] = sprintf('%02x', ($RGB['b']));
	
	$GradientColors['color'][] = implode(NULL, $HexRGB['color']);
}



$dark = "111111";
$light = "999999";

#make the color gradient
$FromRGB['r'] = hexdec(substr($dark, 0, 2));
$FromRGB['g'] = hexdec(substr($dark, 2, 2));
$FromRGB['b'] = hexdec(substr($dark, 4, 2));

$ToRGB['r'] = hexdec(substr($light, 0, 2));
$ToRGB['g'] = hexdec(substr($light, 2, 2));
$ToRGB['b'] = hexdec(substr($light, 4, 2));

$StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps);
$StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps);
$StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps);

$GradientColors['mono'] = array();

for($i = 0; $i <= $ColorSteps; $i++)
{
	$RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
	$RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
	$RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));
	
	$HexRGB['mono']['r'] = sprintf('%02x', ($RGB['r']));
	$HexRGB['mono']['g'] = sprintf('%02x', ($RGB['g']));
	$HexRGB['mono']['b'] = sprintf('%02x', ($RGB['b']));
	
	$GradientColors['mono'][] = implode(NULL, $HexRGB['mono']);
}

$query = "SELECT * FROM files";
$files = mysql_query($query);
while ($row = mysql_fetch_array($files, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['files'][] = $row;
}

$query = "SELECT * FROM info_rt";
$info_rt = mysql_query($query);
while ($row = mysql_fetch_array($info_rt, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['info_rt'] = $row;
}

$query = "SELECT * FROM info_tmdb";
$info_tmdb = mysql_query($query);
while ($row = mysql_fetch_array($info_tmdb, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['info_tmdb'] = $row;
}

$query = "SELECT * FROM cast";
$cast = mysql_query($query);
while ($row = mysql_fetch_array($cast, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['cast'][] = $row;
}

$query = "SELECT * FROM director";
$director = mysql_query($query);
while ($row = mysql_fetch_array($director, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['director'][] = $row;
}

$query = "SELECT * FROM genre";
$genre = mysql_query($query);
while ($row = mysql_fetch_array($genre, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['genre'][] = $row;
}

$query = "SELECT * FROM studio";
$studio = mysql_query($query);
while ($row = mysql_fetch_array($studio, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['studio'] = $row;
}

$query = "SELECT * FROM producer";
$producer = mysql_query($query);
while ($row = mysql_fetch_array($producer, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['producer'][] = $row;
}

$query = "SELECT * FROM country";
$country = mysql_query($query);
while ($row = mysql_fetch_array($country, MYSQL_ASSOC)) {
	$movie_data[$row['imdb_number']]['country'][] = $row;
}

mysql_close($db);

#make table headers
echo "\n\t<table id=\"Movies\" border=\"2\">\n";
echo "\t<thead>\n";
echo "\t<tr>\n";
echo "\t\t<th class=\"year\">Year</th>\n";
echo "\t\t<th class=\"popularity\">RT</th>\n";
echo "\t\t<th class=\"popularity\">USR</th>\n";
echo "\t\t<th class=\"title\">Title</th>\n";
echo "\t\t<th class=\"rating\">Rating</th>\n";
echo "\t\t<th class=\"smallTagline\">Tagline</th>\n";
echo "\t\t<th class=\"genre\">Genre</th>\n";
echo "\t\t<th class=\"director\">Director</th>\n";
echo "\t\t<th class=\"detail studio\">Studio</th>\n";
echo "\t\t<th class=\"detail producer\">Producer</th>\n";
echo "\t\t<th class=\"detail consensus\">Consensus</th>\n";
echo "\t\t<th class=\"detail time\">Runtime</th>\n";
echo "\t\t<th class=\"detail imdb\">IMDB Number</th>\n";
echo "\t\t<th class=\"detail tmdb\">RT Number</th>\n";
echo "\t\t<th class=\"detail tmdb\">TMDB Number</th>\n";
echo "\t\t<th class=\"detail tagline\">Tagline</th>\n";
echo "\t\t<th class=\"detail overview\">Overview</th>\n";
echo "\t\t<th class=\"detail cast\">Cast</th>\n";
echo "\t\t<th class=\"detail files\">Files</th>\n";
echo "\t</tr>\n";
echo "\t</thead>\n";
echo "\t<tbody>\n";

foreach ($movie_data as $imdb_number => $data) {
	if (!is_null($data['info_rt']['title'])) {

		

			echo "\t<tr>\n";
			echo "\t\t<td class=\"year\">".substr($data['info_rt']['release_theater'], 0, 4)."</td>\n";
			echo "\t\t<td class=\"popularity\"><span style=\"color:#".$GradientColors['color'][$data['info_rt']['rating_critics']]."\">".$data['info_rt']['rating_critics']."</span></td>\n";
			echo "\t\t<td class=\"popularity\"><span style=\"color:#".$GradientColors['mono'][$data['info_rt']['rating_audience']]."\">".$data['info_rt']['rating_audience']."</span></td>\n";
			echo "\t\t<td class=\"title\">".str_replace("&", "&#38;", $data['info_rt']['title'])."</td>\n";
			echo "\t\t<td class=\"rating\">";
			if (!is_null($data['info_rt']['mpaa'])) {
				echo "(".$data['info_rt']['mpaa'].")";
			}
			echo "</td>\n";
			echo "\t\t<td class=\"smallTagline\">".str_replace("&", "&#38;", $data['info_tmdb']['tagline'])."</td>\n";
			echo "\t\t<td class=\"genre\">";
			$previous = false;
			foreach ($data['genre'] as $genre) {
				if ($previous) {
					echo ", ";
				} else {
					$previous = true;
				}
				echo $genre['name'];
			}
			echo "</td>\n";
			echo "\t\t<td class=\"director\">";
			$previous = false;
			foreach ($data['director'] as $director) {
				if ($previous) {
					echo ", ";
				} else {
					$previous = true;
				}
				echo $director['name'];
			}
			echo "</td>\n";
			echo "\t\t<td class=\"detail studio\">".$data['info_rt']['studio']."</td>\n";
			echo "\t\t<td class=\"detail producer\">";
			$previous = false;
			foreach ($data['producer'] as $producer) {
				if ($previous) {
					echo ", ";
				} else {
					$previous = true;
				}
				echo $producer['name'];
			}
			echo "</td>\n";
			echo "\t\t<td class=\"detail consensus\">".$data['info_rt']['consensus']."</td>\n";
			echo "\t\t<td class=\"detail time\">".$data['info_rt']['runtime']." min</td>\n";
			echo "\t\t<td class=\"detail imdb\"><a target=\"_blank\" href=\"http://www.imdb.com/title/tt".str_pad($data['files'][0]['imdb_number'], 7, "0", STR_PAD_LEFT)."/\">IMDb</a></td>\n";
			echo "\t\t<td class=\"detail rt\"><a target=\"_blank\" href=\"".$data['info_rt']['rt_link']."\">RT</a></td>\n";
			echo "\t\t<td class=\"detail tmdb\"><a target=\"_blank\" href=\"http://www.themoviedb.org/movie/".$data['files'][0]['tmdb_number']."\">TMDb</a></td>\n";
			echo "\t\t<td class=\"detail cost\">Budget: ".money_format("%.0n", $data['info_tmdb']['budget'])."<br>Box: ".money_format("%.0n", $data['info_tmdb']['revenue'])."</td>\n";
			echo "\t\t<td class=\"detail files\"><ul>";
			$previous = false;
			foreach ($data['files'] as $file) {
				if ($previous) {
					echo "<br>";
				} else {
					$previous = true;
				}
				echo "<li><span class=\"icon\">".$file['resolution']."</span>".str_replace("&", "&#38;", $file['filename'])."</li>";
			}
			echo "</ul></td>\n";
			echo "\t\t<td class=\"detail tagline\">".str_replace("&", "&#38;", $data['info_tmdb']['tagline'])."</td>\n";
			echo "\t\t<td class=\"detail overview\">".str_replace("&", "&#38;", $data['info_tmdb']['overview'])."</td>\n";
			echo "\t\t<td class=\"detail cast\"><ul>";
			foreach ($data['cast'] as $cast) {
				echo "<li><a target=\"_blank\" href=\"http://www.rottentomatoes.com/celebrity/".$cast['rt_celeb_number']."/\">".$cast['name']." (".$cast['role'].")</a></li>";
			}
			echo "</ul></td>\n";
			echo "\t\t<td class=\"detail country\"><ul>";
			foreach ($data['country'] as $country) {
				echo "<li>".$country['name']."</li>";
			}
			echo "</ul></td>\n";
			echo "\t</tr>\n";
		}

	}
	echo "</tbody>\n";
	echo "</table>\n";
	
	
?>
</body>
</html>
