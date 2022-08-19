#!/bin/sh

vcgencmd measure_temp | cut -d '=' -f 2 | cut -d "'" -f 1
