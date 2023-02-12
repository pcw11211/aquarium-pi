echo $(( (`date +%s` - $(cat $(dirname "$0")/water_change.txt) ) / 86400 ))
