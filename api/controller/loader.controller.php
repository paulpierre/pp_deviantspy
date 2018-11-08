<?php
global $controllerID,$controllerObject,$controllerFunction,$controllerData;

/** ========================
 *  loader.controller.php
 *  ========================
 *
 *  api.chartvisual.com/loader/
 *
 */

//exit(print 'cF: ' . $controllerFunction . ' cID: ' . $controllerID . ' cData: '. $controllerData);
switch($controllerFunction)
{
    case 'sync':


        switch($controllerID)
        {
            /** ============================================================
             *  http://api.deviantspy/loader/sync/placementsToPublishers/networkID
             *  ============================================================
             *  Loads publisher ID into placement list
             */
            case 'placementstopublishers':
                $network_id = $controllerData;

                /**
                 *  Lets grab a list of all the publishers and their IDs
                 */

                $db = new Database();
                $q = 'SELECT publisher_id, publisher_name FROM publisher WHERE network_id=' . $network_id;
                $_publishers = $db->db_query($q);

                $q = 'SELECT placement_id, placement_url FROM placement WHERE publisher_id=0 AND network_id=' . $network_id;
                $_placements = $db->db_query($q);

                $publisher_list = Array();
                $placement_list = Array();

                $count_updates = 0;
                $count_orphans = 0;
                $count_errors = 0;


                foreach($_publishers as $item)
                {
                    $publisher_list[$item['publisher_name']] = $item['publisher_id'];
                }

                foreach($_placements as $item)
                {
                    $_url = strpos($item['placement_url'],'://')?$item['placement_url']:'http://'.$item['placement_url'];

                    $_domain = parse_url($_url);
                    $domain = $_domain['host'];
                    $placement_list[$item['placement_id']] = $domain;

                }

                //exit(print_r($publisher_list,true));


                foreach($placement_list as $key=>$val)
                {
                    if(array_key_exists($val,$publisher_list))
                        $publisher_id = $publisher_list[$val];
                    else {
                        $count_orphans++;
                        continue;
                    }
                    $count_updates++;
                    $placement_id = $key;

                    //print 'k: ' . $key . ' id: ' . $val . ' => ' . $publisher_id . PHP_EOL;

                    $db_columns = Array(
                        'publisher_id'=>$publisher_id,
                        'placement_tmodified'=>current_timestamp()
                    );
                    $db_conditions = Array(
                        'placement_id'=>$placement_id
                    );

                    try {
                        $result = $db->db_update('placement',$db_columns,$db_conditions,false);
                    } catch(Exception $e)
                    {
                        $count_errors++;
                        ds_error($e->getMessage());
                    }
                }

                $msg = $count_updates . ' Placements updated. ' . $count_orphans . ' orphaned. ' . $count_errors . ' errors.';

                api_response(array(
                    'code'=> RESPONSE_SUCCESS,
                    'data'=> array('message'=>$msg)
                ));


            break;
        }
    break;


    case 'load':

        switch($controllerID)
        {


            /** =======================================================
             *  http://api.deviantspy/loader/load/fromPlacementUrls/networkID
             *  =======================================================
             *  Loads domains into the publisher list from the placement table
             */
            case 'fromplacementurls':

                $network_id = $controllerData;
                if(!isset($network_id) || !is_numeric($network_id))
                    api_response(array(
                        'code'=> RESPONSE_ERROR,
                        'data'=> array('message'=>'Must specify network_id, "' . $controllerData .  '" is not valid.')
                    ));

                $publisher_is_enabled = true;


                $db = new Database();
                $q = 'SELECT placement_id, placement_url FROM placement WHERE publisher_id=0';
                $result = $db->db_query($q);

                $domain_list = Array();
                $_duplicates = 0;
                $_inserts = 0 ;
                if(!empty($result))
                {
                    foreach($result as $item)
                    {
                       $d= parse_url($item['placement_url']);
                        //exit(print_r($d,true));
                        //$item['placement_url'];
                        $_domain = parse_url((strpos($item['placement_url'],'://'))?$item['placement_url']:'http://'.$item['placement_url']);
                        //exit(print_r($_domain,true));
                        if(!in_array($_domain['host'],$domain_list))
                        {
                            $domain_list[] = $_domain['host'];
                            $db_columns = array(
                                'network_id'=>$network_id,
                                'publisher_name'=>$_domain['host'],
                                'publisher_domain'=>$_domain['host'],
                                'publisher_is_enabled'=>$publisher_is_enabled,
                                'publisher_tmodified'=>current_timestamp(),
                                'publisher_tcreate'=>current_timestamp()
                            );
                            $result = $db->db_create('publisher',$db_columns);
                            if(!$result) $_duplicates++; else {
                                print ('Inserting: ' . $_domain['host'] . ' with publisher_id: ' . print_r($result,true) . PHP_EOL);
                                $_inserts++;
                            }
                        }
                    }
                    print ('Inserted ' . $_inserts. ' publishers, skipped ' . $_duplicates . ' duplicates. Data: '. PHP_EOL . print_r($domain_list,true));

                } else {
                    api_response(array(
                        'code'=> RESPONSE_ERROR,
                        'data'=> array('message'=>'No data returned for method: ' . $controllerID)
                    ));
                }
            break;

            default:
                api_response(array(
                    'code'=> RESPONSE_ERROR,
                    'data'=> array('message'=>'Method: ' . $controllerID . ' does not exist in Function:' . $controllerFunction)
                ));
            break;



        }
    break;

    default:
        api_response(array(
            'code'=> RESPONSE_ERROR,
            'data'=> array('message'=>'Function: ' . $controllerFunction . ' does not exist in Object:' . $controllerObject)
        ));
    break;

}
