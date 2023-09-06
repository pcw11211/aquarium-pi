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


/*
      $pos = -2; $line = ''; $c = '';
      do {
        $line = $c . $line;
        fseek($fp, $pos--, SEEK_END);
        $c = fgetc($fp);
      } while ($c != "\n");
      $sensor_value=explode(",",$line);
*/

      fseek($fp, -50, SEEK_END);
      $lastlines = fread($fp,65535);
      $lastlines_arr = explode(PHP_EOL,$lastlines);
      $lastline = $lastlines_arr[count($lastlines_arr) -2];
      $sensor_value=explode(",",$lastline);

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
    $fp = fopen($logs_dir.'/'.$sensor_name.'.log', 'r');
    //var_dump($fp);
    //var_dump($length);
    $file_size=filesize($logs_dir."/".$sensor_name.".log");
    $lines=array();
    $read_data='';
    $chunk=512;
    $pos = 0;
    while (count($lines)<$length && abs($pos) < $file_size )
    {
    /*
      (!isset($pos)) ? $pos = -2 : $pos=$pos;
      //$pos = -2;
      $line = ''; $c = '';
      do {
        $line = $c . $line;
        fseek($fp, $pos--, SEEK_END);
        $c = fgetc($fp);
      } while ($c != "\n");
    array_push($lines, $line);
    */
      $pos=($pos - $chunk);
//      var_dump(abs($pos));
//      var_dump($file_size);
      fseek($fp, $pos, SEEK_END);
      $read_data=fread($fp, abs($pos));
      $lines=explode(PHP_EOL,$read_data);
      array_pop($lines);
    }
    array_shift($lines);
    /*
    $lines=array();
    while(!feof($fp))
    {
      $line = fgets($fp, 4096);
      array_push($lines, $line);
    array_push($lines, $line)  if (count($lines)>5)
        array_shift($lines);
    }
    */

    fclose($fp);
    return $lines;
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
  //echo "<pre>";
  //var_dump($_GET);
  //echo "</pre>";
  if (isset($_GET['sensor']) && isset($_GET['length'])){
    //echo "running function sensor history<br>";
    $output=get_sensor_history($_GET['sensor'],$_GET['length']);
    //echo "<pre>";
    //var_dump($output);
    //echo "</pre>";

  
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time);
    //echo "Execution time of script = ".$execution_time." sec";

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($output);

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
  border: 1px solid black;
  height: fit-content;
  display:block;
}
.sensor_current {
  float: left;
  /*display: block;*/
  display: inline-block;
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
.sensor_hist {
  display: inline-block;
  height: 100%;
  width: 70%;
  padding : 1em;
  margin : 1em ;

  position: relative;
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
    echo "<div class=sensor id=".$sensor_value['name'].">";
    echo "<div class=sensor_current  id=".$sensor_value['name']."_current >";
//    echo "<div class=sensor_name id=".$sensor_value['name']."_name>".$sensor_value['name']."</div>";
    echo "<div id=".$sensor_value['name']."_desc>".$sensor_value['description']."</div>";
    echo "<div class=sensor_value id=".$sensor_value['name']."_value>"."NA"."</div>";
    echo "<div id=".$sensor_value['name']."_value_age>".$sensor_value['value_age']."</div>";
    echo "</div>";
    echo "<div class=sensor_hist id=".$sensor_value['name']."_hist ><canvas id=".$sensor_value['name']."_canvas></canvas>";
    echo "</div>";
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.27.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@0.1.1"></script>
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
  console.log(sensors_data); 
  sensors_data.forEach(async function (item) {
//    console.log(item)

    //add color to value
    test_error="pass"
    tests=[]
    tests = tests.concat(item['test'])
//    console.log(tests)
    tests.forEach(function(test){
//      console.log(test)
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
   // value_age=
    document.getElementById(item['name']+"_value_age").innerHTML=moment(new Date(item['value_age']*1000)).format('hh:mm:ss a');
    //copy.push(item + item+2)


    //get sensor history
    let url_hist = "/sensor_history?sensor="+item['name']+"&length=80";
    //console.log(url_hist)
    let sensors_hist = await (await fetch(url_hist)).json();

    
    const xValues=[]
    const yValues=[]
    const pointBackgroundColors=[]
    hist_tests=[]
    hist_tests = hist_tests.concat(item['test'])

    sensors_hist.forEach(function (hist_item) {
        xValues.push(new Date(hist_item.split(",")[0] * 1000))
        hist_item_value=hist_item.split(",")[1]
        yValues.push(hist_item_value)
     
        //add color to value
        hist_test_error="green"
        //hist_tests=[]
        //tests = tests.concat(item['test'])
  //    console.log(tests)
        hist_tests.forEach(function(test){
//      console.log(test)
           if ( eval(hist_item_value+test)) {hist_test_error=hist_test_error}else{hist_test_error="red"}
        })
        pointBackgroundColors.push(hist_test_error)

    })

    
    //const xValues=[1,2,3,4,5,6,7,8,9]
    //minValue = Math.min(...yValues);
    //maxValue = Math.max(...yValues);
    //span=((maxValue - minValue) * 1 + 2 )
    //minValue = (minValue - span).toFixed(0)
    //maxValue = (maxValue + span).toFixed(0)


    //console.log("sensors_hist="+sensors_hist)
    if (typeof window[item['name']+"_chart"] !== 'undefined'){
        //console.log( window[item['name']+"_chart"].config.data.labels[0] )
        //console.log( xValues[0] ) 
        //console.log( window[item['name']+"_chart"].config.data.labels[0].getTime() !== xValues[0].getTime() )
        if (  window[item['name']+"_chart"].config.data.labels[0].getTime() !== xValues[0].getTime() ){
          //window[item['name']+"_chart"].destroy();
            console.log( "updating chart: "+item['name']+"_chart")
            window[item['name']+"_chart"].config.data.labels.pop()
            window[item['name']+"_chart"].config.data.labels.unshift(xValues[0])
            window[item['name']+"_chart"].config.data.datasets[0].data.pop()
            window[item['name']+"_chart"].config.data.datasets[0].data.unshift(yValues[0])
            window[item['name']+"_chart"].config.data.datasets[0].backgroundColor.pop()
            window[item['name']+"_chart"].config.data.datasets[0].backgroundColor.unshift(hist_test_error)
            window[item['name']+"_chart"].update();
            return
        }else{
            return
        }
    }
    //console.log("xValues="+xValues)
    //console.log("yValues="+yValues)
    //console.log("minValue="+minValue)
    //console.log("maxValue="+maxValue)


    //console.log(sensors_hist)
    const ctx = document.getElementById(item['name']+'_canvas');
    window[item['name']+"_chart"] =  new Chart(ctx, {
      type: 'line',
      data: {
          labels: xValues,
          datasets: [{
            fill: false,
            lineTension: 0,
            //backgroundColor: "rgba(0,0,255,1.0)",
            //borderColor: "rgba(0,0,255,0.1)",
            data: yValues,
            backgroundColor: pointBackgroundColors,
          }]
      },
      options: {
        plugins: {
          legend: {
            display: false
          }
        },
        //legend: {display: false},
        responsive: true,
        aspectRatio: 5, 
        //maintainAspectRatio: false,
        radius: 2,
        borderWidth: 1,
        scales: {
          y: {
            //min: minValue,
            //max: maxValue,
             //reverse: true,
          },
          x: {
            display: true,
            type: "time",
            reverse: true,
          //  time: {
          //          unit: 'minute'
          //      }
          } 
        }
      }
    });

  });
}
</script>


<?php
}

?>



















