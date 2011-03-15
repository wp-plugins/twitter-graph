<?php
/*  Copyright 2011  Lumolink  (email : Jussi Räsänen <jussi.rasanen@lumolink.com>)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Contributions:
    @wickux - http://twitter.com/wickux
*/


include 'gChart.php';


class Twt {
    // Twitter API url
    const API_URL = 'http://api.twitter.com';
    const API_VERSION = 1;
    const TABLE_NAME = 'twtstats';
    
    private $resource;
    private $method;
    private $format;
    private $username;
    private $count;
    
    // User json object
    private $user;
    
    public function __construct($username) {
        $this->username = $username;
        $this->format = "json";
    }
    
    /**
     * Fetches user's timeline
     *
     * @return object
     * @author Jussi
     **/
    public function timeline($username, $count = 5) {
        $this->resource = "statuses";
        $this->method = "user_timeline";
        $this->username = $username;
        $this->count = $count;
        $params  = "&amp;count=" . $this->count;
        $response = $this->getResponse($params);
        return $response;
    }
    
    /**
     * Fetches user object
     *
     * @return object
     * @author Jussi
     **/
    public function user($username) {
        if (isset($this->user))
            return $this->user;
        $this->resource = "users";
        $this->method = "show";
        $this->username = $username;
        $response = $this->getResponse();
        $this->user = $response;
        return $response;
    }
    
    /**
     * Construct's twitter API call url and fetches it as json object
     * 
     * @return object
     * @author Jussi
     **/
    private function getResponse($params = null) {
        $url  = self::API_URL . "/";
        $url .= self::API_VERSION . "/";
        $url .= $this->resource . "/";
        $url .= $this->method . ".";
        $url .= $this->format . "?";
        $url .= "screen_name=" . $this->username;
        if ($params !== null)
            $url .= $params;
        return $this->getUrl($url);
    }
    
    /**
     * Uses curl to fetch url & decodes json response
     *
     * @return object
     * @author Jussi
     **/
    private function getUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1); 
        $content = curl_exec($ch);
        curl_close($ch);
        return json_decode($content);
    }
    
    /**
     * Generates url for twitter
     *
     * @return string
     * @author Jussi Räsänen
     **/
    public function getGraph($days = 7) {
        $data = $this->getData();
        $dates = array();
        $followers = array();
        $following = array();
        $listed = array();
        
        foreach($data as $d) {
            if ($days <= 0) break;
            $dates[] = date("d.M", strtotime($d->created));
            $followers[] = $d->followers;
            $following[] = $d->following;
            $listed[] = $d->listed;
            $days--;
        }
        
        $lineChart = new gLineChart(520,250);

        # Followers
        $lineChart->addDataSet($followers);
        $lineChart->addDataSet($following);
        $lineChart->addDataSet($listed);

        $lineChart->setLegend(array("Followers ({$followers[count($followers)-1]})", "Following ({$following[count($following)-1]})", "Listed ({$listed[count($listed)-1]})"));
        $lineChart->setColors(array("FE4365", "83AF9B", "22aacc", "3333AA"));
        $maxNum = ceil($this->getLargest($listed,$following,$followers)/100)*100;

        $lineChart->setVisibleAxes(array('x','y'));
        $lineChart->setDataRange(0, $maxNum);
        $lineChart->addAxisLabel(0, $dates);
        $lineChart->addAxisRange(1, 30, $maxNum);
        
        return $lineChart->getUrl();
    }

    /**
     * Returns twitter dataset from DB
     *
     * @return mixed
     * @author Jussi Räsänen
     **/
    public function getData() {
        global $wpdb;
        $tblName = $wpdb->prefix . self::TABLE_NAME;
        
        $rows = $wpdb->get_results( "SELECT * FROM $tblName ORDER BY $tblName.`created` ASC" );
        
        // Get latest
        $latest = (isset($rows[count($rows)-1])) ? $rows[count($rows)-1] : null;
        
        if (count($rows) < 1)
        {
            $this->updateStats();
        }
        
        // Day passed?
        else if ($latest != null && time()-strtotime($latest->created) > 86400) {
            $this->updateStats();
        }
        
        // Fetch newest
        $rows = $wpdb->get_results( "SELECT * FROM $tblName ORDER BY $tblName.`created` ASC" );
        return $rows;
    }
    
    /**
     * Returns row count in DB
     *
     * @return int
     * @author Jussi Räsänen
     **/
    public function getRowCount()
    {
        global $wpdb;
        $tblName = $wpdb->prefix . self::TABLE_NAME;
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tblName"));
        return $count; 
    }
    
    /**
     * Updates newest followers, following and listed values to db
     *
     * @return void
     * @author Jussi Räsänen
     **/
    private function updateStats() {
        global $wpdb;
        $listed       = $this->user($this->username)->listed_count;
        $following    = $this->user($this->username)->friends_count;
        $followers    = $this->user($this->username)->followers_count;
        $data = array(  'followers' => $followers,
                        'following' => $following,
                        'listed' => $listed,
                        'created' => date("Y-m-d H:i:s"));
        $wpdb->insert( $wpdb->prefix . "twtstats", $data );
    }
    
    /**
     * Returns username associated with the class.
     *
     * @return string
     * @author Jussi Räsänen
     **/
    public function getUsername()
    {
        return $this->username;
    }
    
    //
    // Helper functions
    //
    
    /**
     * Returns the largest integer in array(s)
     *
     * @return int
     * @author Jussi Räsänen, @wickux
     **/
    private function getLargest() {
        $args = func_get_args();
        $largest = 0;

        foreach($args as $array)
        {
            $is_largest = max($array);
            if (is_array($is_largest))
                $is_largest = $this->getLargest($is_largest);
            if ($is_largest > $largest)
                $largest = $is_largest;
        }
        return $largest;
    }
    
}
