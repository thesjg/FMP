<?

echo "LOLZ MAKING SUM STATS BITCHES \n";
echo "\n";
$conn_string = "dbname=fmp_dev_stats user=flixn host=db.flixn.com";
$link = pg_connect($conn_string);
$delete[0] = "DELETE FROM event_hour_load WHERE timestamp > date_trunc('hour',now() - INTERVAL '178 hours');";
$delete[1] = "DELETE FROM event_hour_upload WHERE timestamp > date_trunc('hour',now() - INTERVAL '178 hours');";
$delete[2] = "DELETE FROM event_hour_record_complete WHERE timestamp > date_trunc('hour',now() - INTERVAL '178 hours');";
$delete[3] = "DELETE FROM event_hour_play WHERE timestamp > date_trunc('hour',now() - INTERVAL '178 hours');";
$delete[4] = "DELETE FROM event_hour_record WHERE timestamp > date_trunc('hour',now() - INTERVAL '178 hours');";




foreach($delete as $lol)
	pg_query($link,$lol);

for($j = 0; $j <= 174; $j++)
{

	pg_query($link,"insert into event_hour_load (url_id,component_instance_id,count,timestamp) VALUES (".rand(1,3).",14,".($j+rand(190,220)).", now() - INTERVAL '".$j." hours');");
	pg_query($link,"insert into event_hour_play (media_id,count,timestamp) VALUES (9,".($j+rand(80,95)).", now() - INTERVAL '".$j." hours');");
	pg_query($link,"insert into event_hour_upload (component_instance_id,count,timestamp) VALUES (11,".($j+rand(50,60)).", date_trunc('hour',now() - INTERVAL '".$j." hours'));");
	pg_query($link,"insert into event_hour_record (component_instance_id,count,timestamp) VALUES (14,".($j+rand(60,74)).", date_trunc('hour',now() - INTERVAL '".$j." hours'));");
        pg_query($link,"insert into event_hour_record_complete (component_instance_id,count,timestamp) VALUES (14,".($j+rand(60,74)).", date_trunc('hour',now() - INTERVAL '".$j." hours'));");




}
pg_close($link);
?>
