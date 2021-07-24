#!/bin/sh

# Config.
updatePath='/PATH/TO/UPDATE/DIRECTORY/'
apacheConfigPath="/etc/apache2/sites-enabled/"

# Count files update path.
count=0
for source in "$updatePath"*
do
  if [ "$source" != "$updatePath""*" ]
  then
    count=$((count + 1))
  fi
done

# If no file exists.
if [ "$count" == "0" ]
then
  exit
fi

# Copy files to apache path.
for source in "$updatePath"*
do
  if [ "$source" != "$updatePath""*" ]
  then
    IFS='/' read -r -a array <<< "${source}"
    fileName=${array[-1]}
    destination="${apacheConfigPath}""${fileName}"
    rm "${destination}"
    cp "${source}" "${destination}"
    chown root:root "${destination}"
    rm "${source}"
    count=$((count + 1))
  fi
done

# Restart apache.
sudo service apache2 restart

exit
