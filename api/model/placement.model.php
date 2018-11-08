<?php

/** ===================
 *  placement.model.php
 *  ===================
 */

class Placement extends Database {
    const TABLE_OFFER = 'offer';
    const TABLE_CREATIVE = 'creative';
    const TABLE_PLACEMENT = 'placement';


    public $id = null;
    public $identifier = null;
    public $publisher_id = null;
    public $network_id = null;
    public $type = null;
    public $url = null;
    public $status = null;
    public $date_created = null;
    public $date_modified = null;

    public $placement_creatives = Array();

    public function set_id($id)
    {
        $this->id = $id;
    }

    public function set_identifier($id)
    {
        $this->identifier = $id;
    }

    public function set_publisher_id($id)
    {
        $this->publisher_id = $id;
    }

    public function set_network_id($id)
    {
        $this->network_id = $id;
    }

    public function set_type($type)
    {
        $this->type = $type;
    }

    public function set_url($url)
    {
        $this->url = $url;
    }

    public function set_status($data)
    {
        $this->status = $data;
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
            'placement_id'          => $this->id,
            'placement_identifier'  => $this->identifier,
            'publisher_id'          => $this->publisher_id,
            'network_id'            => $this->network_id,
            'placement_type'        => $this->type,
            'placement_url'         => $this->url,
            'placement_status'      => $this->status,
            'placement_tcreate'     => $this->date_created,
            'placement_tmodified'   => $this->date_modified
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



    public function __construct($placement = null)
    {
        /** ===================
         *  LOAD FROM OBJECT ID
         *  ===================
         *  If an placement ID is provided, lets fetch the data for this particular object
         */
        if($placement != null && is_numeric($placement))
        {
            $placement_id = $placement;
            $db_conditions = Array();
            $db_conditions['placement_id'] = $placement_id;

            $db_columns = array(
                'placement_id',
                'placement_identifier',
                'publisher_id',
                'network_id',
                'placement_type',
                'placement_url',
                'placement_status',
                'placement_tcreate',
                'placement_tmodified'
            );

            $result = $this->db_retrieve(self::TABLE_PLACEMENT,$db_columns,$db_conditions,null,false);
            if(empty($result[0]))
                throw new Exception('placement ID ' . $placement_id . ' is not a valid placement_id.');

            $this->set_id($placement_id);
            $this->set_publisher_id($result[0]['publisher_id']);
            $this->set_network_id($result[0]['network_id']);
            $this->set_identifier($result[0]['placement_identifier']);
            $this->set_type($result[0]['placement_type']);
            $this->set_url($result[0]['placement_url']);
            $this->set_status($result[0]['placement_status']);
            $this->set_date_created($result[0]['placement_tcreate']);
            $this->set_date_modified($result[0]['placement_tmodified']);


        } elseif(is_array($placement))
        {
            /** =============================
             *  NEW OBJECT FROM ARRAY OF DATA
             *  =============================
             *  If an array off data is being loaded, then lets go ahead and load them into the object
             */

            foreach($placement as $key=>$val)
            {
                if($key == 'placement_id')              $this->set_id($val);
                if($key == 'publisher_id')              $this->set_publisher_id($val);
                if($key == 'network_id')                $this->set_network_id($val);
                if($key == 'placement_identifier')      $this->set_identifier($val);
                if($key == 'placement_type')            $this->set_type($val);
                if($key == 'placement_url')             $this->set_url($val);
                if($key == 'placement_status')          $this->set_status($val);
                if($key == 'placement_tcreate')         $this->set_date_created($val);
                if($key == 'placement_tmodified')       $this->set_date_modified($val);
            }
        }
    }



    public function update_placement($placement = null)
    {
        $placement_id = null;
        $db_columns = Array();

        if($placement == null && !is_numeric($this->id))
            throw new Exception('You must provide a placement_id or set a placement ID to this Placement object.');
        if(!is_array($placement) && $placement == null) $placement = $this->id;

        /**
         *  This method can either take an array of valid placement table columns
         *  and store it, if it is not provided, it will assume to save all
         *  the properties within the object
         */

        if($placement != null && is_array($placement))
        {
            $placement_id = $this->id;
            $data = $placement;
            foreach($data as $key=>$val)
            {
                if($key == 'publisher_id') $db_columns[$key] = $val;
                if($key == 'network_id') $db_columns[$key] = $val;
                if($key == 'placement_identifier') $db_columns[$key] = $val;
                if($key == 'placement_type') $db_columns[$key] = $val;
                if($key == 'placement_url') $db_columns[$key] = $val;
                if($key == 'placement_status') $db_columns[$key] = $val;
                if($key == 'placement_tcreate') $db_columns[$key] = $val;
                if($key == 'placement_tmodified') $db_columns[$key] = $val;
            }
        } elseif($placement != null && is_numeric($placement))
        {
            $placement_id = $placement;
            $this->id = $placement_id;
            /**
             *  No array data provided, then lets just save the properties within the object
             */
            if($this->publisher_id != null)         $db_columns['publisher_id'] = $this->publisher_id;
            if($this->network_id != null)           $db_columns['network_id'] = $this->network_id;
            if($this->identifier != null)           $db_columns['placement_identifier'] = $this->identifier;
            if($this->type != null)                 $db_columns['placement_type'] = $this->type;
            if($this->url != null)                  $db_columns['placement_url'] = $this->url;
            if($this->status != null)               $db_columns['placement_status'] = $this->status;
            $db_columns['placement_tmodified'] = current_timestamp();
        }

        if(empty($db_columns))
            throw new Exception('No data provided to update placement');

        $db_conditions = array('placement_id'=>$placement_id);

        try {
        $this->db_update(self::TABLE_PLACEMENT,$db_columns,$db_conditions,false);
            } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
    }


    public function add_creative($creative = null)
    {
        /**
         *  $creative should be a Creative object being passed
         */

        if($creative instanceof Creative)
        {
            $db_columns =  $creative->serialize_object();
            if(!isset($db_columns['placement_id'])) $db_columns['placement_id'] = $this->id;
        } else {
            throw new Exception('Not a valid Creative object!' . print_r($creative,true));
        }

        if(isset($db_columns['creative_id'])) unset($db_columns['creative_id']);

        try {
            $insert_id = $this->db_create(self::TABLE_CREATIVE,$db_columns);
            return $insert_id;

        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        return false;
    }


    public function get_creatives($id = null)
    {
        if($id == null)
            $db_conditions['placement_id'] = $this->id;
        elseif(is_numeric($id))
            $db_conditions['placement_id'] = $id;

        $db_columns = array(
            'creative_id',
            'placement_id',
            'network_id',
            'offer_id',
            'placement_id',
            'publisher_id',
            'creative_position',
            'creative_category',
            'creative_img',
            'creative_img_url',
            'creative_position',
            'creative_headline',
            'creative_view_count',
            'creative_tcreate',
            'creative_tmodified'
        );

        try {

            $result = $this->db_retrieve(self::TABLE_CREATIVE,$db_columns,$db_conditions,null,false);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        if(empty($result[0]))
            throw new Exception('No creatives found under placement_id ' . $this->id);


        foreach($result[0] as $item)
        {
            if(isset($_creative)) unset($_creative);
            $_creative = new Creative($item);
            $this->placement_creatives[] = $_creative;
        }

        return $this->placement_creatives;
    }

}




