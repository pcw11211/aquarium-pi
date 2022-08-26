#!/bin/bash

time_now=$(date +%s)

printSection()
{
  section="$1"
  file="$2"

  if [ "$(echo $file | rev | cut -d'.' -f 1 | rev )" == "conf" ];then 
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
    done<<<$(cat $file)
  fi
}


my_path="$(dirname "$(realpath "$0")")"
#echo $my_path

PATH=$PATH:$my_path/bin
#echo $PATH


for arg in "$@"
do
  #echo "$var"
  if [ "$arg" == "log" ] || [ "$arg" == "logs" ]; then logging=true; fi
done




for FILE in $my_path/sensors/*; do
#   echo $FILE;
#  printSection name <$FILE
  sensor_name="$(printSection name $FILE)"
#  echo "$sensor_name"

  sensor_desc="$(printSection description $FILE)"
  echo "$sensor_name ($sensor_desc)"

  sensor_exec=$(printSection "exec" $FILE)
  echo $my_path/bin/$sensor_exec

  $sensor_exec >> /dev/null 2>&1
  OUT=$?
  echo "OUT=$OUT"
  if [ "$OUT" != "0" ];then
    sensor_value="NA"
  else
    sensor_value=$($sensor_exec)
  fi

#  echo $sensor_value

  #printSection alarm  <$FILE
  sensor_test="$(printSection test $FILE)"
  #echo "$sensor_test"

#  echo $sensor_value$sensor_test
  sensor_test_out=good
  #for test in $sensor_test; do


  #IFS=$'\n';for line in $sensor_test ; do
  #echo "$sensor_test"# |# while read line ; do
  while read line ; do
    #echo testing [ ${sensor_value} ${line} ]

    if [ ${sensor_value} ${line} ] ; then
      echo "good=${sensor_value} ${line}" >>/dev/null
      #echo $sensor_test_out
    else
      echo "error=${sensor_value} ${line}" >>/dev/null
      sensor_test_out=error
      #echo $sensor_test_out
    fi
  done<<<$sensor_test


  if [ "$sensor_test_out" == "good" ];then
    color='\033[0;32m'
  else
    color='\033[0;31m'
  fi
  NC='\033[0m' # No Color
  echo -e "${color}${sensor_value}${NC}"
  echo


  #save data to logs
  if [ "$logging" == true ]; then
    echo "${time_now},${sensor_value}" >> $my_path/logs/${sensor_name}.log
  fi

done


