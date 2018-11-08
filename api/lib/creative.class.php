<?php

/** ==================
 *  creative.class.php
 *  ==================
 */

class CreativeObject extends Creative {

    public function get_creative_by_hash($hash=null)
    {
        if($hash==null)
            throw new Exception('You must provide a creative hash');

        $db_conditions = Array();
        $db_conditions['creative_hash'] = $hash;

        $db_columns = array(
            'creative_id',
            'offer_id',
            'network_id',
            'publisher_id',
            'creative_hash',
            'creative_category',
            'creative_img',
            'creative_img_url',
            'creative_position',
            'creative_headline',
            'creative_view_count',
            'creative_tcreate',
            'creative_tmodified'
        );

        $result = $this->db_retrieve(self::TABLE_CREATIVE,$db_columns,$db_conditions,null,false);
        if(empty($result[0]))
            return false;

        $creative_object = new Creative($result[0]);
        if ($creative_object instanceof Creative)
            return $creative_object;
        else return false;

    }
}




