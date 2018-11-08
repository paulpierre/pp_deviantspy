<?php

/** ===================
 *  network.model.php
 *  ===================
 */

class Network extends Database {
    const TABLE_NETWORK = 'network';
    const TABLE_PLACEMENT = 'placement';
    const TABLE_OFFER = 'offer';

    public $id = null;
    public $name = null;
    public $is_enabled = null;
    public $status = null;
    public $date_created = null;
    public $date_modified = null;

    public $network_publishers = Array();
    public $network_networks = Array();
    public $network_offers = Array();
    public $network_creatives = Array();
    public $network_placements = Array();


    public function set_id($id)
    {
        $this->id = $id;
    }

    public function set_name($name)
    {
        $this->name = $name;
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
            'network_id' => $this->id,
            'network_name'=>$this->name,
            'network_is_enabled'=>$this->is_enabled,
            'network_status'=>$this->status,
            'network_tcreate'=>$this->date_created,
            'network_tmodified'=>$this->date_modified
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



    public function __construct($network = null)
    {
        /** ===================
         *  LOAD FROM OBJECT ID
         *  ===================
         *  If a network ID is provided, lets fetch the data for this particular object
         */


        if($network != null && is_numeric($network))
        {
            $network_id = $network;
            $db_conditions = Array();
            $db_conditions['network_id'] = $network_id;

            $db_columns = array(
                'network_id',
                'network_name',
                'network_is_enabled',
                'network_status',
                'network_tcreate',
                'network_tmodified'
            );

            $result = $this->db_retrieve(self::TABLE_NETWORK,$db_columns,$db_conditions,null,false);
            if(empty($result[0]))
                throw new Exception('network ID ' . $network_id . ' is not a valid network_id.');

            $this->set_id($network_id);
            $this->set_name($result[0]['network_name']);
            $this->set_is_enabled($result[0]['network_is_enabled']);
            $this->set_status($result[0]['network_status']);
            $this->set_date_created($result[0]['network_tcreate']);
            $this->set_date_modified($result[0]['network_tmodified']);


        } elseif(is_array($network))
        {
            /** =============================
             *  NEW OBJECT FROM ARRAY OF DATA
             *  =============================
             *  If an array off data is being loaded, then lets go ahead and load them into the object
             */

            foreach($network as $key=>$val)
            {
                if($key=='network_id')            $this->set_id($val);
                if($key=='network_name')          $this->set_name($val);
                if($key=='network_is_enabled')    $this->set_is_enabled($val);
                if($key=='network_status')        $this->set_status($val);
                if($key=='network_tcreate')       $this->set_date_created($val);
                if($key=='network_tmodified')     $this->set_date_modified($val);
            }

        }
    }



    public function update_network($network = null)
    {
        $network_id = null;
        $db_columns = Array();

        if($network == null && !is_numeric($this->id))
            throw new Exception('You must provide an network_id or set an network ID to this network object.');
        if(!is_array($network) && $network == null) $network = $this->id;

        /**
         *  This method can either take an array of valid network table columns
         *  and store it, if it is not provided, it will assume to save all
         *  the properties within the object
         */

        if($network != null && is_array($network))
        {
            $network_id = $this->id;
            $data = $network;
            foreach($data as $key=>$val)
            {
                if($key == 'network_id') $db_columns[$key] = $val;
                if($key == 'network_name') $db_columns[$key] = $val;
                if($key == 'network_is_enabled') $db_columns[$key] = $val;
                if($key == 'network_status') $db_columns[$key] = $val;
                if($key == 'network_tcreate') $db_columns[$key] = $val;
                if($key == 'network_tmodified') $db_columns[$key] = current_timestamp();
            }
        } elseif($network != null && is_numeric($network))
        {

            $network_id = $network;
            $this->id = $network_id;
            /**
             *  No array data provided, then lets just save the properties within the object
             */
            if($this->id != null)                   $db_columns['network_id'] = $this->id;
            if($this->name != null)                 $db_columns['network_name'] = $this->name;
            if($this->is_enabled != null)           $db_columns['network_is_enabled'] = $this->is_enabled;
            if($this->status != null)               $db_columns['network_status'] = $this->status;
            $db_columns['network_tmodified'] = current_timestamp();
        }

        if(empty($db_columns))
            throw new Exception('No data provided to update network');

        $db_conditions = array('network_id'=>$network_id);

        try {
            $this->db_update(self::TABLE_NETWORK,$db_columns,$db_conditions,false);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
    }


    public function add_network($network = null)
    {
        /**
         *  $network should be a network object being passed
         */

        if($network instanceof network)
        {
            $db_columns =  $network->serialize_object();
            if(!isset($db_columns['network_id'])) $db_columns['network_id'] = $this->id;

        } else {
            throw new Exception('Not a valid network object!' . print_r($network,true));
        }

        if(isset($db_columns['network_id'])) unset($db_columns['network_id']);

        try {
            $insert_id = $this->db_create(self::TABLE_NETWORK,$db_columns);
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

    public function get_networks($network_id=false,$include_disabled=false)
    {
        $db_conditions = Array();
        if(!$include_disabled)
            $db_conditions['network_is_enabled'] = 1;
        if($network_id)
            $db_conditions['network_id'] = $network_id;

        $db_columns = Array(
            'network_id',
            'network_name',
            'network_is_enabled',
            'network_status',
            'network_tcreate',
            'network_tmodified'
        );

        try {
            $result = $this->db_retrieve(self::TABLE_NETWORK,$db_columns,$db_conditions,null,false);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        if(empty($result[0]))
            throw new Exception('No networks found under network_id ' . $this->id);


        foreach($result as $item)
        {
            if(isset($_network)) unset($_network);
            $_network = new network($item);
            $this->network_networks[] = $_network;
        }

        return $this->network_networks;
    }

    public function get_placements($id = null,$placement_range=false)
    {



        $q = 'SELECT placement_id, placement_identifier, publisher_id, network_id, placement_type, placement_url, placement_status, placement_tcreate, placement_tmodified FROM placement ';


        if($id == null)
            $q .= 'WHERE network_id=' . $this->id;
        elseif(is_numeric($id))
            $q .= 'WHERE network_id=' . $id;

        if($placement_range && is_numeric($placement_range[0]) && is_numeric($placement_range[1]) && ($placement_range[0] <= $placement_range[1]))
        {
            $range_start = $placement_range[0];
            $range_end = $placement_range[1];

            if(strpos($q,'WHERE'))
                $q .= ' AND placement_id BETWEEN ' . $range_start . ' AND ' . $range_end;
            else
                $q .= ' WHERE placement_id BETWEEN ' . $range_start . ' AND ' . $range_end;
        }


        try {
            $result = $this->db_query($q);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        if(empty($result[0]))
        {
            throw new Exception('No placements found under network_id ' . $this->id);
        }

        foreach($result as $item)
        {
            if(isset($_placement)) unset($_placement);
            $_placement = new Placement($item);
            $this->network_placements[] = $_placement;
        }

        return $this->network_placements;
    }

    public function get_offers($id = null)
    {
        if($id == null)
            $db_conditions['network_id'] = $this->id;
        elseif(is_numeric($id))
            $db_conditions['network_id'] = $id;

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
            throw new Exception('No offers found under network_id ' . $this->id);

        foreach($result as $item)
        {
            if(isset($_offer)) unset($_offer);
            $_offer = new offer($item);
            $this->network_offers[] = $_offer;
        }

        return $this->network_offers;
    }


    public function get_creatives($id = null)
    {
        if($id == null)
            $db_conditions['network_id'] = $this->id;
        elseif(is_numeric($id))
            $db_conditions['network_id'] = $id;

        $db_columns = array(
            'offer_id',
            'network_id',
            'publisher_id',
            'placement_id',
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
            $result = $this->db_retrieve(self::TABLE_NETWORK,$db_columns,$db_conditions,null,false);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        if(empty($result[0]))
            throw new Exception('No creatives found under network_id ' . $this->id);

        foreach($result as $item)
        {
            if(isset($_creative)) unset($_creative);
            $_creative = new creative($item);
            $this->network_creatives[] = $_creative;
        }

        return $this->network_creatives;
    }


}







