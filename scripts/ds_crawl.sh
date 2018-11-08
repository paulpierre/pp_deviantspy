#!/bin/bash
#============
# ds_crawl.sh
#============

args=("$@")
param_count=${args[0]}
NETWORK=${args[1]}

#countries
#array=( us id ca my au sg in nl )
array=( sg )

if [ -z "${param_count}" ]; then
    echo "Setting the param_count is required"
	exit 0
fi

if [ -z "${NETWORK}" ]; then
    echo "NETWORK param not set, defaulting to revcontent"
	NETWORK=1
fi

sql_get_total="SELECT ROUND(count(*)/$param_count) FROM placement";
segment_count=$(echo "$sql_get_total" | mysql --user=deviant --password='donkey!!' deviantspy --host dspy-1.cxltafv0vk2f.us-east-1.rds.amazonaws.com | sed -n 2p);
echo "Scanning placement_identifiers in segments of $segment_count"
C=1
until [  $C -gt $param_count ]; do
	COUNTER=$(((C*segment_count)+1))
	LOWER=$((COUNTER-segment_count+1))
	let C++
	#echo "$LOWER - $COUNTER"
	
	for i in "${array[@]}"
	do
		#echo "/usr/bin/php -q //mnt/deviantspy-master/api/index.php \"api.deviantspy.com/crawl/$NETWORK/$i/$LOWER:$COUNTER\""
		nohup /usr/bin/php -q //mnt/deviantspy-master/api/index.php "api.deviantspy.com/crawl/$NETWORK/$i/$LOWER:$COUNTER" &
		sleep 60
	done
done
exit 0
