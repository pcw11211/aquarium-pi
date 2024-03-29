#!/bin/bash

#while true; do
  if  [ ! -f /sys/bus/w1/devices/28-3c89f64845f4/w1_slave ];then
#    echo "Cannot find the thermal sensor"
    raspi-gpio set 4 pu
    raspi-gpio set 17 op dh
    sleep 1
#  else
#    noop
  fi

#done


#while true; do
temp_raw="$(cat /sys/bus/w1/devices/28-3c89f64845f4/w1_slave)"

if [ "$?" == "1" ]; then
  exit 1
fi


temp_c=$(echo "scale=2; $(echo "$temp_raw" | grep 't=' | cut -d '=' -f2) / 1000" |bc)

if [ "$?" == "1" ]; then
  exit 1
fi

if [ -z "$temp_c" ]; then
  #echo not set
  exit 1
fi

temp_f=$(echo "scale=2; $temp_c * 9.0 / 5.0 + 32.0" | bc )

#echo -n -e "\r"


if [ "$1" == "C" ] || [ "$1" == "c" ] ; then
  echo "$temp_c"
else
  echo "$temp_f"
#  exit 1
fi



#echo -n -e "   "

#echo -n -e "\b/"
#sleep 1
#echo  -n  -e "\b-"
#sleep 1
#echo -n -e "\b\\"
#sleep 1
#echo -n -e "\b|"
#sleep 1


#done
