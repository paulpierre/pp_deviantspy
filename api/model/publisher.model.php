<?php

/** ===================
 *  publisher.model.php
 *  ===================
 */

class Publisher extends Database {
    const TABLE_PUBLISHER = 'publisher';
    const TABLE_PLACEMENT = 'placement';
    const TABLE_OFFER = 'offer';

    public $id = null;
    public $network_id = null;
    public $name = null;
    public $domain = null;
    public $is_enabled = null;
    public $status = null;
    public $date_created = null;
    public $date_modified = null;
    public $publisher_placements = Array();
    public $publisher_creatives = Array();
    public $publisher_offers = Array();


    public function set_id($id)
    {
        $this->id = $id;
    }

    public function set_network_id($id)
    {
        $this->network_id = $id;
    }

    public function set_name($name)
    {
        $this->name = $name;
    }

    public function set_domain($domain)
    {
        $this->domain = $domain;
    }

    public function set_is_enabled($is_enabled)
    {
        $this->is_enabled = $is_enabled;
    }

    public function set_status($status)
    {
        $this->status = $status;
    }

    public function set_date_created($date)
    {
        $this->date_created = $date;
    }

    public function set_date_modified($date)
    {
        $this->date_modified = $date;
    }

    public function serialize_object($type=SERIALIZE_DATABASE)
    {
        $data = Array(
            'publisher_id' => $this->id,
            'network_id'=>$this->network_id,
            'publisher_name'=>$this->name,
            'publisher_domain'=>$this->domain,
            'publisher_is_enabled'=>$this->is_enabled,
            'publisher_status'=>$this->status,
            'publisher_tcreate'=>$this->date_created,
            'publisher_tmodified'=>$this->date_modified
        );

        switch($type)
        {
            case SERIALIZE_JSON:
                return json_encode($data);
                break;

            case SERIALIZE_DATABASE:
            default:
                return $data;
                break;
        }
    }



    public function __construct($publisher = null)
    {
        /** ===================
         *  LOAD FROM OBJECT ID
         *  ===================
         *  If a publisher ID is provided, lets fetch the data for this particular object
         */


        if($publisher != null && is_numeric($publisher))
        {
            $publisher_id = $publisher;
            $db_conditions = Array();
            $db_conditions['publisher_id'] = $publisher_id;

            $db_columns = array(
                'publisher_id',
                'network_id',
                'publisher_name',
                'publisher_domain',
                'publisher_is_enabled',
                'publisher_status',
                'publisher_tcreate',
                'publisher_tmodified'
            );

            $result = $this->db_retrieve(self::TABLE_PUBLISHER,$db_columns,$db_conditions,null,false);
            if(empty($result[0]))
                throw new Exception('publisher ID ' . $publisher_id . ' is not a valid publisher_id.');



            $this->set_id($publisher_id);
            $this->set_network_id($result[0]['network_id']);
            $this->set_name($result[0]['publisher_name']);
            $this->set_domain($result[0]['publisher_domain']);
            $this->set_is_enabled($result[0]['publisher_is_enabled']);
            $this->set_status($result[0]['publisher_status']);
            $this->set_date_created($result[0]['publisher_tcreate']);
            $this->set_date_modified($result[0]['publisher_tmodified']);


        } elseif(is_array($publisher))
        {
            /** =============================
             *  NEW OBJECT FROM ARRAY OF DATA
             *  =============================
             *  If an array off data is being loaded, then lets go ahead and load them into the object
             */

            foreach($publisher as $key=>$val)
            {
                if($key=='publisher_id')            $this->set_id($val);
                if($key=='network_id')              $this->set_network_id($val);
                if($key=='publisher_name')          $this->set_name($val);
                if($key=='publisher_domain')        $this->set_domain($val);
                if($key=='publisher_is_enabled')    $this->set_is_enabled($val);
                if($key=='publisher_status')        $this->set_status($val);
                if($key=='publisher_tcreate')       $this->set_date_created($val);
                if($key=='publisher_tmodified')     $this->set_date_modified($val);
            }

        }
    }



    public function update_publisher($publisher = null)
    {
        $publisher_id = null;
        $db_columns = Array();

        if($publisher == null && !is_numeric($this->id))
            throw new Exception('You must provide an publisher_id or set an publisher ID to this publisher object.');
        if(!is_array($publisher) && $publisher == null) $publisher = $this->id;

        /**
         *  This method can either take an array of valid publisher table columns
         *  and store it, if it is not provided, it will assume to save all
         *  the properties within the object
         */

        if($publisher != null && is_array($publisher))
        {
            $publisher_id = $this->id;
            $data = $publisher;
            foreach($data as $key=>$val)
            {
                if($key == 'publisher_id') $db_columns[$key] = $val;
                if($key == 'network_id') $db_columns[$key] = $val;
                if($key == 'publisher_name') $db_columns[$key] = $val;
                if($key == 'publisher_domain') $db_columns[$key] = $val;
                if($key == 'publisher_is_enabled') $db_columns[$key] = $val;
                if($key == 'publisher_status') $db_columns[$key] = $val;
                if($key == 'publisher_tcreate') $db_columns[$key] = $val;
                if($key == 'publisher_tmodified') $db_columns[$key] = current_timestamp();
            }
        } elseif($publisher != null && is_numeric($publisher))
        {
            $publisher_id = $publisher;
            $this->id = $publisher_id;
            /**
             *  No array data provided, then lets just save the properties within the object
             */
            if($this->id != null)                   $db_columns['publisher_id'] = $this->id;
            if($this->network_id != null)           $db_columns['network_id'] = $this->network_id;
            if($this->name != null)                 $db_columns['publisher_name'] = $this->name;
            if($this->domain != null)               $db_columns['publisher_domain'] = $this->domain;
            if($this->is_enabled != null)           $db_columns['publisher_is_enabled'] = $this->is_enabled;
            if($this->status != null)               $db_columns['publisher_status'] = $this->status;
                                                    $db_columns['publisher_tmodified'] = current_timestamp();
        }

        if(empty($db_columns))
            throw new Exception('No data provided to update publisher');

        $db_conditions = array('publisher_id'=>$publisher_id);

        try {
            $this->db_update(self::TABLE_PUBLISHER,$db_columns,$db_conditions,false);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
    }


    public function add_publisher($publisher = null)
    {
        /**
         *  $publisher should be a publisher object being passed
         */

        if($publisher instanceof publisher)
        {
            $db_columns =  $publisher->serialize_object();
            if(!isset($db_columns['publisher_id'])) $db_columns['publisher_id'] = $this->id;

        } else {
            throw new Exception('Not a valid publisher object!' . print_r($publisher,true));
        }

        if(isset($db_columns['publisher_id'])) unset($db_columns['publisher_id']);

        try {
            $insert_id = $this->db_create(self::TABLE_PUBLISHER,$db_columns);
            return $insert_id;

        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        return false;
    }

    public function add_offer($offer = null)
    {
        /**
         *  $offer should be a offer object being passed
         */

        if($offer instanceof offer)
        {
            $db_columns =  $offer->serialize_object();
            if(!isset($db_columns['offer_id'])) $db_columns['offer_id'] = $this->id;

        } else {
            throw new Exception('Not a valid offer object!' . print_r($offer,true));
        }

        if(isset($db_columns['offer_id'])) unset($db_columns['offer_id']);

        try {
            $insert_id = $this->db_create(self::TABLE_OFFER,$db_columns);
            return $insert_id;

        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        return false;
    }

    public function add_placement($placement = null)
    {
        /**
         *  $placement should be a placement object being passed
         */

        if($placement instanceof placement)
        {
            $db_columns =  $placement->serialize_object();
            if(!isset($db_columns['placement_id'])) $db_columns['placement_id'] = $this->id;

        } else {
            throw new Exception('Not a valid placement object!' . print_r($placement,true));
        }

        if(isset($db_columns['placement_id'])) unset($db_columns['placement_id']);

        try {
            $insert_id = $this->db_create(self::TABLE_PLACEMENT,$db_columns);
            return $insert_id;

        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        return false;
    }


    public function get_placements($id = null)
    {
        if($id == null)
            $db_conditions['publisher_id'] = $this->id;
        elseif(is_numeric($id))
            $db_conditions['publisher_id'] = $id;

        $db_columns = array(
            'placement_id',
            'network_id',
            'placement_identifier',
            'publisher_id',
            'placement_type',
            'placement_url',
            'placement_status',
            'placement_tcreate',
            'placement_tmodified'
        );

        try {
            $result = $this->db_retrieve(self::TABLE_PLACEMENT,$db_columns,$db_conditions,null,false);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        if(empty($result[0]))
            throw new Exception('No placements found under publisher_id ' . $this->id);

        foreach($result as $item)
        {
            if(isset($_placement)) unset($_placement);
            $_placement = new Placement($item);
            $this->publisher_placements[] = $_placement;
        }

        return $this->publisher_placements;
    }

    public function get_offers($id = null)
    {
        if($id == null)
            $db_conditions['publisher_id'] = $this->id;
        elseif(is_numeric($id))
            $db_conditions['publisher_id'] = $id;

        $db_columns = array(
            'publisher_id',
            'network_id',
            'offer_view_count',
            'offer_click_url',
            'offer_redirect_urls',
            'offer_destination_url',
            'offer_hash',
            'offer_tcreate',
            'offer_tmodified'
        );

        try {
            $result = $this->db_retrieve(self::TABLE_OFFER,$db_columns,$db_conditions,null,false);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        if(empty($result[0]))
            throw new Exception('No offers found under publisher_id ' . $this->id);

        foreach($result as $item)
        {
            if(isset($_offer)) unset($_offer);
            $_offer = new offer($item);
            $this->publisher_offers[] = $_offer;
        }

        return $this->publisher_offers;
    }


    public function get_creatives($id = null)
    {
        if($id == null)
            $db_conditions['publisher_id'] = $this->id;
        elseif(is_numeric($id))
            $db_conditions['publisher_id'] = $id;

        $db_columns = array(
            'creative_id',
            'offer_id',
            'network_id',
            'placement_id',
            'publisher_id',
            'creative_position',
            'creative_category',
            'creative_img',
            'creative_img_url',
            'creative_headline',
            'creative_view_count',
            'creative_tcreate',
            'creative_tmodified'
        );

        try {
            $result = $this->db_retrieve(self::TABLE_PUBLISHER,$db_columns,$db_conditions,null,false);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        if(empty($result[0]))
            throw new Exception('No creatives found under publisher_id ' . $this->id);

        foreach($result as $item)
        {
            if(isset($_creative)) unset($_creative);
            $_creative = new creative($item);
            $this->publisher_creatives[] = $_creative;
        }

        return $this->publisher_creatives;
    }


}







