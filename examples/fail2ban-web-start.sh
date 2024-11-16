#!/bin/bash

# Poll until the socket file exists
while ! [ -S "__FAIL2BAN_SOCK__" ]
do
	sleep 1
done

# Poll until netcat notices someone's listening on the socket
while ! /bin/nc -zU "__FAIL2BAN_SOCK__"
do
	sleep 1
done

/bin/chmod u=rw,g=rw,o= "__FAIL2BAN_SOCK__"
