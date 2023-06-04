<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

//count execution time
$start_time = microtime(true);

//error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

function get_sensor_data ()
{
  $sensors=[];
  $dir = realpath(dirname(__FILE__)."/../sensors/");
  //var_dump($dir);
  foreach (scandir($dir) as $file) {
    if ($file !== '.' && $file !== '..') {
    //var_dump($file);
    $file_contents=file_get_contents($dir ."/". $file);
    //echo "<pre><b>".$file."</b><br>".$file_contents."</pre>";
    //$sensors
    $file_lines=explode("\n", $file_contents);
    //var_dump($file_lines);
    $sensor_data=[];
    foreach ($file_lines as $file_line) {
      if ( $file_line == '' ){continue;}
      if ( preg_match('/^\[(.*)\]/', $file_line, $headers)){
      //var_dump($headers);
      //echo "<br>";
      $key=$headers[1];
      //$sensor_data[$key] = [];
      }else{
      //array_push($sensor_data, array($key => $file_line));
        if ( isset($sensor_data[$key]) ){
          $sensor_data[$key] = [ $sensor_data[$key], $file_line ];
        }else{
          $sensor_data[$key] = $file_line;
        }
      }
    }
    array_push($sensors,$sensor_data);
    //$file_contents=htmlspecialchars($file_contents);
    //$file_contents=preg_replace('/(.*)?[[]](.*)?/m', ';', $file_contents);
    //$file_contents=htmlspecialchars( $file_contents);
    //echo "<pre><b>".$file."</b><br>".$file_contents."</pre>";
    //$conf_array = parse_ini_string( $file_contents, true, INI_SCANNER_RAW);
    //var_dump($conf_array);
    }
  }

  //echo '<pre>';
  //var_export($sensors);
  //echo '</pre>';

  $logs_dir = realpath(dirname(__FILE__)."/../logs/");
  for ($i = 0; $i < count($sensors); $i++) {
  //$sensor_values = file($logs_dir.'/'.$sensor['name'].'.log');
  //$sensor_value = array_pop($sensor_values);
  //var_dump($sensor_value);

    $fp = fopen($logs_dir.'/'.$sensors[$i]['name'].'.log', 'r');
    //var_dump($fp);
    $pos = -2; $line = ''; $c = '';
    do {
      $line = $c . $line;
      fseek($fp, $pos--, SEEK_END);
      $c = fgetc($fp);
    } while ($c != "\n");
    //echo $line."<br>";
    $sensor_value=explode(",",$line);
    //var_dump($sensor_value);
    //echo "<br>";
    $sensors[$i]['value']=$sensor_value[1];
    $sensors[$i]['value_age']=$sensor_value[0];
  }

  //echo '<pre>';
  //var_export($sensors);
  //echo '</pre>';
  return $sensors;
}

/*


End of defighningn functions

*/


if ( $_SERVER["SCRIPT_URL"] == "/sensor_values" ){
  $sensor_values=get_sensor_data();
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($sensor_values);
  exit();
}

//echo "<pre>";
//var_dump($_SERVER);
//echo "</pre>";

if ( $_SERVER["SCRIPT_URL"] == "/" ){
?>
<html>
<head>
<style>
* {
  box-sizing: border-box;
}
.sensor {
  float: left;
  width: 50%;
  padding : 1em;
  text-align: center;
  border: 1px solid black;
  border-radius: 5%;
}
</style>
</head>
<body>
<div id="main">
<?php
  $sensor_values=get_sensor_data();
  foreach ($sensor_values as $sensor_value){
    echo "<div class=sensor  id=".$sensor_value['name'].">";
    echo "<div id=".$sensor_value['name']."_name>".$sensor_value['name']."</div>";
    echo "<div id=".$sensor_value['name']."_desc>".$sensor_value['description']."</div>";
    echo "<div id=".$sensor_value['name']."_value>".$sensor_value['value']."</div>";
    echo "<div id=".$sensor_value['name']."_value_age>".$sensor_value['value_age']."</div>";
    echo "</div>"; 
  }
?>
</div>


<div>
<label for='refresh_interval'>Choose refresh interval:</label>
<select id=refresh_interval onchange="start_interval()">
<option  value=5>5</option>
<option  value=10>10</option>
<option  value=15>15</option>
<option  value=30>30</option>
</select>
</div>

<div>
<?php
  // Calculate script execution time
  $end_time = microtime(true);
  $execution_time = ($end_time - $start_time);
  echo "<br><br> Execution time of script = ".$execution_time." sec";
?>
</div>
</body>
<script>

window.addEventListener('load', function () {
  start_interval()
})

function start_interval(){
  refresh_options = document.getElementById("refresh_interval")
  if (typeof interval_timer !== 'undefined') {clearInterval(interval_timer)}
  interval_timer = setInterval(load_data, ( refresh_options[refresh_options.selectedIndex].value * 1000 ));
}


async function load_data(){
  let url = '/sensor_values';
  let sensor_data = await (await fetch(url)).json();

 // sensor_data = await load();
  console.log(sensor_data)
}

</script>
<?php
}

?>



















