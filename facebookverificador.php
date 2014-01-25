<?php
header( 'Content-type: text/html; charset=utf-8' );
header("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
set_time_limit(0);
ob_implicit_flush(1);

include_once "facebookUtils.php";

//vars
$raw_facebook	= 'raw_fb.txt';			//contains raw html dumped from facebook
$access_token 	= 'CAACEdEose0cBAGc8xD6rMiAaBfOSV8aZBijhQ8zrOva3pSnzJrKyLajv8yInmMqfnYqPxJmrs7SAp5TZCzFhiXUzueXvg3My7r0ymjF1xVIHME222nZBeYpyq4ZCaZBWrGFuAzmDW0sM7k7ZBXofxUEHswo0dZCLz7n6ZAn4TUBcFO7zv5Nh4IIndtvo3SfjfqoZD';
$row1		 	= array('validez%','fid','username','Url','amigos','foto','paginas','thmb');
$promedio		= 300;	// common number of friends.
$limit			= 1000;
ini_set('max_execution_time', 999999);
error_reporting(E_ERROR | E_PARSE);


//magic
$FacebookScraper = new FacebookScraper();
$ids = $FacebookScraper->extractIds(file_get_contents($raw_facebook));

$fbquery = new FacebookQuickFql();
$fbquery->setAccessToken( $access_token );

printf("total: %d  \n" ,count($ids)); 
ob_flush();

//dump into excel csv file.
$fp = fopen( time().'_export.csv', 'w');
fputcsv($fp, $row1);

//gather data :)
$x = 1;
foreach ($ids as $username) { 
	if($x==$limit+1) break;
	printf("-- procesando: %s \n" ,$x.' - '.$username); ob_flush();
	$fid = $fbquery->getIdFromUsername($username); 
	if( $fid == null){
		printf(" +- omitido: %s \n" ,$username); ob_flush();
		continue;
	}
	$fbquery->setTargetId($fid);
    try {
		$myfriends = $fbquery->getFriendsCount();
		$row	= array(
					//($myfriends/$promedio)*100,	//100/300*amigos
					((100/$promedio)*$myfriends > 100) ?  100 : (100/$promedio)*$myfriends ,	//100/300*amigos
					$fid,
					$username,
					$fbquery->getAttribute("profile_url"),	
					$myfriends,	
					$fbquery->getAttribute("pic_big"),	
					null,
					'=image(F'.($x+1).', 2)'						
					);
		$row[6] = @implode(", ",$FacebookScraper->extractFanPagesFromUrl( $fbquery->getAttribute("profile_url") ));
	} catch (Exception $e) {	
		$row[4] = $e;
    }
	$fbquery->flush();
	$x++;
	printf(" +-- procesado: %s \n" ,$username); ob_flush();
	fputcsv($fp, $row); $row = null;
}

fclose($fp);
echo 'Done. report created!';

