<?php
/**
 * Intended for command line usage for live development. Loads player data into the hawkhost database. Example syntax for world 70:
 *
 * $ php db_load_players.php 70
 */

// Tracks execution time
$start_time = microtime(true);

session_start();

// Connects to the database
$mysqli = new mysqli("localhost", "twplanco", "@Uo54KjjtsLgxMx3NK2gd&!C", "twplanco_analytics");

// Checks for connection error
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}
else {
  printf("Connected to database... \n");
}

$worlds = array (
  1=>array("world"=>19,"lastUpdated"=>""),
  2=>array("world"=>30,"lastUpdated"=>""),
  3=>array("world"=>38,"lastUpdated"=>""),
  4=>array("world"=>42,"lastUpdated"=>""),
  5=>array("world"=>46,"lastUpdated"=>""),
  6=>array("world"=>48,"lastUpdated"=>""),
  10=>array("world"=>56,"lastUpdated"=>""),
  11=>array("world"=>57,"lastUpdated"=>""),
  12=>array("world"=>58,"lastUpdated"=>""),
  13=>array("world"=>59,"lastUpdated"=>""),
  14=>array("world"=>60,"lastUpdated"=>""),
  15=>array("world"=>61,"lastUpdated"=>""),
  16=>array("world"=>63,"lastUpdated"=>""),
  17=>array("world"=>64,"lastUpdated"=>""),
  18=>array("world"=>65,"lastUpdated"=>""),
  19=>array("world"=>66,"lastUpdated"=>""),
  20=>array("world"=>67,"lastUpdated"=>""),
  21=>array("world"=>68,"lastUpdated"=>""),
  22=>array("world"=>69,"lastUpdated"=>"")
);

for ($i = 0; $i < count($worlds); $i++) {
  $w = $worlds[$i]["world"];
  $query = "SELECT `plans` FROM `plansPerWorld` WHERE `world` LIKE '$w'";
  $result = $mysqli->query($query);
  $resultArray = $result->fetch_array();
  if ($resultArray["plans"] < 5) {
    $worlds[$i]["lastUpdated"] = date("Y-m-d H:i:s");
  }
  else {
    $query = "SELECT `dateTime` FROM `lastUpdated` WHERE `world` LIKE '$w'";
    $result = $mysqli->query($query);
    $resultArray = $result->fetch_array();
    $worlds[$i]["lastUpdated"] = $resultArray["dateTime"];
  }
}

function cmp_dates($a, $b) {
    if (date($a["lastUpdated"]) == date($b["lastUpdated"]))
      return 0;
    else if (date($a["lastUpdated"]) < date($b["lastUpdated"]))
      return -1;
    else
      return 1;
}

usort($worlds, "cmp_dates"); // organizes the array by last updated first

$world = $worlds[0]["world"];

mail("site@twplan.com", "TWplan Village Database Updated", "RefreshVillages W" . $world .  " Opened");

if (!$world) {
  //mail("site@twplan.com","TWplan Village Database Updated", "No world supplied");
  printf("No world supplied\n");
  exit();
}
else {
  printf("Using world %d\n", $world);
}

set_time_limit(60); // in seconds; 1 min should be ample

$filepath = 'http://en' . $world. '.tribalwars.net/map/village.txt.gz';
$local_filepath = '/tmp/data/villages/en' . $world . '_village_data.txt';

// Unzips the remote data file into an array
$village_file = gzfile($filepath);

// Preprocesses the csv to decode the village names and remove extraneous columns
if (!$village_file) {
  printf("Error loading remote file %s\n", $filepath);
  exit();
}
else {
  printf("Loaded remote village data... \n");
  $write_to = '';
  for ($i = 0; $i < count($village_file); $i++) {
    $line = explode(',', $village_file[$i]);
    $write_to .= $line[0] . '>' . urldecode($line[1]) . '>' . $line[2] . '>' . $line[3] . '>' . $line[4] . "\n"; // Delimit using '>' because TW doesn't allow this character
  }
  printf("Processed data... \n");
}

// Pulls the processed csv file into the tmp folder
if (!file_put_contents($local_filepath, $write_to)) {
  printf("Error writing remote file %s to path %s\n", $filepath, $local_filepath);
  exit();
}
else {
  printf("Wrote data to tmp folder... \n");
}

$mysqli->select_db("twplanco_villages");

$truncate_query = "DROP TABLE IF EXISTS en{$world}";

if (!$mysqli->query($truncate_query)) {
    printf("Error truncating old table with query \n %s \n", $create_query);
    printf("Error message: %s \n", $mysqli->error);
    exit();
}
else {
	printf("Truncated old table...\n");
}

$create_query = "CREATE TABLE IF NOT EXISTS en{$world}
      (
        village_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        x_coord INT NOT NULL,
        y_coord INT NOT NULL,
        player_id INT NOT NULL,
      PRIMARY KEY
        (village_id)
      )";

if (!$mysqli->query($create_query)) {
    printf("Error creating table with query \n %s \n", $create_query);
    printf("Error message: %s \n", $mysqli->error);
    exit();
}
else {
  printf("Created table (IF NOT EXISTS)...\n");
}

$local_filepath_handle = fopen($local_filepath, "r");
    while (($data = fgetcsv($local_filepath_handle, 1000, ">"))) {
      $load_query = "INSERT INTO en{$world} values('" . implode('\',\'', $data) . "')";
        $mysqli->query($load_query) or printf("Error loading player data with query \n %s \n Error message: %s \n", $load_query, $mysqli->error);

    }
fclose($local_filepath_handle);

printf("Parsed csv into mysql...\n");

$end_time = microtime(true);

date_default_timezone_set("Europe/London");
$currenttime = date("Y-m-d H:i:s");
$message = "Database villages successfully loaded village data for table en{$world}! \nElapsed time:" . ($end_time - $start_time);
mail("site@twplan.com", "TWplan Village Database Updated", $message);


$mysqli->select_db("twplanco_analytics", $con);

$updatetime = "UPDATE `analytics`.`lastUpdated` SET dateTime='$currenttime' WHERE `world` = '$world'";
$mysqli->query($updatetime);

$mysqli->close();

exit();

?>