<?php
/** =================
 *  crawler.class.php
 *  =================
 *  Crawler object that handles crawling logic
 */
class Crawler
{
    public $publisher_list;
    public $crawl_url;
    public $proxy_list = Array();

    public function build_url($placement)
    {
        /**
         *  This function builds the URL to grab the HTML for the ad
         */

        $network_id = $placement->network_id;

        switch($network_id)
        {
            case NETWORK_REVCONTENT:
                $screen_width = 1401;
                $url = 'http://trends.revcontent.com/serve.js.php?w=' .$placement->identifier.'&t=##########&c=' . time() .'&width=' . $screen_width . '&referer=';
                return $url;
                break;

            case NETWORK_CONTENTAD:
                return $placement->url;
                break;

        }
    }


    public function fetch($url)
    {
        if(empty($this->proxy_list) || !ENABLED_PROXY) $proxy = null;
        else
            $proxy = $this->proxy_list;


        $user_agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
        $ch = curl_init($url);
        if(isset($user_agent)) curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($proxy != null)
        {

            $proxy_ip = $proxy['geo_ip'];
            $proxy_port = $proxy['geo_port'];
            ds_error('Connecting to proxy: ' . $proxy_ip . ':' . $proxy_port);
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
        }


        $result = curl_exec($ch);
	echo "derrick";
	var_dump($result);
	var_dump($ch);
        curl_close($ch);


        return $result;

    }

    /**
     * @param $content
     * @param int $network_id
     * @return bool|mixed
     */
    public function get_network_raw_content($content,$network_id=1)
    {
        switch($network_id)
        {
            case NETWORK_REVCONTENT:

                $start_string = 'rcel.innerHTML = "';
                $end_string = '";';

                $result = $this->get_string_inbetween($content,$start_string,$end_string);
                if(empty($result)) return false;

                return str_replace('\\"','"',str_replace($end_string,'',str_replace($start_string,'',$result[0])));

                break;

            case NETWORK_CONTENTAD:

                $_content = preg_replace("/\s+/", " ",$content);



                $start_string = 'var params =';
                $end_string = ', cb:';
                $data = $this->get_string_inbetween($_content,$start_string,$end_string);
                $_title = $this->get_string_inbetween($_content,"<title>","</title>");
                $title = $_title[1];



                if(empty($data)) return false;

                $content = $data[1] . ',"title":"'. rawurlencode($title) . '","url":"' . rawurlencode($this->crawl_url) .'"}';



                $_a = str_replace(' wid: ',' "wid": ',$content);
                $_b = str_replace(' id:',' "id": ',$_a);
                $_c = str_replace(' d:',' "d": ',$_b);
                $content = json_decode($_c,true);

                //exit(print_r($content));

                $_widget_id = $content['wid'];
                $_d = $content['d'];
                $_id = $content['id'];
                $_title = $content['title'];
                $_url = $content['url'];

                //exit($_widget_id);


                $url = 'http://api.content.ad/GetWidget.aspx?id=' . $_id . '&d='. $_d . '&wid=' . $_widget_id . '&cb=' . time() . '&lazyLoad=false&server=api.content.ad&title=' . $_title . '&url=' . $_url;

                //exit('accessing: ' . $url);
                return $this->fetch($url);

                /**
                 * http://api.content.ad/GetWidget.aspx?id=0eeace09-9c55-4a03-be97-fe40b4423211&d=d29ua2V0dGUuY29t&wid=70799&cb=1455918605494&lazyLoad=false&server=api.content.ad&title=San%2520Francisco%2520Tech%2520Bro%2520Sick%2520Of%2520Stepping%2520Over%2520Untouchables%2520On%2520Way%2520To%2520And%2520From%2520Mansion%2520%257C%2520Wonkette&url=http%3A%2F%2Fwonkette.com%2F598888%2Fsan-francisco-tech-bro-sick-of-stepping-over-untouchables-on-way-to-and-from-mansion
                 */

                /**
                 * http://api.content.ad/GetWidget.aspx?
                 *      id=0eeace09-9c55-4a03-be97-fe40b4423211&
                 *      d=d29ua2V0dGUuY29t&
                 *      wid=70799&cb=1455918605494&
                 *      lazyLoad=false&server=api.content.ad&
                 *      title=San%2520Francisco%2520Tech%2520Bro%2520Sick%2520Of%2520Stepping%2520Over%2520Untouchables%2520On%2520Way%2520To%2520And%2520From%2520Mansion%2520%257C%2520Wonkette&
                 *      url=http%3A%2F%2Fwonkette.com%2F598888%2Fsan-francisco-tech-bro-sick-of-stepping-over-untouchables-on-way-to-and-from-mansion
                 */

                break;




            default:
                return false;
                break;
        }
    }


    /**
     * @param $content
     * @param int $network_id
     * @return bool|SimpleXMLElement[]
     */
    public function parse_network_content($content,$network_id=null)
    {
        if($network_id == null) return false;

        $data = Array();
        switch($network_id)
        {


            case NETWORK_CONTENTAD:

                $_html = $this->get_string_inbetween($content,'line += \'','line += \'<!--[if lt IE 9]>\';');

                if(empty($_html[1]))
                    $_html = $this->get_string_inbetween($content,'line += \'','return line;');


                if(empty($_html[1]))
                {
                    file_put_contents(TMP_PATH . 'err_parse_'. $network_id . '_' . md5($content),$content);
                    ds_error('parse_network_content() error written to:' .TMP_PATH . 'err_parse_' . $network_id . '_' . md5($content));
                    return false;
                }

                $_html[1] = str_replace('line += \'','',$_html[1]);
                $_html[1] = str_replace('\';','',$_html[1]);
                $_html[1] = str_replace('\';','',$_html[1]);
                $content = $_html[1];


                $doc = new DOMDocument;
                $doc->preserveWhiteSpace = false;
                @$doc->loadHTML($content);
                $xpath = new DOMXpath($doc);


                $result = $xpath->query('//div/div/div/div[contains(@class, \'ac_container\')]/a');

                $count = 0;
                //exit(print_r($result->item(0)));
                //exit($doc->saveHTML($result->item(0)));

                foreach($result as $attr)
                {
                    $count++;

                    $img = $xpath->query('//img', $attr);


                    //$htmlString = $doc->saveHTML($a->item(0)); //VIEW HTML
                    //exit($htmlString);


                    $title = $img->item($count-1)->getAttribute('alt');
                    $img = $img->item($count-1)->getAttribute('src');
                    $click_url = $attr->getAttribute('href');
                    $position = $count;



                    if(SCAN_ALL_REDIRECTS)
                    {
                        $redirects = $this::get_all_redirects($click_url);
                        //exit(print_r($redirects,true));
                        if(empty($redirects)) $redirects[0] = $click_url;
                        $url = $redirects[count($redirects)-1];
                    }

                    else
                    {
                        $redirects[0] = $click_url;
                        $url = $redirects[0];
                    }



                    $data[]= Array(
                        'title'=> $title,
                        'url'=> $url,
                        'click_url' => $click_url,
                        'img' => $img,
                        'redirects'=>$redirects,
                        'position'=>$position
                    );
                }


                unset($xpath);
                unset($doc);

                return $data;

                break;

            case NETWORK_REVCONTENT:
                /*
                $xml = new SimpleXMLElement($content);
                $result = $xml->xpath('//li[@class="rc-item"]/a');

                $count = 0;
                foreach($result as $item)
                {
                    $count++;
                    $attr = $item[0];

                    $title = (string)$attr['title'][0];
                    $position = $count;
                    $url = (string)$attr['data-target'][0];
                    $click_url = 'http:'.(string)$attr['href'][0];
                    $redirects = $this::get_all_redirects($click_url);

                    $data[] = Array(
                        'title'=> $title
,                        'url'=>$url,
                        'click_url'=>$click_url,
                        'redirect'=>$redirects,
                        'position'=>$position
                    );


                }

                print 'Content: ' . $content;
                print_r($data);

                exit();

                return $result;*/

                $doc = new DOMDocument;
                $doc->preserveWhiteSpace = false;
                @$doc->loadHTML($content);
                $xpath = new DOMXpath($doc);


                $result = $xpath->query('//div[contains(@class, \'rc-item\')]/a[@class=\'rc-cta\']');

                $count = 0;

                foreach($result as $attr)
                {
                    $count++;
                    /**
                     *  Lets extract the image URL of the creative
                     */
                    $a = $xpath->query('//div[contains(@class,\'rc-photo-scale\')]/div[contains(@class,\'rc-photo\')]', $attr);
                    //$htmlString = $doc->saveHTML($img->item(0)); //VIEW HTML

                    if($a->item($count-1) == null) return false;

                    $b = $a->item($count-1)->getAttribute('style');
                    $_str_start = 'url(//';
                    $_str_end = ');';
                    $_start = strpos($b,$_str_start) + strlen('url(//');
                    $_end = strpos($b,$_str_end) - $_start;

                    $img = 'http://'.substr($b,$_start,$_end);
                    $title = $attr->getAttribute('title');
                    $position = $count;
                    $url = $attr->getAttribute('data-target');
                    $click_url = 'http:'.$attr->getAttribute('href');

                    if(SCAN_ALL_REDIRECTS) $redirects = $this::get_all_redirects($click_url);
                    else $redirects[0] = $url;

                    $data[] = Array(
                        'title'=> $title,
                        'url'=>$url,
                        'click_url'=>$click_url,
                        'img'=>$img,
                        'redirects'=>$redirects,
                        'position'=>$position
                    );
                    ds_error('Placement parse data: ' . PHP_EOL. print_r($data,true));

                }
                unset($xpath);
                unset($doc);

                return $data;


                break;

            default:
                return false;
                break;
        }
    }



    private function get_redirect_url($url){
        $redirect_url = null;

        $url_parts = @parse_url($url);
        if (!$url_parts) return false;
        if (!isset($url_parts['host'])) return false; //can't process relative URLs
        if (!isset($url_parts['path'])) $url_parts['path'] = '/';

        $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
        if (!$sock) return false;

        $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n";
        $request .= 'Host: ' . $url_parts['host'] . "\r\n";
        $request .= "Connection: Close\r\n\r\n";
        fwrite($sock, $request);
        $response = '';
        while(!feof($sock)) $response .= fread($sock, 8192);
        fclose($sock);

        if (preg_match('/^Location: (.+?)$/m', $response, $matches)){
            if ( substr($matches[1], 0, 1) == "/" )
                return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
            else
                return trim($matches[1]);

        } else {
            return false;
        }

    }

    /**
     * get_all_redirects()
     * Follows and collects all redirects, in order, for the given URL.
     *
     * @param string $url
     * @return array
     */
    private function get_all_redirects($url){
        $redirects = array();
        $count = 0;
        while ($newurl = $this::get_redirect_url($url)){
            if (in_array($newurl, $redirects) || $count == REDIRECT_COUNT){
                break;
            }
            $redirects[] = $newurl;
            $url = $newurl;
            $count++;
        }
        return $redirects;
    }

    /**
     * get_final_url()
     * Gets the address that the URL ultimately leads to.
     * Returns $url itself if it isn't a redirect.
     *
     * @param string $url
     * @return string
     */
    private function get_final_url($url){
        $redirects = $this::get_all_redirects($url);
        if (count($redirects)>0){
            return array_pop($redirects);
        } else {
            return $url;
        }
    }


    private function get_string_inbetween($content,$start_string,$end_string)
    {
        $delimiter = '#';
        $regex = $delimiter . preg_quote($start_string, $delimiter)
            . '(.*?)'
            . preg_quote($end_string, $delimiter)
            . $delimiter
            . 's';
        preg_match($regex,$content,$matches);
        return $matches;
    }

}
