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
    if ($file !== '.' && $file !== '..' && $file[0] != '.') {
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
      //echo $file_line."<br>";
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
    if (file_exists($logs_dir."/".$sensors[$i]['name'].'.log')){
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
  }

  //echo '<pre>';
  //var_export($sensors);
  //echo '</pre>';
  return $sensors;
}

function get_sensor_history ($sensor_name, $length=10){
  $logs_dir = realpath(dirname(__FILE__)."/../logs/");
  if (file_exists($logs_dir."/".$sensor_name.".log")){
    $fp = fopen($logs_dir.'/'.$sensors[$i]['name'].'.log', 'r');
    var_dump($fp);
    $pos = -2 - $length; 
    $line = ''; $c = '';
    do {
      $line = $c . $line;
      fseek($fp, $pos--, SEEK_END);
      $c = fgetc($fp);
    } while ($c != "\n");
    echo $line."<br>";
    //$sensor_value=explode(",",$line);
    //var_dump($sensor_value);
    //echo "<br>";
    //$sensors[$i]['value']=$sensor_value[1];
    //$sensors[$i]['value_age']=$sensor_value[0];

  }
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


if ( $_SERVER["SCRIPT_URL"] == "/sensor_history" ){
  echo "<pre>";
  var_dump($_GET);
  echo "</pre>";
  if (isset($_GET['sensor']) && isset($_GET['length'])){
    echo "running function sensor history";
    $output=get_sensor_history($_GET['sensor'],isset($_GET['length']));
    echo "<pre>";
    var_dump($output);
    echo "</pre>";
    exit();
  } 
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
body {
 text-align: center;
}
.main {
  /*width: 80%;*/
  display: block;
  /*float: left;*/ 
  /*width: 98%;*/
  height: fit-content;
}
.sensor {
  /*float: left;*/
  display: block;
  height: auto;
  position: relative;
  width: 20%;
  padding : 1em;
  margin : 1em ;
  text-align: center;
  border: 1px solid black; 
  border-radius: 5px;
  box-shadow: 0px 19px 217px 0px rgba(0,0,0,0.3),0px 15px 12px 0px rgba(0,0,0,0.22);
}
.sensor_value {
  font-weight: bold;
}
.interval {
  /*float: left;*/
  width : 20%; 
  margin: 1em;
  padding: 0.5em;
}
</style>
</head>
<body>
<div class=main id="main">
<?php
  $sensor_values=get_sensor_data();
  foreach ($sensor_values as $sensor_value){
    echo "<div class=sensor  id=".$sensor_value['name'].">";
//    echo "<div class=sensor_name id=".$sensor_value['name']."_name>".$sensor_value['name']."</div>";
    echo "<div id=".$sensor_value['name']."_desc>".$sensor_value['description']."</div>";
    echo "<div class=sensor_value id=".$sensor_value['name']."_value>"."NA"."</div>";
    echo "<div id=".$sensor_value['name']."_value_age>".$sensor_value['value_age']."</div>";
    echo "</div>"; 
  }
?>
</div>

<div class=interval>
<label for='refresh_interval'>Choose refresh interval:</label>
<select id=refresh_interval onchange="start_interval()">
<option  value=5>5</option>
<option  value=10>10</option>
<option selected="selected" value=15>15</option>
<option  value=30>30</option>
</select>
</div>

<div>
<?php
  // Calculate script execution time
  $end_time = microtime(true);
  $execution_time = ($end_time - $start_time);
  echo "Execution time of script = ".$execution_time." sec";
?>
</div>
</body>
<script>

window.addEventListener('load', function () {
  load_data()
  start_interval()
})

function start_interval(){
  refresh_options = document.getElementById("refresh_interval")
  if (typeof interval_timer !== 'undefined') {clearInterval(interval_timer)}
  interval_timer = setInterval(load_data, ( refresh_options[refresh_options.selectedIndex].value * 1000 ));
}


async function load_data(){
  let url = '/sensor_values';
  let sensors_data = await (await fetch(url)).json();

 // sensor_data = await load();
  //console.log(sensors_data); 
  sensors_data.forEach(function (item) {
    console.log(item)

    //add color to value
    test_error="pass"
    tests=[]
    tests = tests.concat(item['test'])
//    console.log(tests)
    tests.forEach(function(test){
      console.log(test)
      if ( eval(item['value']+test)) {test_error=test_error}else{test_error="fail"}
    })
    console.log(item['name']+":"+item['value']+":"+test_error)
    if (test_error=="pass"){document.getElementById(item['name']+"_value").style.color = "green"}
    if (test_error=="fail"){document.getElementById(item['name']+"_value").style.color = "red"}

    //display value correctly
    if (typeof item['value display'] !== 'undefined') {
      display_val=item['value display'].replace('echo ','').replace('$value',item['value'])
    }else{
      display_val=item['value']
    }
    
    document.getElementById(item['name']+"_value").innerHTML=display_val;
    document.getElementById(item['name']+"_value_age").innerHTML=item['value_age']
    //copy.push(item + item+2)
  });
}
</script>
<?php
}

?>



















