<?php

/** ===================
 *  agent.model.php
 *  ===================
 */

class Agent extends Database {
    const TABLE_NAME = 'agent';

    public function get_publishers($network_id=false,$include_disabled=false)
    {
        $db_conditions = Array();
        if(!$include_disabled)
            $db_conditions['publisher_is_enabled'] = 1;
        if($network_id)
            $db_conditions['network_id'] = $network_id;

        $db_columns = array(
            'publisher_id',
            'network_id',
            'publisher_network_id',
            'publisher_name',
            'publisher_domain',
        );

        $result = $this->db_retrieve(self::TABLE_NAME,$db_columns,$db_conditions,null,false);
        if(empty($result)) return false;
        return $result;
    }





}


