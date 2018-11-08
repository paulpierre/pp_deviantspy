
/**
  +-------------------------------------------+
  | Deviant Spy - Native Network Ad Spy Tool |
 +-------------------------------------------+
  by Paul Pierre
 
  =========
  networks
  =========
  A listing of all the networks we will scrape from
 */

SET UNIQUE_CHECKS=0;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `nmap`;
DROP TABLE IF EXISTS `creative`;
DROP TABLE IF EXISTS `offer`;
DROP TABLE IF EXISTS `scrape`;
DROP TABLE IF EXISTS `crawler`;
DROP TABLE IF EXISTS `geo`;
DROP TABLE IF EXISTS `agent`;
DROP TABLE IF EXISTS `placement`;
DROP TABLE IF EXISTS `publisher`;
DROP TABLE IF EXISTS `network`;

/*===
  geo
  ===
  Proxy list for different geos */

CREATE TABLE `geo`(
 `geo_id` int(10),

 `geo_name` varchar(255) NOT NULL,
 `geo_country` varchar(255) NOT NULL,
 `geo_ip` varchar(255) NOT NULL,
 `geo_port` int(7) NOT NULL,
 `geo_user` varchar(255) NOT NULL,
 `geo_pw` varchar(255) NOT NULL,

 `geo_status` int(3) NOT NULL, 
 `geo_is_enabled` int(3) NOT NULL,

 `geo_tmodified` DATETIME NOT NULL,
 `geo_tcreate` DATETIME NOT NULL
  #PRIMARY KEY (`geo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*=====
  agent
  =====
  User-agent list for different platform */

CREATE TABLE `agent`(
 `agent_id` int(10),
 `agent_name` varchar(255) NOT NULL,
 `agent_string` varchar(1500) NOT NULL,

 `agent_status` int(3) NOT NULL, 
 `agent_is_enabled` int(3) NOT NULL,

 `agent_tmodified` DATETIME NOT NULL,
 `agent_tcreate` DATETIME NOT NULL
  #PRIMARY KEY (`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `network`(
 `network_id` int(10), 
 `network_name` varchar(255) NOT NULL,
 `network_status` int(3) NOT NULL, 
 `network_is_enabled` int(3) NOT NULL, 
 `network_tmodified` DATETIME NOT NULL,
 `network_tcreate` DATETIME NOT NULL
  #PRIMARY KEY (`network_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*=========
  publisher
  =========
  A listing of all the publishers */

CREATE TABLE `publisher`(
 `publisher_id` int(10), 
 `network_id` int(10) NOT NULL,
 `publisher_name` varchar(255) NOT NULL,
 `publisher_domain` varchar(255) NOT NULL, 
 `publisher_status` int(3) NOT NULL,
 `publisher_is_enabled` int(3) NOT NULL,
 `publisher_tmodified` DATETIME NOT NULL,
 `publisher_tcreate` DATETIME NOT NULL
  #PRIMARY KEY (`publisher_id`)
/*
  FOREIGN KEY (`network_id`) REFERENCES network(`network_id`) ON DELETE CASCADE ON UPDATE CASCADE
*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*=====
  offer
  =====
  A listing of all the offers */

CREATE TABLE `offer`(
 `offer_id` int(10),
 `publisher_id` int(10) NOT NULL,
 `network_id` int(10) NOT NULL,
 `offer_view_count` int(7) NOT NULL, 
 `offer_click_url` varchar(1500) NOT NULL,
 `offer_redirect_urls` blob NOT NULL,
 `offer_destination_url` varchar(1500) NOT NULL,
 `offer_domain` varchar(1500) NOT NULL,
 `offer_hash` varchar(100) NOT NULL,

 `offer_tmodified` DATETIME NOT NULL,
 `offer_tcreate` DATETIME NOT NULL
  #PRIMARY KEY (`offer_id`),
  #UNIQUE KEY (`offer_hash`)
/*
  FOREIGN KEY (`network_id`) REFERENCES network(`network_id`)  ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`)  ON DELETE CASCADE ON UPDATE CASCADE
*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*=========
  placement
  =========
  Placements mapped to publishers */

CREATE TABLE `placement`(
 `placement_id` int(10),

 `placement_identifier` varchar(100) NOT NULL,
 `publisher_id` int(10) NOT NULL,
 `network_id` int(10) NOT NULL,

 `placement_type` int(3) NOT NULL,
 `placement_url` varchar(1500) NOT NULL,
 `placement_status` int(3) NOT NULL,

 `placement_tmodified` DATETIME NOT NULL,
 `placement_tcreate` DATETIME NOT NULL
  #PRIMARY KEY (`placement_id`)
/*
  FOREIGN KEY (`network_id`) REFERENCES network(`network_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`) ON DELETE CASCADE ON UPDATE CASCADE
*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*====
  nmap
  ====
  Placement ID map of networks */


CREATE TABLE `nmap`(
 `nmap_id` int(10),

 `placement_identifier` varchar(100) NOT NULL,
 `publisher_id` int(10) NOT NULL,
 `network_id` int(10) NOT NULL,

 `nmap_type` int(3) NOT NULL,
 `nmap_url` varchar(1500) NOT NULL,
 `nmap_status` int(3) NOT NULL,

 `nmap_tmodified` DATETIME NOT NULL
  #PRIMARY KEY (`nmap_id`),
  #UNIQUE KEY (`placement_identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*========
  creative
  ========
  A listing of all the offers */

CREATE TABLE `creative`(
 `creative_id` int(10),

 `offer_id` int(10) NOT NULL,
 `network_id` int(10) NOT NULL,
 `publisher_id` int(10) NOT NULL,

 `creative_view_count` int(7) NOT NULL,
 `creative_headline` varchar(1500) NOT NULL,
 `creative_img` varchar(1500) NOT NULL,
 `creative_img_url` varchar(1500) NOT NULL,
 `creative_position` int(3) NOT NULL,
 `creative_category` varchar(255) NOT NULL,
 `creative_hash` varchar(100) NOT NULL,
 `creative_tmodified` DATETIME NOT NULL,
 `creative_tcreate` DATETIME NOT NULL

  #PRIMARY KEY (`creative_id`),
  #UNIQUE KEY (`creative_hash`)
/*
  FOREIGN KEY (`network_id`) REFERENCES network(`network_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`offer_id`) REFERENCES offer(`offer_id`) ON DELETE CASCADE ON UPDATE CASCADE
*/
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*======
  scrape
  ======
  Log of all the scrape attempts */

CREATE TABLE `scrape`(
 `scrape_id` int(10),
 `publisher_id` int(10) NOT NULL,
 `network_id` int(10) NOT NULL,
 `offer_id` int(10) NOT NULL,

 `placement_id` int(10) NOT NULL,
 `creative_id` int(10) NOT NULL,
 `creative_position` int(3) NOT NULL,
 `geo_id` int(10) NOT NULL,
 `agent_id` int(10) NOT NULL,

 `scrape_tstart` DATETIME NOT NULL,
 `scrape_tfinish` DATETIME NOT NULL,

 `scrape_tmodified` DATETIME NOT NULL,
 `scrape_tcreate` DATETIME NOT NULL

  #PRIMARY KEY (`scrape_id`)
/*
  FOREIGN KEY (`network_id`) REFERENCES network(`network_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`placement_id`) REFERENCES placement(`placement_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`creative_id`) REFERENCES creative(`creative_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`offer_id`) REFERENCES offer(`offer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`geo_id`) REFERENCES geo(`geo_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`agent_id`) REFERENCES agent(`agent_id`) ON DELETE CASCADE ON UPDATE CASCADE
*/

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*=======
  crawler
  =======
  Configuration for all the scrapes */

CREATE TABLE `crawler`(
 `crawler_id` int(10),
 `network_id` int(10) NOT NULL,
 `placement_id` int(10) NOT NULL,
 `publisher_id` int(10) NOT NULL,
 `crawler_geo_config` blob NOT NULL,
 `crawler_agent_config` blob NOT NULL,
 `crawler_interval` int(10) NOT NULL,
 `crawler_cronjob` varchar(255) NOT NULL,
 `crawler_count` int(10) NOT NULL,
 `crawler_is_enabled` int(3) NOT NULL,
 `crawler_status` int(3) NOT NULL,

 `crawler_tmodified` DATETIME NOT NULL,
 `crawler_tcreate` DATETIME NOT NULL

  #PRIMARY KEY (`crawler_id`)
/*
  FOREIGN KEY (`network_id`) REFERENCES network(`network_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`publisher_id`) REFERENCES publisher(`publisher_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`placement_id`) REFERENCES placement(`placement_id`) ON DELETE CASCADE ON UPDATE CASCADE
*/

) ENGINE=InnoDB DEFAULT CHARSET=utf8;
