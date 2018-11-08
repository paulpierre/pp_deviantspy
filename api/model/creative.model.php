<?php

/** ===================
 *  creative.model.php
 *  ===================
 */

class Creative extends Database {
    const TABLE_OFFER = 'offer';
    const TABLE_CREATIVE = 'creative';
    const TABLE_PLACEMENT = 'placement';

    public $id = null;
    public $offer_id = null;
    public $publisher_id = null;
    public $network_id = null;
    public $hash = null;
    public $category = null;
    public $image = null;
    public $image_url = null;
    public $position = null;
    public $headline = null;
    public $view_count = null;
    public $date_created = null;
    public $date_modified = null;

    public function serialize_object($type=SERIALIZE_DATABASE)
    {
        $data = Array(
            'creative_id'           =>$this->id,
            'offer_id'              =>$this->offer_id,
            'network_id'            =>$this->network_id,
            'publisher_id'          =>$this->publisher_id,
            'creative_hash'         =>$this->hash,
            'creative_category'     =>$this->category,
            'creative_img'          =>$this->image,
            'creative_img_url'      =>$this->image_url,
            'creative_position'     =>$this->position,
            'creative_headline'     =>$this->headline,
            'creative_view_count'   =>$this->view_count,
            'creative_tcreate'      =>$this->date_created,
            'creative_tmodified'    =>$this->date_modified
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

    public function set_id($id)
    {
        $this->id = $id;
    }

    public function set_offer_id($id)
    {
        $this->offer_id = $id;
    }


    public function set_publisher_id($id)
    {
        $this->publisher_id = $id;
    }



    public function set_network_id($id)
    {
        $this->network_id = $id;
    }

    public function set_hash($hash=null)
    {
        if($hash==null)
            $hash = $this->get_hash();
        $this->hash = $hash;
    }

    public function get_hash()
    {
        if($this->image_url !== null && $this->headline !== null)
            return md5($this->image_url . $this->headline);
        else return false;
    }

    public function set_category($category)
    {
        $this->category = $category;
    }

    public function set_image($image)
    {
        $this->image = $image;
    }

    public function set_image_url($url)
    {
        $this->image_url = $url;
    }

    public function set_position($position)
    {
        $this->position = $position;
    }

    public function set_headline($headline)
    {
        $this->headline = $headline;
    }

    public function set_view_count($count)
    {
        $this->view_count = $count;
    }

    public function set_date_created($date)
    {
        $this->date_created = $date;
    }

    public function set_date_modified($date)
    {
        $this->date_modified = $date;
    }


    public function __construct($creative = null)
    {
        /**
         *  If a creative ID is provided, lets fetch the data for this particular object
         */


        if($creative != null && is_numeric($creative))
        {
            $creative_id = $creative;
            $db_conditions = Array();
            $db_conditions['creative_id'] = $creative_id;

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
                throw new Exception('Creative ID ' . $creative_id . ' is not a valid creative_id.');

            $this->set_id($creative_id);
            $this->set_offer_id($result[0]['offer_id']);
            $this->set_network_id($result[0]['network_id']);
            $this->set_publisher_id($result[0]['publisher_id']);

            $this->set_hash($result[0]['creative_hash']);
            $this->set_category($result[0]['creative_category']);
            $this->set_image($result[0]['creative_img']);
            $this->set_image_url($result[0]['creative_img_url']);
            $this->set_position($result[0]['creative_position']);
            $this->set_headline($result[0]['creative_headline']);

            $this->set_view_count($result[0]['creative_view_count']);
            $this->set_date_created($result[0]['creative_tcreate']);
            $this->set_date_modified($result[0]['creative_tmodified']);


        } elseif(is_array($creative))
        {
            /**
             *  If an array off data is being loaded, then lets go ahead and load them into the object
             */

            foreach($creative as $key=>$val)
            {
                if($key == 'creative_id')                $this->set_id($val);

                if($key == 'offer_id')                $this->set_offer_id($val);
                if($key == 'network_id')              $this->set_network_id($val);
                if($key == 'publisher_id')            $this->set_publisher_id($val);
                if($key == 'creative_hash')           $this->set_hash($val);
                if($key == 'creative_category')       $this->set_category($val);
                if($key == 'creative_img')            $this->set_image($val);
                if($key == 'creative_img_url')        $this->set_image_url($val);
                if($key == 'creative_position')       $this->set_position($val);
                if($key == 'creative_headline')       $this->set_headline($val);
                if($key == 'creative_view_count')     $this->set_view_count($val);
                if($key == 'creative_tcreate')        $this->set_date_created($val);
                if($key == 'creative_tmodified')      $this->set_date_modified($val);
            }
        }
    }



    public function update_creative($creative = null)
    {

        $db_columns = Array();
        $creative_id = null;

        if($creative == null && !is_numeric($this->id))
            throw new Exception('You must provide a creative_id or set a creative ID to this Creative object.');
        if(!is_array($creative) && $creative == null) $creative = $this->id;

        /**
         *  This method can either take an array of valid creative table columns
         *  and store it, if it is not provided, it will assume to save all
         *  the properties within the object
         */

        if($creative != null && is_array($creative))
        {
            $creative_id = $this->id;
            $data = $creative;
            foreach($data as $key=>$val)
            {
                if($key == 'offer_id')            $db_columns[$key] = $val;
                if($key == 'network_id')          $db_columns[$key] = $val;
                if($key == 'publisher_id')        $db_columns[$key] = $val;

                if($key == 'creative_hash')       $db_columns[$key] = $val;
                if($key == 'creative_category')   $db_columns[$key] = $val;
                if($key == 'creative_img')        $db_columns[$key] = $val;
                if($key == 'creative_img_url')    $db_columns[$key] = $val;
                if($key == 'creative_position')   $db_columns[$key] = $val;
                if($key == 'creative_headline')   $db_columns[$key] = $val;
                if($key == 'creative_view_count') $db_columns[$key] = $val;

                if($key == 'creative_tcreate')    $db_columns[$key] = $val;
                if($key == 'creative_tmodified')  $db_columns[$key] = current_timestamp();
            }
        } elseif($creative != null && is_numeric($creative))
        {
            $creative_id = $creative;
            $this->id = $creative_id;
            /**
             *  No array data provided, then lets just save the properties within the object
             */
            if($this->offer_id != null)             $db_columns['offer_id']             = $this->offer_id;
            if($this->network_id != null)           $db_columns['network_id']           = $this->network_id;
            if($this->publisher_id != null)         $db_columns['publisher_id']         = $this->publisher_id;
            if($this->hash != null)             $db_columns['creative_hash']    = $this->hash;
            if($this->category != null)             $db_columns['creative_category']    = $this->category;
            if($this->image != null)                $db_columns['creative_img']         = $this->image;
            if($this->image_url != null)            $db_columns['creative_img_url']     = $this->image_url;
            if($this->position != null)            $db_columns['creative_position']     = $this->position;
            if($this->headline != null)             $db_columns['creative_headline']    = $this->headline;
            if($this->view_count != null)           $db_columns['creative_view_count']  = $this->view_count;

            $db_columns['creative_tmodified'] = current_timestamp();
        }

        if(empty($db_columns))
            throw new Exception('No data provided to update creative');

        $db_conditions = array('creative_id'=>$creative_id);
        try {
            $this->db_update(self::TABLE_CREATIVE,$db_columns,$db_conditions,false);
        } catch(Exception $e) {
            error_log('Error'. $e->getCode() .': '. $e->getMessage());
        }
    }




}




