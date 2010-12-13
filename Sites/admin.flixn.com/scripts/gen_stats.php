<?php

$starttime = date('l jS \of F Y h:i:s A');

############################################
//psuedo-include section
function set_timestamp($milliseconds)
{
    $output = $milliseconds += rand(0,65);
    return $output;
}

function n_timestamp($milliseconds)
{
    $time_to_go = "TIMESTAMP '1970-01-01' + interval '" . $milliseconds . " seconds'";
    return $time_to_go;
}

function build_query($link,$hour_time,$instance_id,$seed,$media_id)
{
    //$query_cache = array(); //init, baby
    //this is called for each instance_id
    
    $select_query = "SELECT type_id FROM component_instances WHERE id = ".$instance_id.";";
    $pre = pg_fetch_row(pg_query($link, $select_query));
    $instance_type = $pre[0];
    unset($pre);
    
    //$interval = ceil(3600/$seed);
    $present_time = array(); //represents internal loop depth
    $present_time[0] = set_timestamp($hour_time);
    
    for($g_loop = 0;$g_loop<$seed;$g_loop++)
    {
        //increment some for each iteration
        $present_time[0] = set_timestamp($present_time[0]);
        
        $query_cache .= "INSERT INTO event_load (component_instance_id,url_id,timestamp) VALUES("
                            . $instance_id .",1,".n_timestamp($present_time[0]).");";

        switch($instance_type)
            {
                case 1: //recorder
                    for($n=0;$n<rand(0,4);$n++)
                        //randomize child event count
                        {
                            $present_time[1] = set_timestamp($present_time[0]);
                            $query_cache .= "INSERT INTO event_record (load_id,timestamp) VALUES ((SELECT currval('event_load_id_seq')),"
                            . n_timestamp($present_time[1]) .");";
                            
                            for($inner1 = 0; $inner1 < rand(0,5); $inner1++)
                            {
                                if($inner1 <= 4)
                                {
                                    //more often than not, finish it
                                    $present_time[2] = set_timestamp($present_time[1]);
                                    $query_cache .= "INSERT INTO event_record_complete (record_id,duration,timestamp) VALUES ((SELECT currval('event_record_id_seq')),"
                                    . rand(50000,450000) . "," . n_timestamp($present_time[2]) .  "); ";
                                	$inner1 = 6;
					}			
	                            }
                            
                            for($inner1 = 0; $inner1 < rand(0,2); $inner1++)
                            {
                                $present_time[2] = set_timestamp($present_time[1]);
                                $query_cache  .=  "INSERT INTO event_record_review (record_id,timestamp) VALUES ((SELECT currval('event_record_id_seq')),"
                                . n_timestamp($present_time[2]) . "); ";
                            }
                            
                        }
                        break;

		case 2: //uploader
			for($inner1=0;$inner1<rand(0,4);$inner1++)
			//probability of child event chain
			{
				$present_time[1] = set_timestamp($present_time[0]);
				$query_cache .= "INSERT INTO event_upload (load_id,file_size,timestamp_start,timestamp_end) VALUES ((SELECT currval('event_load_id_seq')),"
                    		. rand(520000,126402482) . "," . n_timestamp($present_time[1]) . "," . n_timestamp($present_time[1] + rand(10,50)) . ");";
			}

		break;

		case 4: //player
		for($n=1;$n<rand(0,4);$n++)
		//probability of child event chain - root: event_load_media
		{
		$present_time[1] = set_timestamp($present_time[0]);
                $query_cache .= "INSERT INTO event_load_media (load_id,media_id,load_time,timestamp) VALUES ((SELECT currval('event_load_id_seq')),"
		. array_rand($media_id) . ",".rand(5000,45000)."," . n_timestamp($present_time[1]) .");";

                for($o = 0; $o < rand(0,4); $o++)
                {
		$present_time[2] = set_timestamp($present_time[1]);
                $query_cache .= "INSERT INTO event_play (load_media_id,timestamp) VALUES ((SELECT currval('event_load_media_id_seq')),". n_timestamp($present_time[2]) .");";
                    for($m = 0; $m < rand(0,1); $m++)
                    {
			$present_time[3] = set_timestamp($present_time[2]);
                        $query_cache .= "INSERT INTO event_play_complete (play_id,timestamp) VALUES ((SELECT currval('event_play_id_seq')),"
			. n_timestamp($present_time[3]) . "); ";
                    }
                    for($m = 0; $m < rand(0,3); $m++)
                    {
			$present_time[3] = set_timestamp($present_time[2]);
                        $query_cache .= "INSERT INTO event_play_stop (play_id,toffset,timestamp) VALUES ((SELECT currval('event_play_id_seq')),"
			. rand(1,60) . "," . n_timestamp($present_time[3]) . "); ";
                    }
		    for($m = 0; $m < rand(0,1); $m++)
                    {
			$present_time[3] = set_timestamp($present_time[2]);
                        $query_cache .= "INSERT INTO event_play_seek (play_id,toffset_start,toffset_end,timestamp) VALUES ((SELECT currval('event_play_id_seq')),"
			. rand(1,20) . "," . rand(24,60) . ",". n_timestamp($present_time[3]) . "); ";
                    }
		    for($m = 0; $m < rand(0,3); $m++)
                    {
			$present_time[3] = set_timestamp($present_time[2]);
                        $query_cache .= "INSERT INTO event_play_pause (play_id,toffset,timestamp) VALUES ((SELECT currval('event_play_id_seq')),"
			. rand(1,60) . "," . n_timestamp($present_time[3]) . "); ";
                    }
		} //end player for loop: play
		} //end player for loop: load_media
		break;
	

		//assumption: instance type_id only 1,2, or 4;
		default:
		break;
            }//end switch
            
    }//end outmost loop - seed * instance
    return $query_cache;
} //end function build query set    
        
   
################################################

$conn_string = "host='db.flixn.com' user='flixn' dbname='fmp_dev_stats'";

//open the connection
$link = pg_connect($conn_string);

$day_weight = array(.92,1.15,1.23,1.15,2.14,1.8,1.23);
$hour_weight = array(1.2,.9,.62,.41,.25,.22,.27,.45,.78,
                     1.1,1.16,1.23,1.45,//12, 1pm
                     1.6,1.73,1.8,2,2.25,2.22,2.12,
                     2.6,2.2,1.7,1.2); //23, 11pm

//to be replaced with query later
$instance_id = array(30,31,32,33,34,35,36);

//to be replaced with query later
$media_id = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14);

$base_seed = 425; //aimed number of event_load per hour
$seed = ceil($base_seed/max($day_weight)/max($hour_weight));

//$min_time = 1191801600; //oct 8th 2007
//$min_time = 1200614400;
$min_time = 1215820277; //present, july 12 timestamp
$max_time = strtotime("now + 2 weeks");

$query_list = array();
echo "STARTING STATISTICS: \n";
for($i = $min_time; $i < $max_time; $i += 86400)
{
    echo $i . "\n";
    foreach($instance_id as $inst)
    {
	$output_cache = null;
	echo "\nHours: ";
        for($j = 0;$j<=23;$j++)
        {
	    echo $j . " ";
            $per_day_seed = ceil($day_weight[date("w",$i)] * $hour_weight[$j] * $seed);
		$query_list = build_query($link,($i + ($j * 3600)),$inst,$per_day_seed,$media_id);
		$query = null;
		/*
		foreach($query_list as $key => $value)
		{
			$output_cache .= ".";	
			$query .= $value;
			if($key % 35 == 0)
			{
				pg_query($link,$query);
				usleep(45000);
				$query = null;
			}
		}
                echo $output_cache . "\n";
		if($query)
			pg_query($link,$query);
        */
	pg_query($link,$query_list);
	usleep(1500);
	}
	echo "\nAdding statistics for instance: " . $inst . "\n";
    }
}
pg_close($link);

echo "-------------------------------\n";
echo "The script has successfully completed. \n";
echo "Start time: " . $starttime . "\n";
echo "Completion time: " . date('l jS \of F Y h:i:s A') . "\n";
echo "Tony is really awesome. kthxbai.\n";

?>
