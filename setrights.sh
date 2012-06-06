#!/bin/bash 

# see http://stackoverflow.com/a/1638397
# Absolute path to this script, e.g. /home/user/bin/foo.sh
SCRIPT=`readlink -f $0`
# Absolute path this script is in, thus /home/user/bin
SCRIPTPATH=`dirname $SCRIPT`
if [ $SCRIPTPATH=='/bin' ]
  then SCRIPTPATH=`pwd`
fi

USER=`whoami`
GROUP=$1
if [ $GROUP=='' ]
  then
    GROUP='www-data'
fi

echo "Path: $SCRIPTPATH"
echo "User: $USER"
echo "Group: $GROUP"

sudo chown -R $USER $SCRIPTPATH
sudo chgrp -R $GROUP $SCRIPTPATH
sudo chmod -R 770 $SCRIPTPATH

