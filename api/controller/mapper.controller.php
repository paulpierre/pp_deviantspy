<?php
global $controllerID,$controllerObject,$controllerFunction,$controllerData;

/** =====================
 *  mapper.controller.php
 *  =====================
 *
 *  api.deviantspy/mapper/<network_id>/<start_id>/<finish_id>
 *
 *  Maps out an entire network. For now, RevContent
 *
 */



switch($controllerFunction)
{

    case NETWORK_REVCONTENT:

        $geo_country = 'us';
        $network_id = intval($controllerFunction);

        $start_id = $controllerID;
        $finish_id = $controllerData;



        $geo_instance = new Geo();
        $proxy_list = $geo_instance->get_proxies($geo_country);
        if(!$proxy_list) $proxy_list = null;


        /**
         *  Lets grab all the placements from Revcontent
         */

        try {
            $network_instance = new Network($network_id); }
        catch (Exception $e) {
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'You must provide a valid network ID'
                )
            ));
        }


        $geo_id = 0;
        $agent_id = 0;

        $network_id = $network_instance->id;

        /**
         *  Lets create the Crawler object
         */

        $crawler_instance = new Crawler();

        /**
         *  Now we interate through the existing placement objects
         */

        ds_error('Scanning through '. ((intval($finish_id) +1) - intval($start_id)) .' widget IDs.');

        /** ===========================================================
         *  Run through all the placements (widgets) within the network
         *  ===========================================================
         */




        $db = new Database();

        $crawl_count = 0;
        if(CRAWL_LIMIT)
            ds_error('Scan limited to:' . CRAWL_LIMIT);

        for($i=$start_id;$i<=$finish_id;$i++)
        {
            ds_error('scanning widget ID: ' . $i);

            if(CRAWL_LIMIT)
            {
                if($crawl_count >= CRAWL_LIMIT)
                    exit('Scan halted at count #: ' . $crawl_count);
                else
                    $crawl_count++;

            }

            $screen_width = 1401;
            $url = 'http://trends.revcontent.com/serve.js.php?w=' . $i .'&t=rc_32591&c=' . time() .'&width=' . $screen_width . '&referer=';

            if($proxy_list)
            {
                $geo_id = $proxy_list[0]['geo_id'];
                $count = count($proxy_list);
                $id = rand(0,$count-1);
                $proxy = Array('geo_ip'=>$proxy_list[$id]['geo_ip'],'geo_port'=>$proxy_list[$id]['geo_port']);
            } else {
                $proxy = null;
                $geo_id = 0;
            }

            $crawler_instance->proxy_list = $proxy;
            $raw = $crawler_instance->fetch($url);
	    echo "damn man";
	    var_dump($raw);
	    echo "\n damn man";
            //$crawler_instance = new Crawler();
            $crawler_instance->crawl_url = $url;
	    var_dump($crawler_instance);

            $res = $crawler_instance->get_network_raw_content($raw,$network_id);

            $nmap_status = 1;
            $q = 'REPLACE INTO nmap SET placement_identifier ="' . $i .'" , network_id =' . $controllerFunction . ', nmap_status=' . $nmap_status . ',nmap_tmodified="'. current_timestamp().'";';

            /**
             *  This means we weren't able to pull the data, so lets bail
             */
	    var_dump($res);
            //ds_error('Raw: ' . $raw);
            if(!$res) {
                ds_error('Error mapping widgetID: ' . $i . ' url: ' . $url);
                $nmap_status = 2;
                $q = 'REPLACE INTO nmap SET placement_identifier ="' . $i .'" , network_id =' . $controllerFunction . ', nmap_status=' . $nmap_status . ',nmap_tmodified="'. current_timestamp().'";';
                $result = $db->db_query($q,DATABASE_NAME);
                //ds_error($result);
                continue;
            }

            $result = $db->db_query($q,DATABASE_NAME);

        }

        unset($crawler_instance);
        unset($db);

        break;

}
