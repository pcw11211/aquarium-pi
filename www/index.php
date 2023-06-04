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

echo "<h1>Authors</h1>";
echo "<ul id=\"authors\"></ul>";


// Calculate script execution time
$end_time = microtime(true);
$execution_time = ($end_time - $start_time);
echo "<br><br> Execution time of script = ".$execution_time." sec";

?>


<script>

async function load() {
    let url = '/sensor_values';
    let obj = await (await fetch(url)).json();
    //console.log(obj);
    //return obj;
    //sensor_data =  obj;
}

//const sensor_data = await load();
//sensor_data = load();

var text
load().then(res=>{text = res});

//fetch('/sensor_values')
//  .then((response) => {
//    return response.json();
//  })
//  .then((myJson) => {
//    //console.log(myJson);
//    sensor_data = myJson
//  });


console.log(text)




var obj;

fetch('/sensor_values')
  .then(res => res.json())
  .then(data => {
    obj = data;
   })
  .then(() => {
     
    //console.log(obj);
   });

console.log(obj)

fetch('/sensor_values')
    .then(jsonData => jsonData.json())
    .then(data => printIt(data))

let printIt = (data) => {
//    console.log(data)
}

console.log(printIt())





















</script>







