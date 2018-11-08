<?php

/** =============
 *  geo.model.php
 *  =============
 */

class Geo extends Database {
    const TABLE_NAME = 'geo';

    public function get_proxies($geo=null,$include_disabled=false)
    {
        $db_conditions = Array();


        if(is_numeric($geo))
            $db_conditions['geo_id'] = $geo;
        else
            $db_conditions['geo_country'] = strtolower($geo);

        if(!$include_disabled)
            $db_conditions['geo_is_enabled'] = 1;

        if($geo==null) return false;


        $db_columns = array(
            'geo_id',
            'geo_name',
            'geo_ip',
            'geo_port'
        );

        $result = $this->db_retrieve(self::TABLE_NAME,$db_columns,$db_conditions,null,false);
        if(empty($result)) return false;
        return $result;
    }
}


