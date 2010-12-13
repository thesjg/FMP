<?php
error_reporting (E_ALL ^ E_NOTICE);

class StatisticsWindow
{
    
    private $link;
    private $conn_string = "host='db.flixn.com' user='flixn' dbname='fmp_dev_stats'";
    private $result_set;
    private $out;
    private $ident;
    private $uid;
    private $tables = array('recorder' => array('load', 'record', 'record_complete'),'uploader' => array('load','upload', 'upload_complete'),'player' => array('load', 'play','play_complete'));
    
    private function convertJson($in)
    {
        $output = "[";
        foreach($in as $v)
        {
            $output .= "[".$v['h'].",".$v['c']."],";
        }

        $output = substr_replace($output,"",-1);
        $output .= "]";
        
        if (strlen($output) < 5)
            return "[]";
        else
            return $output;
    }
        
    public function __construct($user_id)
    {
        $this->uid = 1; //$user_id;
        $this->link = pg_connect($this->conn_string);
    }
    
    private function createArray($endt,$interval = 3600,$data,$timestamp) 
    {
        
        if ($interval == (3600*7*24))
        {
            $start_query = "SELECT date_part('epoch', date_trunc('week', TIMESTAMP '$timestamp')) * 1000 AS h;";
            $end_query = "SELECT date_part('epoch', date_trunc('week', TIMESTAMP '$endt')) * 1000 AS h;";
        }
        elseif ($interval == (3600*24))
        {
            $start_query = "SELECT date_part('epoch', date_trunc('day', TIMESTAMP '$timestamp')) * 1000 AS h;";
            $end_query = "SELECT date_part('epoch', date_trunc('day', TIMESTAMP '$endt')) * 1000 AS h;";
        }
        else
        {
            $start_query = "SELECT date_part('epoch', date_trunc('hour', TIMESTAMP '$timestamp')) * 1000 AS h;";
            $end_query = "SELECT date_part('epoch', date_trunc('hour', TIMESTAMP '$endt')) * 1000 AS h;";
        }
            
        $time = pg_fetch_all(pg_query($this->link,$start_query));
        $start = $time[0]['h'];
        $time = pg_fetch_all(pg_query($this->link,$end_query));
        $end = $time[0]['h'];

        $single = array();
        foreach($data as $v)
            $single[$v['h']] = $v['c'];
            
        $results = array();
        for ($i = $start; $i < $end; $i += ($interval * 1000))
        {
            if ($single[$i])
                $results[] = array('h' => $i,'c' => $single[$i]);
            else
               
                $results[] = array('h' => $i, 'c' => 0);
        }

        return $results;  
    }
    
     
     
    public function getStatistics($start, $end, $instance_id, $media_id)
    {
        
        if(strtotime($end) < strtotime($start))
        {
            $three = $end;
            $end = $start;
            $start = $three;
        }
            
        if(strtotime($end) == strtotime($start))
        {
            $end = date("m\-d\-Y", strtotime($start . "+ 1 day"));
        }
        
    if(is_numeric($instance_id) && $type != "player")
    {
        $type_id = $this->query("SELECT type_id FROM component_instances WHERE id = $instance_id");
        $type_id = (int)$type_id[0]['type_id'];
        
        $type = $this->query("SELECT name FROM component_types WHERE id = $type_id");
        $type = $type[0]['name'];
        
    } else {
        $instance_clause = "";
    }  
    
    $week_min = $this->query("SELECT max(timestamp) FROM event_week_load;");
    $week_min = $week_min[0]['max'];
    
    $day_min = $this->query("SELECT max(timestamp) FROM event_day_load;");
    $day_min = $day_min[0]['max'];
    
    $uid = $this->uid;
    
        if (strtotime($end) < strtotime($week_min))
        {
            $interval = (3600 * 7 * 24);
            $resolution = "'week'";
        }
        elseif (strtotime($end) < strtotime($day_min))
        {
            $interval = (3600 * 24);
            $resolution = "'day'";
        }
        else
        {
            $interval = 3600;
            $resolution = "'hour'";
        }
    
    $data_set = array();
    
    if(is_numeric($instance_id))
    {
    if($type == "player")
        {
            return;
        } else {
            
        foreach($this->tables[$type] as $key => $value)
        {
            
            $table = $value;
                
            $table_id = "component_instance_id";
            $id = $instance_id;
            $source = "component_instances";
            
            $instance_clause = "AND $id IN (SELECT id FROM $source WHERE user_id = $uid) AND $table_id = $id";
            
            $stats_query = "SELECT count as c, date_part('epoch', date_trunc($resolution,timestamp)) * 1000 as h
                    FROM event_week_$table
                    WHERE date_trunc($resolution, timestamp) BETWEEN
                        date_trunc($resolution, timestamp '$start')
                    AND
                        date_trunc($resolution, timestamp '$end')
                    $instance_clause
                    GROUP BY h,c
            UNION
                SELECT sum(count) as c2, date_part('epoch', date_trunc($resolution, timestamp)) * 1000 as h2
                    FROM event_day_$table
                    WHERE date_trunc($resolution, timestamp) BETWEEN
                        date_trunc($resolution, timestamp '$start')
                    AND
                        date_trunc($resolution, timestamp '$end')
                    $instance_clause
                    GROUP BY h2
            UNION
                SELECT sum(count) as c3, date_part('epoch', date_trunc($resolution, timestamp)) * 1000 as h3
                    FROM event_hour_$table
                    WHERE date_trunc($resolution, timestamp) BETWEEN
                        date_trunc($resolution, timestamp '$start')
                    AND
                        date_trunc($resolution, timestamp '$end')
                    $instance_clause
                    GROUP BY h3 ORDER BY h ASC;";
                    $data_set[$value] = $this->convertJson($this->createArray($end, $interval, $this->query($stats_query), $start));
                    
                }
            unset($stats_query);
        }
        return $data_set;
       
    } else {
        $tableset = array('load','record','play','upload');
    foreach($tableset as $key => $value)
        {
            $table = $value;
    
            $table_id = "component_instance_id";
            $id = $instance_id;
            $source = "component_instances";
            
    
            
            $stats_query = "SELECT count as c, date_part('epoch', date_trunc($resolution,timestamp)) * 1000 as h
                    FROM event_week_$table
                    WHERE date_trunc($resolution, timestamp) BETWEEN
                        date_trunc($resolution, timestamp '$start')
                    AND
                        date_trunc($resolution, timestamp '$end')

                    GROUP BY h,c
            UNION
                SELECT sum(count) as c2, date_part('epoch', date_trunc($resolution, timestamp)) * 1000 as h2
                    FROM event_day_$table
                    WHERE date_trunc($resolution, timestamp) BETWEEN
                        date_trunc($resolution, timestamp '$start')
                    AND
                        date_trunc($resolution, timestamp '$end')

                    GROUP BY h2
            UNION
                SELECT sum(count) as c3, date_part('epoch', date_trunc($resolution, timestamp)) * 1000 as h3
                    FROM event_hour_$table
                    WHERE date_trunc($resolution, timestamp) BETWEEN
                        date_trunc($resolution, timestamp '$start')
                    AND
                        date_trunc($resolution, timestamp '$end')

                    GROUP BY h3 ORDER BY h ASC;";

                    $data_set[$value] = $this->convertJson($this->createArray($end, $interval, $this->query($stats_query), $start));
                }
            unset($stats_query);
        }
        return $data_set;
    }

    private function getStats($start,$end,$instance_id,$media_id)
    {
        

        $valid_request = false;
        if ($instance_id)
        {
           if (is_numeric($instance_id))
            {
                $query = "SELECT user_id FROM component_instances WHERE id = " . $instance_id . ";";
                $return = $this->query($query);
                if ($return[0]['user_id'] = $this->uid)
                    {
                        $valid_request = true;
                        $id =& $instance_id;
                        $type = $this->query("SELECT c.name FROM component_types c, component_instances i WHERE c.id = i.type_id AND i.id = $id;");
                        $type = $type[0]['name'];
                    }
            }
        }
        elseif ($media_id)
        {
            if (is_numeric($media_id))
            {
                //manual SQL injection cleansifierizing needless with only numeric input
                $query = "SELECT user_id FROM media WHERE id = " . $media_id . ";";
                $return = $this->query($query);
                if ($return[0]['user_id'] = $this->uid)
                    {
                        $valid_request = true;
                        $id =& $media_id;
                        $type = "media";
                    }
            }
        }
         
        if ($valid_request = true)
        {
            $querylist = array();
            if (strtotime($start) > strtotime("now"))
                return false;
            //validate more
        
            if (strtotime($start) > strtotime("now - 7 days"))
            {
                $querylist = $this->buildQueryHour($start,$end,$type,$id);
                $interval = 3600;
            }
            elseif (strtotime($start) >= strtotime("now - 28 days"))
            {
                $querylist = $this->buildQueryDay($start,$end,$type,$id);
                $interval = 3600 * 24;
            }
            else
            {
                $querylist = $this->buildQueryWeek($start,$end,$type,$id);
                $interval = 3600 * 24 * 7;
            }
                
            $return_set = array();
    
            foreach($querylist as $q)
            {
                //$return_temp = pg_fetch_all(pg_query($this->link,$q));
                $result_set[] = $this->createArray($end,$interval,$q,$start);
            }
            $return_array = array('loads' => $this->convertJson($result_set[0]), //h timestamp, c count
                                  'plays' => $this->convertJson($result_set[3]),
                                  'records' => $this->convertJson($result_set[2]),
                                  'uploads' => $this->convertJson($result_set[1]),);
    
            return $return_array;
        }
    }
     
    //grab the front page summary statistics
    public function getFrontStats()
    {

        $query_total = array();
        $tables = array('load','record','upload');
        foreach($tables as $key)
        {
        $userquery = str_replace("{userid}",$this->uid, "select (select sum(count) from {hour}
                                                        where component_instance_id IN
                                                        (SELECT id FROM component_instances
                                                        WHERE user_id = {userid}))
                                                        + (select sum(count) from {day}
                                                        where component_instance_id IN
                                                        (SELECT id FROM component_instances
                                                        WHERE user_id = {userid}))
                                                        + (select sum(count) from {week}
                                                        where component_instance_id IN
                                                        (SELECT id FROM component_instances
                                                        WHERE user_id = {userid}));");
        
        $times = str_replace("{hour}","event_hour_".$key,$userquery);
        $times = str_replace("{day}","event_day_".$key,$times);
        $times = str_replace("{week}","event_week_".$key,$times);
        $query_total[$key] = $times;
        }
        
        $userquery = str_replace("{userid}",$this->uid, "select (select sum(count) from {hour}
                                                        where media_id IN
                                                        (SELECT id FROM media
                                                        WHERE user_id = {userid}))
                                                        + (select sum(count) from {day}
                                                        where media_id IN
                                                        (SELECT id FROM media
                                                        WHERE user_id = {userid}))
                                                        + (select sum(count) from {week}
                                                        where media_id IN
                                                        (SELECT id FROM media
                                                        WHERE user_id = {userid}));");
        $times = str_replace("{hour}","event_hour_play",$userquery);
        $times = str_replace("{day}","event_day_play",$times);
        $times = str_replace("{week}","event_week_play",$times);
        $query_total["play"] = $times;
        
        
        
        $query_24 = array();
        foreach($tables as $key)
        {
        $userquery = str_replace("{userid}",$this->uid, "SELECT SUM(count) FROM {hour}
                                                        WHERE component_instance_id
                                                        IN (SELECT id FROM component_instances
                                                        WHERE user_id = {userid})
                                                        AND (date_trunc('hour',timestamp))
                                                        BETWEEN date_trunc('hour',now() - INTERVAL '25 hours')
                                                        AND date_trunc('hour',now() - INTERVAL '1 hour');");
        
        $times = str_replace("{hour}","event_hour_".$key,$userquery);
        $query_24[$key] = $times;
        }
        
        
        $userquery = str_replace("{userid}",$this->uid, "SELECT SUM(count) FROM {hour}
                                                        WHERE media_id
                                                        IN (SELECT id FROM media
                                                        WHERE user_id = {userid})
                                                        AND (date_trunc('hour',timestamp))
                                                        BETWEEN date_trunc('hour',now() - INTERVAL '25 hours')
                                                        AND date_trunc('hour',now() - INTERVAL '1 hour');");
        
        $times = str_replace("{hour}","event_hour_play",$userquery);
        $query_24["play"] = $times;
        
        $query_7 = array();
        foreach($tables as $key)
        {
        $userquery = str_replace("{userid}",$this->uid, "SELECT SUM(count) FROM {hour}
                                                        WHERE component_instance_id
                                                        IN (SELECT id FROM component_instances
                                                        WHERE user_id = {userid})
                                                        AND (date_trunc('hour',timestamp))
                                                        BETWEEN date_trunc('hour',now() - INTERVAL '7 days')
                                                        AND date_trunc('hour',now() - INTERVAL '1 hour');");
        
        $times = str_replace("{hour}","event_hour_".$key,$userquery);
        $query_7[$key] = $times;
        }
        
        $userquery = str_replace("{userid}",$this->uid, "SELECT SUM(count) FROM {hour}
                                                        WHERE media_id
                                                        IN (SELECT id FROM media
                                                        WHERE user_id = {userid})
                                                        AND (date_trunc('hour',timestamp))
                                                        BETWEEN date_trunc('hour',now() - INTERVAL '7 days')
                                                        AND date_trunc('hour',now() - INTERVAL '1 hour');");
        
        $times = str_replace("{hour}","event_hour_play",$userquery);
        
        $query_7["play"] = $times;
        $stotal = array();
        foreach ($query_total as $key => $value)
        {
            $stotal[$key] = pg_fetch_row(pg_query($this->link,$value));
            if ($stotal[$key][0] == "")
                $stotal[$key][0] = 0;
        }
        
        $s24 = array();
        foreach ($query_24 as $key => $value)
        {
            $s24[$key] = pg_fetch_row(pg_query($this->link,$value));
            if (!is_numeric($s24[$key][0]))
                    $s24[$key][0] = 0;
        }      
               
               
        $s7 = array();
        foreach($query_7 as $key => $value)
        {
            $s7[$key] = pg_fetch_row(pg_query($this->link,$value));
            if ($s7[$key][0] == "")
                $s7[$key][0] = 0;
        }
        
        $returns = array('total' => $stotal,'24' => $s24,'7' => $s7);
        
        
        
        return $returns;
        //eat my variable naming

    }
    
    //specifically grab graph data for the front page   
    public function retrieveOverview()
    {
        $start_query = "SELECT date_part('epoch', date_trunc('hour',now() - INTERVAL '7 days')) * 1000 AS h;";
        $time = pg_fetch_all(pg_query($this->link,$start_query));
        $start = $time[0]['h'];
        
        $load_query = str_replace("{userid}",$this->uid,"SELECT sum(count) AS c,
                                                    date_part('epoch', date_trunc('hour', timestamp)) * 1000 AS h
                                                    FROM event_hour_load
                                                    WHERE date_trunc('hour', timestamp) BETWEEN
                                                    date_trunc('hour', now() - INTERVAL '7 days')
                                                    AND
                                                    date_trunc('hour', now() - interval '1 hour')
                                                    AND component_instance_id IN
                                                    (SELECT id FROM component_instances
                                                    WHERE user_id = {userid}) GROUP BY h ORDER BY h;");
                    
        $load_return = pg_fetch_all(pg_query($this->link,$load_query));



        $load_single = array();

        foreach($load_return as $value)
            $load_single[$value['h']] = $value['c'];

        $load_fresults = array(); //will be generated - 168 zeros
        for ($i = $start; $i < ($start + (7*24*60*60*1000 + 60*60*1000)); $i += (3600 * 1000))
        {
            if ($load_single[$i])
                $load_fresults[] = array('h' => $i,'c' => $load_single[$i]);
            else
                $load_fresults[] = array('h' => $i, 'c' => 0);
        }

        $play_query = str_replace("{userid}",$this->uid,
                                "SELECT sum(count) AS c,
                                date_part('epoch', date_trunc('hour', timestamp)) * 1000 AS h
                                FROM event_hour_play
                                WHERE date_trunc('hour', timestamp) BETWEEN
                                date_trunc('hour', now() - INTERVAL '7 days')
                                AND
                                date_trunc('hour', now())
                                AND media_id IN
                                (SELECT id FROM media
                                WHERE user_id = {userid}) GROUP BY h ORDER BY h;");
                   
        $play_return = pg_fetch_all(pg_query($this->link,$play_query));
        $play_single = array();

        foreach($play_return as $value)
            $play_single[$value['h']] = $value['c'];
          
        $play_fresults = array(); //will be generated - 168 zeros
        for ($i = $start; $i < ($start + (7*24*60*60*1000 + 60*60*1000)); $i += (3600 * 1000))
        {
            if ($play_single[$i])
                $play_fresults[] = array('h' => $i,'c' => $play_single[$i]);
            else
                $play_fresults[] = array('h' => $i, 'c' => 0);
        }

        $record_query = str_replace("{userid}",$this->uid, "SELECT sum(count) AS c,
                                                            date_part('epoch', date_trunc('hour', timestamp)) * 1000 AS h
                                                            FROM event_hour_record_complete
                                                            WHERE date_trunc('hour', timestamp) BETWEEN
                                                            date_trunc('hour', now() - INTERVAL '7 days')
                                                            AND
                                                            date_trunc('hour', now())
                                                            AND component_instance_id IN
                                                            (SELECT id FROM component_instances
                                                            WHERE user_id = {userid}) GROUP BY h ORDER BY h;");
                    
        $record_return = pg_fetch_all(pg_query($this->link,$record_query));

        $record_single = array();

        foreach($record_return as $value)
            $record_single[$value['h']] = $value['c'];
        
        $record_fresults = array(); //will be generated - 168 zeros
        for ($i = $start; $i < ($start + (7*24*60*60*1000)); $i += (3600 * 1000))
        {
            if ($record_single[$i])
                $record_fresults[] = array('h' => $i,'c' => $record_single[$i]);
            else
                $record_fresults[] = array('h' => $i, 'c' => 0);
        }
        
        
        $upload_query = str_replace("{userid}",$this->uid,"SELECT sum(count) AS c,
                                                    date_part('epoch', date_trunc('hour', timestamp)) * 1000 AS h
                                                    FROM event_hour_upload
                                                    WHERE date_trunc('hour', timestamp) BETWEEN
                                                    date_trunc('hour', now() - INTERVAL '7 days')
                                                    AND
                                                    date_trunc('hour', now())
                                                    AND component_instance_id IN
                                                    (SELECT id FROM component_instances
                                                    WHERE user_id = {userid}) GROUP BY h ORDER BY h;");
                    
        $upload_return = pg_fetch_all(pg_query($this->link,$upload_query));

        $upload_single = array();

        foreach($upload_return as $value)
            $upload_single[$value['h']] = $value['c'];
        
        $upload_fresults = array(); //will be generated - 168 zerosish
        for ($i = $start; $i < ($start + (7*24*60*60*1000)); $i += (3600 * 1000))
        {
            if ($upload_single[$i])
                $upload_fresults[] = array('h' => $i,'c' => $upload_single[$i]);
            else
                $upload_fresults[] = array('h' => $i, 'c' => 0);
        }
        
    $url_query['total'] = "SELECT sum(total.c), total.url FROM (
        SELECT sum(e.count) AS c, u.url FROM event_hour_load e, event_urls u WHERE e.url_id = u.id
             AND component_instance_id IN (SELECT id FROM component_instances WHERE user_id = {userid})
                GROUP BY u.url
    UNION
        SELECT sum(e2.count) AS c, u2.url FROM event_day_load e2, event_urls u2 WHERE e2.url_id = u2.id
            AND e2.component_instance_id IN (SELECT id FROM component_instances WHERE user_id = {userid})
                GROUP BY u2.url
    UNION
        SELECT sum(e3.count) AS c, u3.url FROM event_week_load e3, event_urls u3 WHERE e3.url_id = u3.id
            AND e3.component_instance_id IN (SELECT id FROM component_instances WHERE user_id = {userid})
                GROUP BY u3.url
    ORDER BY c DESC
    ) AS total
    GROUP BY total.url
    ORDER BY sum DESC;";
    
    $url_query['7'] = "select count(*) as c, u.url from event_urls u, event_hour_load e where u.id = e.url_id
    AND e.component_instance_id IN (select id from component_instances where user_id = {userid})
    AND e.timestamp BETWEEN date_trunc('hour',now() - INTERVAL '1 week') AND date_trunc('hour',now())
    group by u.url order by count(*)
    DESC limit 3;";
    
    $url_query['24'] = "select count(*) as c, u.url from event_urls u, event_hour_load e where u.id = e.url_id
    AND e.component_instance_id IN (select id from component_instances where user_id = {userid})
    AND e.timestamp BETWEEN date_trunc('hour',now() - INTERVAL '1 day') AND date_trunc('hour',now())
    group by u.url order by count(*)
    DESC limit 3;";
    
    $top_url = array();
    
    foreach ($url_query as $key => $val)
        $top_url[$key] = pg_fetch_all(pg_query($this->link,str_replace("{userid}",$this->uid,$val)));
     
    $instance_query['total'] = "SELECT sum(total.c), total.id FROM (
                    select sum(e.count) as c, ci.id from event_hour_load e, component_instances ci where ci.user_id = 1 AND ci.id = e.component_instance_id GROUP BY ci.id
                    UNION
                    Select sum(e.count) as c, ci.id from event_day_load e, component_instances ci where ci.user_id = 1 AND ci.id = e.component_instance_id GROUP BY ci.id
                    UNION
                    Select sum(e.count) as c, ci.id from event_week_load e, component_instances ci where ci.user_id = 1 AND ci.id = e.component_instance_id GROUP BY ci.id
                    ) as total
                    group by total.id
                    order by sum desc limit 5;" ;
                    
    $instance_query['7'] = "select sum(e.count) as c, ci.id from event_hour_load e, component_instances ci where ci.user_id = 1
                            AND ci.id = e.component_instance_id
                            AND e.timestamp BETWEEN date_trunc('hour',now() - INTERVAL '1 week') AND date_trunc('hour',now()) GROUP BY ci.id LIMIT 5;";
     
     $instance_query['24'] = "select sum(e.count) as c, ci.id from event_hour_load e, component_instances ci where ci.user_id = 1
                            AND ci.id = e.component_instance_id
                            AND e.timestamp BETWEEN date_trunc('hour',now() - INTERVAL '1 day) AND date_trunc('hour',now()) GROUP BY ci.id LIMIT 5;";
     
     $disk = array();
     $bandwidth = array();
    for ($i = strtotime("-7 days"); $i < strtotime("now"); $i += (60*60*24))
    {
        $disk[] = array('h' => $i * 1000, 'c' => rand(500,4000));
        $bandwidth[] = array('h' => $i * 1000, 'c' => rand(25000,4000));
    }
     
    $return_array = array('loads' => $this->convertJson($load_fresults), //h timestamp, c count
                          'plays' => $this->convertJson($play_fresults),
                          'records' => $this->convertJson($record_fresults),
                          'uploads' => $this->convertJson($upload_fresults),
                          'top_url' => $top_url,
                          'disk' => $this->convertJson($disk),
                          'bandwidth' => $this->convertJson($bandwidth)); //sum, url
    
    return $return_array;
    }
    
    public function query($query)
    {
        return pg_fetch_all(pg_query($this->link,$query));
    }
    
    public function retrieve($start, $end, $instance_id = 0)
    {
        $return = $this->getStats($start, $end, $instance_id, false);
    }
    
    public function sort()
    {
        ksort($this);
        return $this;
    }
    
    public function __destruct()
    {
        pg_close($this->link);
    }

}
?>