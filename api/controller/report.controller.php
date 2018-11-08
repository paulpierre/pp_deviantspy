<?php
global $controllerID,$controllerObject,$controllerFunction,$controllerData;
//header('Content-Type: text/html; charset=iso-8859-1');

error_log(print_r($_POST,true));

//exit();


/** =====================
 *  report.controller.php
 *  =====================
 *
 *  http://api.deviantspy.com/report/
 *
 *  POST DATA:
 *      <country>
 *      <network>
 *      <report>
 *          "widget" - requires $_POST['offer_url'] to be set
 *          "creative" (default) - displays information on all the creatives found in the system
 */

    if(isset($_POST['reset_cache']) && intval($_POST['reset_cache'])==1)
    {
        ds_error('cache reset initiated!');
        \phpFastCache\CacheManager::setup(array("path" => TMP_PATH));
        \phpFastCache\CacheManager::CachingMethod("phpfastcache");

        /**
         *  FLUSH CACHE
         */
        $cache = \phpFastCache\CacheManager::getInstance(); $cache->clean();

    }


$db = new Database();

$geo = '';
if(!is_numeric($_POST['geo']) && $_POST['geo'] !=  'all')
{
    $result = $db->db_query('SELECT geo_id,geo_country from geo','deviantspy');
    foreach($result as $item)
    {
        if($item['geo_country'] == strtolower($_POST['geo']))
            $geo = ' AND scrape.geo_id='.$item['geo_id'];
    }
} else if(is_numeric($_POST['geo']))
{
    $geo = ' AND scrape.geo_id='.$_POST['geo'];
}

$network = (!empty($_POST['network']) || $_POST['network'] != 0)?'AND scrape.network_id='. strtolower($_POST['network']):'' ;
$report = (!empty($_POST['report']))? strtolower($_POST['report']):"" ;

$draw_count = intval($_POST['draw']);

$dest_url = rawurldecode($_POST['widget_url']);

$index_start = intval($_POST['start']);
$index_length = intval($_POST['length']);

$sql_limit = ' LIMIT ' . $index_start . ',' . $index_length;

/** -----------
 *  REPORT TYPE
 *  -----------
 */
if(!empty($_POST['report']))
    $report = $_POST['report'];
else exit('error. must provide report');

/** --------
 *  ORDERING
 *  --------
 */

if(!empty($_POST['order']))
    $index_order = Array(
        'col'=>intval($_POST['order'][0]['column']),
        'dir'=>$_POST['order'][0]['dir']
);
else
    $index_order = false;


/** ------
 *  SEARCH
 *  ------
 */
if(!empty($_POST['search'][0]['value']))
    $index_search = $_POST['search'][0]['value'];
else $index_search = false;


//Widget columns
$col_widget = Array(
    "widget_id",
    "img_url",
    "headline",
    "destination_url",
    "last_position",
    "widget_avg_position",
    "view_count",
    "first_seen",
    "last_seen",
    "publisher_url",
    "publisher_id",
    "network_id"
);

$col_creative = Array(
    "widget_count",
    "img_url",
    "headline",
    "destination_url",
    "last_position",
    "widget_avg_position",
    "view_count",
    "offer_domain",
    "first_seen",
    "last_seen",
    "publisher_url",
    "network_id"
);

switch($report)
{
    case 'creative2':
        $_cols = $col_creative;
    break;

    case 'widget':
        $_cols = $col_widget;
    break;
    default:
    case 'creative':
        $_cols = $col_creative;
    break;
}

/**
 *  Determine if there is any ordering
 */
if($index_order && is_array($index_order)) //
{
    $sql_ordering = ' ORDER BY ' . $_cols[$index_order['col']] . ' ' . strtoupper($index_order['dir']) ;
} else $sql_ordering = '';

/**
 *  Determine if there is a search
 */

if($index_search)
{
    $sql_search = ' AND ' . $_cols[0] . ' LIKE "%' . $index_search .'%"';
    $i=0;
    foreach($_cols as $item)
    {
        if($i>0) $sql_search .= ' OR ' . $_cols[0] . ' LIKE "%' . $index_search .'%"';
    }
} else $sql_search = '';

/** TODO: FIX ME */
$sql_search = '';

switch($report)
{

    case 'creative2':
        $q = '
            SELECT SQL_CALC_FOUND_ROWS
					count(Distinct placement.placement_identifier) as widget_count,
					creative.creative_img_url as img_url,
                    creative.creative_headline as headline,
                    ROUND(AVG(scrape.creative_position),1) as widget_avg_position,
                    COUNT(Distinct scrape.offer_id) as view_count,
                    count(Distinct SUBSTRING_INDEX(SUBSTRING_INDEX(offer.offer_destination_url, "/", 3),"http://",-1)) AS affiliate_count,
                    count(Distinct SUBSTRING_INDEX(SUBSTRING_INDEX(placement.placement_url, "/", 3),"http://",-1))  as publisher_count,
                    scrape.network_id as network_id,
                    MIN(scrape.scrape_tcreate) as first_seen,
                    MAX(scrape.scrape_tmodified) as last_seen
            FROM creative
                INNER JOIN offer
                ON offer.offer_id = creative.offer_id
                INNER JOIN scrape
                ON scrape.creative_id = creative.creative_id
                INNER JOIN placement
                ON scrape.placement_id = placement.placement_id
            WHERE creative.creative_headline != ""
                ' . $network . '
                ' . $geo . '
            GROUP BY creative.creative_headline
            '. $sql_ordering . $sql_search . $sql_limit .'
            ';

        //error_log($q);
        $result = $db->db_query($q,'deviantspy');
        //$_count = $db->db_query('SELECT FOUND_ROWS() as row_count','deviantspy');
        $cache_instance = \phpFastCache\CacheManager::Files();
        $key = md5($q);

        if(!empty($result) && !is_null($cache_instance->get($key . 'row_count')))
            $row_count = $cache_instance->get($key.'row_count');

        //error_log('ROW COUNT FOUND: ' . print_r($row_count,true));

        //$row_count = $_count[0]['row_count'];//count($result);
        unset($cache_instance);
        foreach($result as $item)
        {
            $data[]=Array(
                $item['widget_count'],
                $item['img_url'],
                $item['headline'],
                $item['widget_avg_position'],
                $item['view_count'],
                $item['affiliate_count'],
                $item['publisher_count'],
                $item['first_seen'] .'<br><strong class="ds_ago">'. time_ago(strtotime($item['first_seen'])).' ago</strong>',
                $item['last_seen'] .'<br><strong class="ds_ago">'. time_ago(strtotime($item['last_seen'])) . ' ago</strong>'
            );
        }

        $response = Array(
            'draw'=>$draw_count,
            'recordsTotal'=>$row_count,
            'recordsFiltered'=>$row_count,//$index_length,
            'data'=>$data);
        $json = json_encode($response);
        //error_log($json);
        exit($json);
        break;

    case 'creative':
            $q = '
            SELECT SQL_CALC_FOUND_ROWS
                    count(Distinct placement.placement_identifier) as widget_count,
                    creative.creative_img_url as img_url,
                    creative.creative_headline as headline,
                    offer.offer_destination_url as destination_url,
                    scrape.creative_position as last_position,
                    ROUND(AVG(scrape.creative_position),1) as widget_avg_position,
                    COUNT(scrape.offer_id) as view_count,
                    SUBSTRING_INDEX(SUBSTRING_INDEX(offer.offer_destination_url, "/", 3),"http://",-1) AS offer_domain,
                    placement.placement_url as publisher_url,
                    scrape.network_id as network_id,
                    MIN(scrape.scrape_tcreate) as first_seen,
                    MAX(scrape.scrape_tmodified) as last_seen,
                    creative.offer_id
            FROM scrape
            		INNER JOIN offer
            			ON scrape.offer_id = offer.offer_id
                    	AND scrape.publisher_id = offer.publisher_id
            		INNER JOIN creative
            			ON scrape.creative_id = creative.creative_id
            			AND scrape.offer_id = creative.offer_id
            			AND scrape.publisher_id = creative.publisher_id
            		INNER JOIN placement
            			ON scrape.placement_id = placement.placement_id
            			AND scrape.publisher_id = placement.publisher_id
            WHERE offer.offer_destination_url !=""
                    AND creative.creative_headline !=""
                ' . $network . '
                ' . $geo . '
            GROUP BY headline,offer_domain
            '. $sql_ordering . $sql_search . $sql_limit .'
            ';

        //error_log($q);
        $result = $db->db_query($q,'deviantspy');
        //$_count = $db->db_query('SELECT FOUND_ROWS() as row_count','deviantspy');
        $cache_instance = \phpFastCache\CacheManager::Files();
        $key = md5($q);

        if(!empty($result) && !is_null($cache_instance->get($key . 'row_count')))
            $row_count = $cache_instance->get($key.'row_count');

        //error_log('ROW COUNT FOUND: ' . print_r($row_count,true));

        //$row_count = $_count[0]['row_count'];//count($result);
        unset($cache_instance);
        foreach($result as $item)
        {
            $duration = time_ago(time() - ((strtotime($item['last_seen'])-strtotime($item['first_seen']))));

            $data[]=Array(
                $item['widget_count'],
                $item['img_url'],
                $item['headline'],
                $item['publisher_url'],
                $item['last_position'],
                $item['widget_avg_position'],
                $item['view_count'],
                $item['first_seen'] .'<br><strong class="ds_ago">'. time_ago(strtotime($item['first_seen'])).' ago</strong>',
                $item['last_seen'] .'<br><strong class="ds_ago">'. time_ago(strtotime($item['last_seen'])) . ' ago</strong>',
                '<strong class="ds_ago">' . $duration . '</strong>',
                $item['destination_url'],
                $item['offer_domain'],
                $item['network_id'],
            );
        }

        $response = Array(
            'draw'=>$draw_count,
            'recordsTotal'=>$row_count,
            'recordsFiltered'=>$row_count,//$index_length,
            'data'=>$data);
        $json = json_encode($response);
        //error_log($json);
        exit($json);
    break;


    case 'widget':
        if(isset($country_id))
        {
            $dest_country = ' AND scrape.geo_id=' . $country_id .' ';
        } else $dest_country = '';
        $q = '
        SELECT SQL_CALC_FOUND_ROWS
            placement.placement_identifier as widget_id,
            creative.creative_img_url as img_url,
            creative.creative_headline as headline,
            offer.offer_destination_url as destination_url,
            scrape.creative_position as last_position,
            ROUND(AVG(scrape.creative_position),1) as widget_avg_position,
            COUNT(scrape.scrape_id) as view_count,
            MIN(scrape.scrape_tcreate) as first_seen,
            MAX(scrape.scrape_tmodified) as last_seen,
            placement.placement_url as publisher_url,
            scrape.publisher_id as publisher_id,
            scrape.network_id as network_id,
            creative.offer_id
        FROM offer
        LEFT JOIN scrape
                ON scrape.offer_id = offer.offer_id
        LEFT JOIN placement
                ON scrape.placement_id = placement.placement_id
        LEFT JOIN creative
                ON scrape.offer_id = creative.offer_id

        WHERE offer_destination_url="'. $dest_url.'"
                '. $network . '
                ' . $geo . '
        GROUP BY placement.placement_identifier
            '. $sql_ordering . $sql_search . $sql_limit .'
            ';

        $result = $db->db_query($q,'deviantspy');
        $cache_instance = \phpFastCache\CacheManager::Files();
        $key = md5($q);

        if(!empty($result) && !is_null($cache_instance->get($key . 'row_count')))
            $row_count = $cache_instance->get($key.'row_count');

        foreach($result as $item)
        {

            $duration = time_ago(time() - ((strtotime($item['last_seen'])-strtotime($item['first_seen']))));

            $data[]=Array(
                $item['widget_id'],
                $item['img_url'],
                $item['headline'],
                $item['publisher_url'],
                $item['last_position'],
                $item['widget_avg_position'],
                $item['view_count'],
                $item['first_seen'] .'<br><strong class="ds_ago">'. time_ago(strtotime($item['first_seen'])).' ago</strong>',
                $item['last_seen'] .'<br><strong class="ds_ago">'. time_ago(strtotime($item['last_seen'])) . ' ago</strong>',
                '<strong class="ds_ago">' . $duration . '</strong>',
                $item['destination_url'],
            );
        }

        $response = Array(
            'draw'=>$draw_count,
            'recordsTotal'=>$row_count,
            'recordsFiltered'=>$row_count,//$index_length,
            'data'=>$data);
        $json = json_encode($response);
        exit($json);
    break;
}
?>