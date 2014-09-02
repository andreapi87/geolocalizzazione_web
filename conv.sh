#!/bin/bash
if [ "$#" -lt "3" ]; then
echo "formato: $0 log_file lista_jpg"
exit;
fi
files=$2
a=0;
while [ "$a" -eq "0" ]; do
read name;

if [ "$?" -ne "0" ]; then
a=1;
fi
if [ -z "$name" ]; then
break;
fi
echo $name;

name="${name%.*}".key;
name_escaped=$(sed -e 's/[\/&]/\\&/g' <<< $name)

b=$(sed -n "/$name_escaped/,/#########################/p" $1 | sed -n "/#### found solution/,/#########################/p"); 
echo "$b";
if [ ! -z "$b" ];then 
echo "$(echo "$b" | sed -e 's/ camera calibration:\s//'|sed -e 's/ camera rotation:\s//'|sed -e 's/ camera position: //'| sed -e 's/\[ \| \]//g' | sed -e '/found \|needed /d'|sed -e '/#/d' |sed -e '4 s/^$/0 0 0/'| sed -e '/^$/d' | sed -e 's/,//g')" > $3/$(basename $name).camera
fi
done < $files





