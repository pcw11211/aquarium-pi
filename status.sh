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
  if [ "$arg" == "log" ]   || [ "$arg" == "logs" ]; then logging=true;  fi
  if [ "$arg" == "debug" ] || [ "$arg" == "d" ];    then debuging=true; fi
done




for FILE in $my_path/sensors/*; do
#   echo $FILE;
#  printSection name <$FILE
  sensor_name="$(printSection name $FILE)"
#  echo "$sensor_name"

  sensor_desc="$(printSection description $FILE)"
  echo "$sensor_name ($sensor_desc)"

  sensor_exec=$(printSection "exec" $FILE)
  if [ $debuging ]; then echo $my_path/bin/$sensor_exec; fi

  $sensor_exec &> /dev/null
  OUT=$?
  if [ $debuging ]; then echo "OUT=$OUT"; fi
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
  if [ "${sensor_value}" == "NA" ]; then 
    sensor_test_out=error
  else
    sensor_test_out=none
  fi


  while read line ; do
    echo testing [ ${sensor_value} ${line} ]
    if [ "${line}" == "" ]; then continue; fi
    if [ "${sensor_value}" == "NA" ]; then continue ; fi 
    if [ $(echo "${sensor_value} ${line}" | bc) == 1 ] && [ "$sensor_test_out" != "error" ] ; then
      echo "good=${sensor_value} ${line}" >>/dev/null
      sensor_test_out=good
      #echo $sensor_test_out
    else
      echo "error=${sensor_value} ${line}" >>/dev/null
      sensor_test_out=error
      sensor_test_line="${line}"
      #echo $sensor_test_out
    fi
  done<<<$sensor_test
  echo $sensor_test_out

  #value dispaly setup
  sensor_display=$(printSection "value display" $FILE)
  if [ $debuging ]; then echo $sensor_display; fi
  if [ "$sensor_display" != "" ];then
    sensor_display=$( echo $sensor_display | sed 's#$value#'"${sensor_value}"'#')
    #echo $sensor_display
    sensor_value=$($sensor_display)
  fi

  color=''
  if [ "$sensor_test_out" == "good" ];then
    color='\033[0;32m'
  elif [ "$sensor_test_out" == "error" ];then
    color='\033[0;31m'
  else
    color=''
  fi
  NC='\033[0m' # No Color
  echo -e "${color}${sensor_value}${NC}"
#  echo
  
  #save data to logs
  if [ "$logging" == true ]; then
    echo "${time_now},${sensor_value}" >> $my_path/logs/${sensor_name}.log
  fi


  #alrming
  if [ ! -f $my_path/alerts/${sensor_name}.log ];then touch $my_path/alerts/${sensor_name}.log; fi
  if [ "$(printSection alerts $FILE)" == "true"  ] && [ "$sensor_test_out" == "error" ]; then
    alerts_freq="$(printSection alerts_frequency $FILE)"
    #echo $alerts_freq
    alerts_freq_num=$(echo $alerts_freq | cut -d " " -f 1)
    alerts_freq_measure=$(echo $alerts_freq | cut -d " " -f 2)
    #echo "alerts_freq_num: ---${alerts_freq_num}---"
    #echo "alerts_freq_measure: ---${alerts_freq_measure}---"

    if [[ ! "${alerts_freq_measure}" =~ ^[s,m,h,d]$ ]];then echo "Time measure incorrect format, only s,m,h,d is allowed"; continue; fi
    if [[ ! "${alerts_freq_num}"     =~ ^[0-9]+$   ]];then echo "Time amount incorrect format, only numbers are allowed. ${alerts_freq_num}";   continue; fi

    if [ "${alerts_freq_measure}" == "s" ]; then alerts_freq_sec=$(( ${alerts_freq_num} )) ;fi
    if [ "${alerts_freq_measure}" == "m" ]; then alerts_freq_sec=$(( ${alerts_freq_num} * 60 )) ;fi
    if [ "${alerts_freq_measure}" == "h" ]; then alerts_freq_sec=$(( ${alerts_freq_num} * 60 * 60 )) ;fi
    if [ "${alerts_freq_measure}" == "d" ]; then alerts_freq_sec=$(( ${alerts_freq_num} * 60 * 60 * 24 )) ;fi


    last_alert=$(tail -n 1 $my_path/alerts/${sensor_name}.log | cut -d "," -f 1)
    if [ -z $last_alert ];then last_alert=0;fi
    
	#echo last_alert: $last_alert
	#echo time_now: $time_now
	#echo "alerts_freq_sec: $alerts_freq_sec"
    
    if [  $time_now -lt $(( $last_alert + $alerts_freq_sec )) ];then
      echo "time frequency didnt pass no need to alert"
      till_next_alert=$(( $last_alert + $alerts_freq_sec - $time_now  ))
      #echo Next alert in $(( $till_next_alert / 60 / 60 )) h,  $(( $till_next_alert / 60 / 60)) m, $(( $till_next_alert / 60 / 60 / 60 )) s
      echo Next alert in $(date -u -d @${till_next_alert} +"%T")
      echo
      continue
    fi
	
	
    echo alerting
    #echo ${sensor_desc}

    email_content="$(cat $my_path/email.template)"
#    echo "$email_content"
#    email_content="$(echo "$email_content")"
    email_content="$(echo "$email_content" | sed 's|\"sensor\"|'"${sensor_name}"'|g'      )"
    email_content="$(echo "$email_content" | sed 's|\"desc\"|'"${sensor_desc}"'|g'        )"
    email_content="$(echo "$email_content" | sed 's|\"value\"|'"${sensor_value}"'|g'      )"
    #echo "$email_content"
    (echo "$email_content") | /usr/sbin/sendmail pinchasweinstein@gmail.com

    echo "${time_now},${sensor_value}" >> $my_path/alerts/${sensor_name}.log

  fi

  echo
done


