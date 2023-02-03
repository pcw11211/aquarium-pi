#!/usr/bin/python

import sys
import serial
import time


#if len(sys.argv) == 1:
#  ser_dev = '/dev/serial0'

#if len(sys.argv) > 1:
#  ser_dev = sys.argv[1]

if len(sys.argv) == 1:
  avg_count = 5

if len(sys.argv) > 1:
  avg_count = int(sys.argv[1])



ser_dev = '/dev/serial0'

ser = serial.Serial()
ser.port=ser_dev
ser.baudrate=115200
ser.parity=serial.PARITY_NONE
ser.stopbits=serial.STOPBITS_ONE
ser.bytesize=serial.EIGHTBITS
ser.timeout = 1


try:
    ser.open()
except  Exception as e:
    #print("Error open serial port: " + str(e) )
    print(e)
    exit()




#print("connected to: " + ser.portstr)
#print(ser.isOpen())
#serisopen=ser.isOpen()
if not ser.isOpen():
  quit()
#print("connected to: " + ser.name)
count=1

#while True:
#    for line in ser.read().hex:
#        hexData= chr(line).hex()
#        print(str(count) + str(': ') + chr(line) )
#        count = count+1
#    print(ser.read(7))

dist=[]
#for x in range(0, avg_count):
x = 0
while ( x < avg_count):
  ser.flush()
  ser.flushInput()
  ser.flushOutput()
  ser.read_all()

  payload = b'\x01\x03\x00\x10\x00\x01\x85\xcf'
  ser.write(payload)
  time.sleep(0.1)

  hexData= ser.read(7).hex()
#  print(hexData)
#  print(hexData[6:10])
  hex=hexData[6:10]
  dec = int(hex, 16)
#  print(dec)
#  print(dec,end=',')

  if dec == 65535 :
#    x=x-1
    continue

#  sys.stdout.write(str(x) + ":" + str(dec) + ',')
#  sys.stdout.flush()
  dist.append(dec)
  ser.flush()
  x += 1

#print()
#print(str(dist))
#print(len(dist))
print(int(sum(dist) / len(dist) ) )

ser.close()


#while 1:
#    print ser.read()
#    hexData= ser.read().hex()
#    print hexData

# Clearing Input Buffer
#ser.flushInput()
 
# Clearing Output Buffer
#ser.flushOutput()

#sleep(.1)
#print ser.read(7)
#ser.close()
