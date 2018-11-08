<?php

/** ===================
 *  offer.model.php
 *  ===================
 */

class Offer extends Database {
    const TABLE_OFFER = 'offer';
    const TABLE_CREATIVE = 'creative';
    const TABLE_PLACEMENT = 'placement';


    public $id = null;
    public $publisher_id = null;
    public $network_id = null;
    public $view_count = null;
    public $click_url = null;
    public $redirect_urls = null;
    public $destination_url = null;
    public $domain = null;
    public $hash = null;
    public $date_created = null;
    public $date_modified = null;

    public $offer_creatives = Array();
    public $offer_placements = Array();



    public function set_id($id)
    {
        $this->id = $id;
    }

    public function set_publisher_id($id)
    {
        $this->publisher_id = $id;
    }

    public function set_domain($domain)
    {
        $this->domain = $domain;
    }

    public function set_hash($hash = null)
    {
     //   $_hash = $this->get_hash();
    //    if($_hash) $this->hash = $hash;
    //    else
            $this->hash = $hash;
    }

    public function set_network_id($id)
    {
        $this->network_id = $id;
    }

    public function set_view_count($count)
    {
        $this->view_count = $count;
    }

    public function set_click_url($url)
    {
        $this->click_url = $url;
    }

    public function set_redirect_urls($data)
    {
        $this->redirect_urls = $data;
    }

    public function set_destination_url($url)
    {
        $this->destination_url = $url;
    }

    public function set_date_created($date)
    {
        $this->date_created = $date;
    }

    public function set_date_modified($date)
    {
        $this->date_modified = $date;
    }

    public function get_hash()
    {
        return $this->hash;
        /*
        if($this->destination_url !== null && $this->network_id !== null )
            return md5($this->destination_url . $this->network_id );
        else return false;*/
    }

    public function serialize_object($type=SERIALIZE_DATABASE)
    {
        $data = Array(
            'offer_id' => $this->id,
            'publisher_id'=>$this->publisher_id,
            'network_id'=>$this->network_id,
            'offer_view_count'=>$this->view_count,
            'offer_click_url'=>$this->click_url,
            'offer_redirect_urls'=>(isJson($this->redirect_urls))?$this->redirect_urls:json_encode($this->redirect_urls),
            'offer_destination_url'=>$this->destination_url,
            'offer_domain'=>$this->domain,
            'offer_hash'=>$this->hash,
            'offer_tcreate'=>$this->date_created,
            'offer_tmodified'=>$this->date_modified
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

    public function get_offer_by_hash($offer=null)
    {
        /**
         *  RETURNS OBJECT
         */
        if($offer == null)
            throw new Exception('Offer identifier provided must be an offer_id or Offer Object.');

        if($offer instanceof Offer)
        {
            $offer_hash = $offer->get_hash();
        } elseif(is_string($offer))
        {
            $offer_hash = $offer;
        }
        $db_conditions = Array('offer_hash'=>$offer_hash);


        $db_columns = array(
            'offer_id',
            'publisher_id',
            'network_id',
            'offer_view_count',
            'offer_click_url',
            'offer_redirect_urls',
            'offer_destination_url',
            'offer_domain',
            'offer_hash',
            'offer_tcreate',
            'offer_tmodified'
        );

        $result = $this->db_retrieve(self::TABLE_OFFER,$db_columns,$db_conditions,null,false);
        if(empty($result[0]))
            return false;
        else return new Offer($result[0]);

    }

    public function __construct($offer = null)
    {
        /** ===================
         *  LOAD FROM OBJECT ID
         *  ===================
         *  If an offer ID is provided, lets fetch the data for this particular object
         */


        if($offer != null && is_numeric($offer))
        {
            $offer_id = $offer;
            $db_conditions = Array();
            $db_conditions['offer_id'] = $offer_id;

            $db_columns = array(
                'offer_id',
                'publisher_id',
                'network_id',
                'offer_view_count',
                'offer_click_url',
                'offer_redirect_urls',
                'offer_destination_url',
                'offer_domain',
                'offer_hash',
                'offer_tcreate',
                'offer_tmodified'
            );

            $result = $this->db_retrieve(self::TABLE_OFFER,$db_columns,$db_conditions,null,false);
            if(empty($result[0]))
                throw new Exception('Offer ID ' . $offer_id . ' is not a valid offer_id.');



            $this->set_id($offer_id);
            $this->set_publisher_id($result[0]['publisher_id']);
            $this->set_network_id($result[0]['network_id']);
            $this->set_view_count($result[0]['offer_view_count']);
            $this->set_click_url($result[0]['offer_click_url']);
            $this->set_redirect_urls($result[0]['offer_redirect_urls']);
            $this->set_destination_url($result[0]['offer_destination_url']);
            $this->set_domain($result[0]['offer_domain']);
            $this->set_hash($result[0]['offer_hash']);
            $this->set_date_created($result[0]['offer_tcreate']);
            $this->set_date_modified($result[0]['offer_tmodified']);


        } elseif(is_array($offer))
        {
            /** =============================
             *  NEW OBJECT FROM ARRAY OF DATA
             *  =============================
             *  If an array off data is being loaded, then lets go ahead and load them into the object
             */

            foreach($offer as $key=>$val)
            {
                if($key=='offer_id')                $this->set_id($val);
                if($key=='publisher_id')            $this->set_publisher_id($val);
                if($key=='network_id')              $this->set_network_id($val);
                if($key=='offer_view_count')        $this->set_view_count($val);
                if($key=='offer_click_url')         $this->set_click_url($val);
                if($key=='offer_redirect_urls')     $this->set_redirect_urls($val);
                if($key=='offer_destination_url')   $this->set_destination_url($val);
                if($key=='offer_domain')            $this->set_domain($val);
                if($key=='offer_hash')              $this->set_hash($val);
                if($key=='offer_tcreate')           $this->set_date_created($val);
                if($key=='offer_tmodified')         $this->set_date_modified($val);
            }

        }
    }



    public function update_offer($offer = null)
    {
        $offer_id = null;
        $db_columns = Array();

        if($offer == null && !is_numeric($this->id))
                throw new Exception('You must provide an offer_id or set an offer ID to this Offer object.');
        if(!is_array($offer) && $offer == null) $offer = $this->id;

        /**
         *  This method can either take an array of valid offer table columns
         *  and store it, if it is not provided, it will assume to save all
         *  the properties within the object
         */

        if($offer != null && is_array($offer))
        {
            $offer_id = $this->id;
            $data = $offer;
            foreach($data as $key=>$val)
            {
                if($key == 'publisher_id') $db_columns[$key] = $val;
                if($key == 'network_id') $db_columns[$key] = $val;
                if($key == 'offer_view_count') $db_columns[$key] = $val;
                if($key == 'offer_click_url') $db_columns[$key] = $val;
                if($key == 'offer_redirect_urls') $db_columns[$key] = (isJson($val))?$val:json_encode($val);
                if($key == 'offer_destination_url') $db_columns[$key] = $val;
                if($key == 'offer_domain') $db_columns[$key] = $val;
                if($key == 'offer_tcreate') $db_columns[$key] = $val;
                if($key == 'offer_tmodified') $db_columns[$key] = current_timestamp();
            }
        } elseif($offer != null && is_numeric($offer))
        {
            $offer_id = $offer;
            $this->id = $offer_id;
            /**
             *  No array data provided, then lets just save the properties within the object
             */
            if($this->publisher_id != null)         $db_columns['publisher_id'] = $this->publisher_id;
            if($this->network_id != null)           $db_columns['network_id'] = $this->network_id;
            if($this->view_count != null)           $db_columns['offer_view_count'] = $this->view_count;
            if($this->click_url != null)            $db_columns['offer_click_url'] = $this->click_url;
            if($this->redirect_urls != null)        $db_columns['offer_redirect_urls'] = (isJson($this->redirect_urls))?$this->redirect_urls:json_encode($this->redirect_urls);
            if($this->destination_url != null)      $db_columns['offer_destination_url'] = $this->destination_url;
            if($this->domain != null)               $db_columns['offer_domain'] = $this->domain;
            $db_columns['offer_tmodified'] = current_timestamp();
        }



        if(empty($db_columns))
            throw new Exception('No data provided to update offer');

        $db_conditions = array('offer_id'=>$offer_id);

        try {
        $this->db_update(self::TABLE_OFFER,$db_columns,$db_conditions,false);
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
            if(!isset($db_columns['offer_id'])) $db_columns['offer_id'] = $this->id;

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


    public function add_placement($placement = null)
    {
        /**
         *  $creative should be a Creative object being passed
         */

        if($placement instanceof Placement)
        {
            $db_columns =  $placement->serialize_object();
            if(!isset($db_columns['offer_id'])) $db_columns['offer_id'] = $this->id;


        } else {
            throw new Exception('Not a valid Placement object!' . print_r($placement,true));
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



    public function get_creatives($id = null)
    {
        if($id == null)
            $db_conditions['offer_id'] = $this->id;
        elseif(is_numeric($id))
            $db_conditions['offer_id'] = $id;

        $db_columns = Array(
            'creative_id',
            'offer_id',
            'network_id',
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
            throw new Exception('No creatives found under offer_id ' . $this->id);


        foreach($result as $item)
        {
            if(isset($_creative)) unset($_creative);
            $_creative = new Creative($item);
            $this->offer_creatives[] = $_creative;
        }

        return $this->offer_creatives;
    }

    public function get_placements($id = null)
    {
        if($id == null)
            $db_conditions['offer_id'] = $this->id;
        elseif(is_numeric($id))
            $db_conditions['offer_id'] = $id;

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
            throw new Exception('No placements found under offer_id ' . $this->id);


        foreach($result as $item)
        {
            if(isset($_placement)) unset($_placement);
            $_placement = new Placement($item);
            $this->offer_placements[] = $_placement;
        }

        return $this->offer_placements;
    }

}




