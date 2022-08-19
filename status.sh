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
    echo "$line" | cut -d ']' -f 2 | tr -dc '[[:print:]]'
  done
}

my_path="$(dirname "$(realpath "$0")")"
#echo $my_path

for FILE in $my_path/sensors/*; do
  #echo $FILE;
  sensor_name=$(printSection name <$FILE)
  sensor_desc=$(printSection description <$FILE)
  echo "$sensor_name ($sensor_desc)"
  sensor_exec=$(printSection "exec" <$FILE)
#  echo $my_path/bin/$sensor_exec

  if [ ! -f $(echo $my_path/bin/${sensor_exec} | cut -d " " -f 1) ]; then
    sensor_value="NA"
  else
    sensor_value=$($my_path/bin/$sensor_exec)
  fi
  echo $sensor_value
  echo
done
