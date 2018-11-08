<?php
global $controllerID,$controllerObject,$controllerFunction,$controllerData;

/** ====================
 *  crawl.controller.php
 *  ====================
 *
 *  api.deviantspy/crawl/<network_id>/<geo_id or country code>/<range>
 *
 */




switch($controllerFunction)
{
    default:

        if(!isset($controllerData) || !isset($controllerID))
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'You must provide a network_id and geo_id / country_code'
                )
            ));

        $geo_country = $controllerID;

        $network_id = $controllerFunction;


        $str_range = $controllerData;
        if(isset($str_range))
        {
            if(!strpos($str_range,':'))
                api_response(array(
                    'code'=> RESPONSE_ERROR,
                    'data'=> array(
                        'message'=>'Range not properly formatted.'
                    )
                ));
            else
                $placement_range = explode(':',$str_range);
        }

        $geo_instance = new Geo();
        $proxy_list = $geo_instance->get_proxies($geo_country);
        if(!$proxy_list) $proxy_list = null;

        //exit(print_r($proxies));


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
        $placements = $network_instance->get_placements($network_id,$placement_range);

        if(!$placements)
        {
            if(isset($placements)) unset($placements); //release object
            api_response(array(
                'code'=> RESPONSE_ERROR,
                'data'=> array(
                    'message'=>'Internal error retrieving placements list'
                )
            ));
        }

        /**
         *  Lets create the Crawler object
         */

        $crawler_instance = new Crawler();

        /**
         *  Now we interate through the existing placement objects
         */

        error_log('START_CRAWL Placements:'.count($placements).' Network ID: '. $network_id . ' Country: '. $geo_country . '  Start time:' . current_timestamp());



        /** ===========================================================
         *  Run through all the placements (widgets) within the network
         *  ===========================================================
         */

        $creative_object = new CreativeObject();
        $offer_object = new OfferObject();

        $crawl_count = 0;
        $crawl_success = 0;
        $crawl_new = 0;
        if(CRAWL_LIMIT)
            ds_error('Crawl limited to:' . CRAWL_LIMIT);

        foreach($placements as $placement)
        {

            if(CRAWL_LIMIT)
            {
                if($crawl_count >= CRAWL_LIMIT)
                    exit('Crawl halted at count #: ' . $crawl_count);
            }
            $crawl_count++;

            /**
             *  Let's begin crawling these placements for new offers and creatives
             */

            if(SCRAPE_LIVE)
            {


                $url = $crawler_instance->build_url($placement);

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
                $result = $crawler_instance->fetch($url);
                //error_log($result);

                //$crawler_instance = new Crawler();
                $crawler_instance->crawl_url = $url;


                $raw = $crawler_instance->get_network_raw_content($result,$network_id);
                /**
                 *  This means we weren't able to pull the data, so lets bail
                 */
                if(!$raw) {
                    ds_error("Error: get_network_raw_content() => placement ID: " . $placement->id . ' Identifier: '. $placement->identifier . ' url:' . $placement->url);
                    continue;
                }

                if(SAVE_SCRAPE)
                {

                    /**
                     *  Now that we have the data, lets write it to a file
                     */

                    ds_error("Writing to:". TMP_PATH . $network_id .'_' . $placement->identifier);
                    file_put_contents(TMP_PATH . $network_id . '_' . $placement->identifier, $raw); //lets save the raw content
                }
            }
            else
                $raw = file_get_contents(TMP_PATH.$placement->identifier);




            /**
             *  Lets clean up and parse the data with XPath
             */
            $result = $crawler_instance->parse_network_content($raw,$network_id);
            if(!$result)
            {
                continue;
            }

            /**
             *  Result should be the following array keys:
             *  title, url, click_url, img, redirect, position
             */

            //error_log('Parsed data:' . print_r($result,true));


            /** ===========================================================
             *  Run through all the creatives within the specific placement
             *  ===========================================================
             */

            foreach($result as $creative)
            {


                $scrape_start = current_timestamp();

                /**
                 *  Lets determine if the placements exist and insert/update the offers and creatives
                 */

                /** ----------------------------
                 *  DATA TO UPDATE OFFER OBJECT
                 *  ----------------------------
                 */

                $offer_click_url        = $creative['click_url'];
                $offer_destination_url  = $creative['url'];
                $offer_redirect_urls    = $creative['redirects'];
                if(!empty($creative['url']))
                {
                    $str_domain             = parse_url($creative['url']);
                    $offer_domain           = $str_domain['host'];

                }
                $headline               = $creative['title'];

                /** -------------------
                 *  GRAB PLACEMENT DATA
                 *  -------------------
                 */

                $placement_id           = $placement->id;
                $placement_identifier   = $placement->identifier;
                $publisher_id           = $placement->publisher_id;


                /** ---------------
                 *  GRAB OFFER DATA
                 *  ---------------
                 */

                if(
                       !isset($offer_click_url)
                    || empty($offer_destination_url)
                    || !isset($offer_redirect_urls)
                    || !isset($placement_id)
                    || !isset($placement_identifier)
                    || !isset($publisher_id)
                    || empty($headline)
                    || $placement_identifier == null
                    || $placement_id == null
                    || $publisher_id == null
                )
                {
                    $error_debug = Array(
                        'offer_click_url'       => $offer_click_url,
                        'offer_destination_url' => $offer_destination_url,
                        'offer_domain'          => $offer_domain,
                        'offer_redirect_urls'   => $offer_redirect_urls,
                        'placement_id'          => $placement_id,
                        'placement_identifier'  => $placement_identifier,
                        'publisher_id'          => $publisher_id
                    );

                    ds_error('Scraping error: ' . print_r($error_debug,true));
                    continue;
                }



                $offer_hash_current = md5($offer_domain . $headline . $network_id);
                ds_error('Offer hash: ' . $offer_domain . ' + ' . $headline . ' + ' . $network_id);

                $offer_instance = $offer_object->get_offer_by_hash($offer_hash_current);


                if(!$offer_instance)
                {
                    /**
                     *  Offer instance does NOT exist, if so let's insert it!
                     */

                    unset($offer_instance);

                    $offer_instance = new Offer(
                        Array(
                            'publisher_id'          => $publisher_id,
                            'network_id'            => $network_id,
                            'offer_view_count'      => 1,
                            'offer_click_url'       => $offer_click_url,
                            'offer_redirect_urls'   => $offer_redirect_urls,
                            'offer_domain'          => $offer_domain,
                            'offer_destination_url' => $offer_destination_url,
                            'offer_hash'            => $offer_hash_current,
                            'offer_tcreate'         => current_timestamp(),
                            'offer_tmodified'       => current_timestamp()
                        ));

                    $offer_id = $network_instance->add_offer($offer_instance);
                }
                else {
                    $offer_id = $offer_instance->id;
                    $offer_view_count = $offer_instance->view_count;
                    $offer_view_count++;
                    $offer_instance->set_view_count($offer_view_count);
                    $offer_instance->update_offer();
                }


                /** --------------------------
                 *  GRAB SCRAPED CREATIVE DATA
                 *  --------------------------
                 */
		var_dump($creative);
                $creative_position = $creative['position'];
                $creative_title = $creative['title'];
                $creative_img_url = $creative['img'];

                /** =======================
                 *  Creative Object Exists?
                 *  =======================
                 *  TRUE: Update redirects, etc.
                 *  FALSE: Create a new record
                 */
                $creative_hash_current = md5($creative_img_url . $creative_title);

                $creative_instance = $creative_object->get_creative_by_hash($creative_hash_current);



                if(!$creative_instance)
                {
                    /**
                     *  Creative instance does NOT exist, if so let's insert it!
                     */
                    unset($creative_instance);
                    $creative_instance = new Creative(Array(
                        'offer_id'=>$offer_id,
                        'network_id'=>$network_id,
                        'publisher_id'=>$publisher_id,
                        'creative_hash'=>$creative_hash_current,
                        'creative_category'=>'',
                        'creative_img'=>'',
                        'creative_img_url'=>$creative_img_url,
                        'creative_headline'=>$creative_title,
                        'creative_position'=>$creative_position,
                        'creative_view_count'=>1,
                        'creative_tcreate'=>current_timestamp(),
                        'creative_tmodified'=>current_timestamp()
                    ));

                    $creative_id = $offer_instance->add_creative($creative_instance);
                    $crawl_new++;
                } else {
                    $creative_id = $creative_instance->id;
                    $creative_view_count = $creative_instance->view_count;
                    $creative_view_count++;
                    $creative_instance->set_view_count($creative_view_count);
                    $creative_instance->update_creative();
                }




                /** -----------------------
                 *  LETS ADD A CRAWL RECORD
                 *  -----------------------
                 */

                $scrape_finish = current_timestamp();
                $scrape_instance = new Scrape(Array(
                    'publisher_id'=>$publisher_id,
                    'network_id'=>$network_id,
                    'offer_id'=>$offer_id,
                    'placement_id'=>$placement_id,
                    'creative_id'=>$creative_id,
                    'creative_position'=>$creative_position,
                    'geo_id'=>$geo_id,
                    'agent_id'=>$agent_id,
                    'scrape_tstart'=>$scrape_start,
                    'scrape_tfinish'=>$scrape_finish,
                    'scrape_tmodified'=>current_timestamp(),
                    'scrape_tcreate'=>current_timestamp()
                ));

                $scrape_id = $scrape_instance->add_scrape();

                unset($offer_instance);
                unset($creative_instance);
                unset($scrape_instance);


                ds_error('New scrape record: ' . $scrape_id);

            }

            $crawl_success++;



            //exit(print_r($result,true));

        }


        unset($crawler_instance);
        unset($creative_object);
        unset($offer_object);
        error_log('END_CRAWL Attempts: '.$crawl_count. ' Success: '. $crawl_success  . ' New creatives found: ' .$crawl_new .' Network ID: '. $network_id . ' Country: '. $geo_country .  ' End time:' . current_timestamp());

        break;

}
