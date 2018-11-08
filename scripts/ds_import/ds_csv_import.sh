#/bin/sh
# +--------------------------------+
# | Deviant Spy CSV Import Script |
# +-------------------------------+
#  by Paul Pierre

now="$(date +'%d_%m_%Y_%H_%M_%S')"
logfile="ds_csv_import.log"
#chown deviantspy "$logfile"
echo "ds_csv_import.sh started at $(date +'%d-%m-%Y %H:%M:%S')" 

echo "reseting database..." 
#lets import the data
mysql --user=root --password='desapark!!' -vvv deviantspy < deviantspy_db.sql

echo "starting mysql database reset and import... " 
mysql --user=root --password='desapark!!' -vvv deviantspy < import_csv.sql
echo "finished mysql database reset and import... " 


echo "starting insert of latest widget IDs into the database... " 
/usr/bin/php -q /home/deviantspy/public_html/api/index.php "api.deviantspy.com/loader/load/fromPlacementUrls/1"
/usr/bin/php -q /home/deviantspy/public_html/api/index.php "api.deviantspy.com/loader/sync/placementsToPublishers/1"
  
echo "finished insert of latest widget IDs into the database... " 

#delete entries older than 30 days
echo "deleting entries older than 30 days and deleting duplicate placements..." 

read -d '' sql << EOF
#delete entries older than 30 days
SET UNIQUE_CHECKS=0;
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM offer WHERE offer_tcreate < DATE_SUB(NOW(), INTERVAL 30 DAY);
DELETE FROM scrape WHERE scrape_tcreate < DATE_SUB(NOW(), INTERVAL 30 DAY);

#delete duplicate entries
DELETE a
FROM placement as a, placement as b
WHERE
(a.placement_identifier   = b.placement_identifier OR a.placement_identifier IS NULL AND b.placement_identifier IS NULL)
AND (a.publisher_id = b.publisher_id OR a.publisher_id IS NULL AND b.publisher_id IS NULL)
AND a.placement_id < b.placement_id;
EOF
mysql --user=root --password='desapark!!' -vvv -e "$sql" deviantspy
echo "finished deleting..."

echo "ds_csv_import.sh finished at $(date +'%d-%m-%Y %H:%M:%S')" 
exit 0

