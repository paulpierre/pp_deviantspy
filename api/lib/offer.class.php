<?php

/** ===============
 *  offer.class.php
 *  ===============
 */

class OfferObject extends Offer {

    public function get_offer_by_hash($hash=null)
    {
        if($hash==null)
            throw new Exception('You must provide an offer hash');

        $db_conditions = Array();
        $db_conditions['offer_hash'] = $hash;

        $db_columns = array(
            'offer_id',
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

        $result = $this->db_retrieve(self::TABLE_OFFER,$db_columns,$db_conditions,null,false);
        if(empty($result[0]))
            return false;

        $offer_object = new Offer($result[0]);
        if ($offer_object instanceof Offer)
            return $offer_object;
        else return false;

    }
}




