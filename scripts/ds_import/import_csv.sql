SET UNIQUE_CHECKS=0;
SET FOREIGN_KEY_CHECKS=0;
#import the CSV files

#------
# OFFER
#------
load data infile '/home/deviantspy/db_backup_03282016/offer.csv' into table offer
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;

#------
# AGENT
#------
load data infile '/home/deviantspy/db_backup_03282016/agent.csv' into table agent
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n';
#ignore 1 LINES;

#---------
# CREATIVE
#---------
load data infile '/home/deviantspy/db_backup_03282016/creative.csv' into table creative
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;

#--------
# NETWORK
#--------
load data infile '/home/deviantspy/db_backup_03282016/network.csv' into table network
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;



#----------
# PLACEMENT
#----------
load data infile '/home/deviantspy/db_backup_03282016/placement.csv' into table placement
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;


#-------
# SCRAPE
#-------
load data infile '/home/deviantspy/db_backup_03282016/scrape.csv' into table scrape
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;

#--------
# CRAWLER
#--------
load data infile '/home/deviantspy/db_backup_03282016/crawler.csv' into table crawler
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;

#----
# GEO
#----
load data infile '/home/deviantspy/db_backup_03282016/geo.csv' into table geo
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;

#-----
# NMAP
#-----
load data infile '/home/deviantspy/db_backup_03282016/nmap.csv' into table nmap
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;

#----------
# PUBLISHER
#----------
load data infile '/home/deviantspy/db_backup_03282016/publisher.csv' into table publisher
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;



#add foreign keys for


#agent
ALTER TABLE `agent`
ADD (
	PRIMARY KEY (`agent_id`));
	
#geo
ALTER TABLE `geo`
ADD (
	PRIMARY KEY (`geo_id`));
	
#network
ALTER TABLE `network`
ADD (
	PRIMARY KEY (`network_id`));

#publisher
ALTER TABLE `publisher`
ADD (
	PRIMARY KEY (`publisher_id`),
	FOREIGN KEY (`network_id`) REFERENCES network(`network_id`));
	
#offer
ALTER TABLE `offer`
ADD (
	PRIMARY KEY (`offer_id`),
  	UNIQUE KEY (`offer_hash`),
	FOREIGN KEY (`network_id`) REFERENCES network(`network_id`),
	FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`));

#placement
ALTER TABLE `placement`
ADD (
	PRIMARY KEY (`placement_id`),
	FOREIGN KEY (`network_id`) REFERENCES network(`network_id`),
	FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`));

#nmap
ALTER TABLE `nmap`
ADD (
	PRIMARY KEY (`nmap_id`),
  	UNIQUE KEY (`placement_identifier`));

#creative
ALTER TABLE `creative`
ADD (
	PRIMARY KEY (`creative_id`),
	UNIQUE KEY (`creative_hash`),
	FOREIGN KEY (`network_id`) REFERENCES network(`network_id`),
	FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`),
	FOREIGN KEY (`offer_id`) REFERENCES offer(`offer_id`));

#scrape
ALTER TABLE `scrape`
ADD (
	PRIMARY KEY (`scrape_id`),
	FOREIGN KEY (`network_id`) REFERENCES network(`network_id`),
	FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`),
	FOREIGN KEY (`offer_id`) REFERENCES offer(`offer_id`),
	FOREIGN KEY (`placement_id`) REFERENCES placement(`placement_id`),
	FOREIGN KEY (`creative_id`) REFERENCES creative(`creative_id`),
	FOREIGN KEY (`geo_id`) REFERENCES geo(`geo_id`),
	FOREIGN KEY (`agent_id`) REFERENCES agent(`agent_id`));

#crawler
ALTER TABLE `crawler`
ADD (
	PRIMARY KEY (`crawler_id`),
	FOREIGN KEY (`network_id`) REFERENCES network(`network_id`),
	FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`),
	FOREIGN KEY (`placement_id`) REFERENCES placement(`placement_id`));

#lets enable auto-increment

ALTER TABLE creative CHANGE creative_id creative_id INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE agent CHANGE agent_id agent_id INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE geo CHANGE geo_id geo_id INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE network CHANGE network_id network_id INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE publisher CHANGE publisher_id publisher_id INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE offer CHANGE offer_id offer_id INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE placement CHANGE placement_id placement_id INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE nmap CHANGE nmap_id nmap_id INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE crawler CHANGE crawler_id crawler_id INT(10) NOT NULL AUTO_INCREMENT;
ALTER TABLE scrape CHANGE scrape_id scrape_id INT(10) NOT NULL AUTO_INCREMENT;

/*
ALTER TABLE creative AUTO_INCREMENT = 1;
ALTER TABLE agent AUTO_INCREMENT = 1;
ALTER TABLE geo AUTO_INCREMENT = 1;
ALTER TABLE network AUTO_INCREMENT = 1;
ALTER TABLE publisher AUTO_INCREMENT = 1;
ALTER TABLE offer AUTO_INCREMENT = 1;
ALTER TABLE placement AUTO_INCREMENT = 1;
ALTER TABLE nmap AUTO_INCREMENT = 1;
ALTER TABLE crawler AUTO_INCREMENT = 1;
ALTER TABLE scrape AUTO_INCREMENT = 1;
*/

#-----------------------
# IMPORT NETWORK WIDGETS
#-----------------------
#load latest whitelist
load data infile '/home/deviantspy/ultimate_whitelist.csv' into table placement
fields terminated by ','
OPTIONALLY enclosed by '"'
lines terminated by '\n'
ignore 1 LINES;

#lets also reset it



#reenable foreign keys



SET FOREIGN_KEY_CHECKS=1;
SET UNIQUE_CHECKS=1;

