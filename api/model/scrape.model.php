<?php

/** ================
 *  scrape.model.php
 *  ================
 */

class Scrape extends Database {
    const TABLE_SCRAPE = 'scrape';

    public $id = null;
    public $publisher_id = null;
    public $network_id = null;
    public $offer_id = null;
    public $placement_id = null;
    public $creative_id = null;
    public $creative_position = null;
    public $geo_id = null;
    public $agent_id = null;
    public $date_started = null;
    public $date_finished = null;


    public $date_created = null;
    public $date_modified = null;

    public function set_id($id)
    {
        $this->id = $id;
    }

    public function set_publisher_id($id)
    {
        $this->publisher_id = $id;
    }

    public function set_network_id ($id)
    {
        $this->network_id = $id;
    }

    public function set_offer_id ($id)
    {
        $this->offer_id = $id;
    }

    public function set_placement_id ($id)
    {
        $this->placement_id = $id;
    }

    public function set_creative_id ($id)
    {
        $this->creative_id = $id;
    }

    public function set_creative_position ($position)
    {
        $this->creative_position = $position;
    }

    public function set_geo_id ($id)
    {
        $this->geo_id = $id;
    }

    public function set_agent_id ($id)
    {
        $this->agent_id = $id;
    }

    public function set_date_started ($date)
    {
        $this->date_started = $date;
    }

    public function set_date_finished ($date)
    {
        $this->date_finished = $date;
    }


    public function set_date_modified ($date)
    {
        $this->date_modified = $date;
    }

    public function set_date_created ($date)
    {
        $this->date_created = $date;
    }

    public function __construct($scrape = null)
    {
        /**
         *  If a scrape ID is provided, lets fetch the data for this particular object
         */


        if($scrape != null && is_numeric($scrape))
        {
            $scrape_id = $scrape;
            $db_conditions = Array();
            $db_conditions['scrape_id'] = $scrape_id;

            $db_columns = array(
                'scrape_id',
                'publisher_id',
                'network_id',
                'offer_id',
                'placement_id',
                'creative_id',
                'creative_position',
                'geo_id',
                'agent_id',
                'scrape_tstart',
                'scrape_tfinish',
                'scrape_tmodified',
                'scrape_tcreated'
            );

            $result = $this->db_retrieve(self::TABLE_SCRAPE,$db_columns,$db_conditions,null,false);
            if(empty($result[0]))
                throw new Exception('Scrape ID ' . $scrape_id . ' is not a valid scrape_id.');

            $this->set_id($scrape_id);
            $this->set_offer_id($result[0]['offer_id']);
            $this->set_network_id($result[0]['network_id']);
            $this->set_publisher_id($result[0]['publisher_id']);
            $this->set_placement_id($result[0]['placement_id']);
            $this->set_creative_id($result[0]['creative_id']);
            $this->set_creative_position($result[0]['creative_position']);
            $this->set_geo_id($result[0]['geo_id']);
            $this->set_agent_id($result[0]['agent_id']);
            $this->set_date_started($result[0]['scrape_tstart']);
            $this->set_date_finished($result[0]['scrape_tfinish']);
            $this->set_date_created($result[0]['scrape_tcreate']);
            $this->set_date_modified($result[0]['scrape_tmodified']);


        } elseif(is_array($scrape))
        {
            /**
             *  If an array off data is being loaded, then lets go ahead and load them into the object
             */

            foreach($scrape as $key=>$val)
            {
                if($key == 'scrape_id')             $this->set_id($val);
                if($key == 'network_id')            $this->set_network_id($val);
                if($key == 'publisher_id')          $this->set_publisher_id($val);
                if($key == 'offer_id')              $this->set_offer_id($val);
                if($key == 'placement_id')          $this->set_placement_id($val);
                if($key == 'creative_id')           $this->set_creative_id($val);
                if($key == 'creative_position')     $this->set_creative_position($val);
                if($key == 'geo_id')                $this->set_geo_id($val);
                if($key == 'agent_id')              $this->set_agent_id($val);
                if($key == 'scrape_tstart')         $this->set_date_started($val);
                if($key == 'scrape_tfinish')        $this->set_date_finished($val);
                if($key == 'scrape_tcreate')        $this->set_date_created($val);
                if($key == 'scrape_tmodified')      $this->set_date_modified($val);
            }
        }
    }

    public function serialize_object($type=SERIALIZE_DATABASE)
    {
        $data = Array(
            'scrape_id' =>$this->id,
            'publisher_id' =>$this->publisher_id,
            'network_id' =>$this->network_id,
            'offer_id' =>$this->offer_id,
            'placement_id' =>$this->placement_id,
            'creative_id' =>$this->creative_id,
            'creative_position' =>$this->creative_position,
            'geo_id' =>$this->geo_id,
            'agent_id' =>$this->agent_id,
            'scrape_tstart' =>$this->date_started,
            'scrape_tfinish' =>$this->date_finished,
            'scrape_tmodified' =>$this->date_modified,
            'scrape_tcreate' =>$this->date_created
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



    public function add_scrape()
    {
        /**
         *  $scrape should be a Scrape object being passed
         */

        $db_columns = $this->serialize_object();

        try {
            $insert_id = $this->db_create(self::TABLE_SCRAPE,$db_columns);
            return $insert_id;

        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
        return false;
    }




}


