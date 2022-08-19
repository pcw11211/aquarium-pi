#!/bin/bash

printSection()
{
  section="$1"
  found=false
  while read line
  do
    [[ $found == false && "$line" != "[$section]" ]] &&  continue
    [[ $found == true && "${line:0:1}" = '[' ]] && break
    found=true
    [[  $found == true &&  "$line" == "[$section]" ]] &&  continue
    echo $line
    #echo 123
    #string="$(echo $line)"
    #string=$(echo "$string" | sed 1d)
    #echo $string
  done
}

printSection1()
{
  section="$1"
  found=false
  while read line
  do
    [[ $found == false && "$line" != "[$section]" ]] &&  continue
    [[ $found == true && "${line:0:1}" = '[' ]] && break
    found=true
    echo "$line"
  done
}





my_path="$(dirname "$(realpath "$0")")"
#echo $my_path

for FILE in $my_path/sensors/*; do
  #echo $FILE;
#  printSection name <$FILE
  sensor_name="$(printSection name <$FILE)"
#  echo "$sensor_name"

  sensor_desc="$(printSection description <$FILE)"
  echo "$sensor_name ($sensor_desc)"

  sensor_exec=$(printSection "exec" <$FILE)
#  echo $my_path/bin/$sensor_exec

  if [ ! -f $(echo $my_path/bin/${sensor_exec} | cut -d " " -f 1) ]; then
    sensor_value="NA"
  else
    sensor_value=$($my_path/bin/$sensor_exec)
  fi
  echo $sensor_value

  #printSection alarm  <$FILE
  sensor_alarm=$(printSection alarm <$FILE)
  echo "$sensor_alarm"
  echo
done
