#!/bin/bash
export SHELL=/bin/bash

# setup
BIOSERVER_HOME=$(pwd)

NAME=BIOSERVER2
DESC="BIOSERVER2"
SU_USER="root"
MYSQL_LIB="lib/mysql-connector-java-5.1.49.jar"
BASE_DAMEON="java -cp $MYSQL_LIB:bin bioserver.ServerMain"
BUILD_CMD="javac -d $BIOSERVER_HOME/bin -cp $MYSQL_LIB $BIOSERVER_HOME/bioserver/*.java"
if [ "$#" -gt "1" ]; then
    DAEMON="$BASE_DAMEON ${@:(2):$#}"
else
    DAEMON="$BASE_DAMEON"
fi

start_server()
{
  if [ -e $BIOSERVER_HOME ]; then
    su $SU_USER -l -c "screen -S $NAME -A -d -m"
    su $SU_USER -l -c "screen -S $NAME -X stuff \"cd $BIOSERVER_HOME^M\""
    su $SU_USER -l -c "screen -S $NAME -X stuff \"$DAEMON^M\""
  else
    echo "No such directory: $BIOSERVER_HOME!"
  fi
}

case "$1" in
	start)
		echo "Starting $DESC: $NAME"
		start_server
		;;

	stop)
		if su $SU_USER -l -c "screen -ls" |grep $NAME; then
			echo -n "Stopping $DESC: $NAME"
			kill `su $SU_USER -l -c "screen -ls" |grep $NAME |awk -F . '{print $1}'|awk '{print $1}'`
			echo " ... done."
		else
			echo "Couldn't find a running $DESC"
		fi
		;;

	restart)
		if su $SU_USER -l -c "screen -ls" |grep $NAME; then
			echo -n "Stopping $DESC: $NAME"
			kill `su $SU_USER -l -c "screen -ls" |grep $NAME |awk -F . '{print $1}'|awk '{print $1}'`
			echo " ... done."
		else
			echo "Couldn't find a running $DESC"
		fi
		echo -n "Starting $DESC: $NAME"
		start_server
		echo " ... done."
		;;

	status)
		# Check whether there's a $NAME process
		ps aux | grep -v grep | grep $NAME > /dev/null
		CHECK=$?
		[ $CHECK -eq 0 ] && echo "$NAME is UP" || echo "$NAME is DOWN"
		;;
    
    build)
        echo -n "Build $NAME..."
        mkdir -p $BIOSERVER_HOME/bin 
        exec $BUILD_CMD
        ;;

	*)
		echo "Usage: $0 {start|stop|status|restart|build} [SERVER RUN ARGS (Optional)]"
		exit 1
		;;
esac

exit 0
