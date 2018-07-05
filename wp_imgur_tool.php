<?php

	include_once('wp_imgur_config.php');

	// --------------------------------------------------------------------

	$rows = array();
	//SQL query
	$query = "SELECT ID, post_content FROM wp35_posts WHERE post_content LIKE '%imgur.%' ";

	if($config['status']){
		$query .= "AND post_status = '".$config['status']."'";
	}

	if($config['test']){
		$query .= 'LIMIT 2';
		echo "test-mode <br>";
	}
	

	$results = $mysqli->query($query);

	while ($result = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
		$rows[] = $result;
	}

	foreach ($rows as $key => $row) {

		if($config['test']){
			echo "before:";
			print_r($row);
		}

		$doc = new DOMDocument();
		$doc->loadHTML($row['post_content']);
		$tags = $doc->getElementsByTagName('img');
		foreach($tags as $tag){
			$src = $tag->getAttribute('src');
		    if(strpos($src, 'imgur.')){
		    	$filename = basename($src);

		    	//下載
		    	$filename_withpath = $config['dlfloder'].'/'.basename($src);
		    	if(!$config['test']){
		    		file_put_contents($filename_withpath, fopen($src, 'r'));
			    }

		    	//更新文章
		    	$rows[$key]['post_content'] = str_replace($src ,$config['websiteurl'].'/'.$filename , $rows[$key]['post_content']);

		    }
		}

		//更新資料庫
		if(!$config['test']){
			$updateresult = $mysqli->query("UPDATE wp35_posts SET post_content = '".$rows[$key]['post_content']."' WHERE ID=".$row['ID']);
		}

	}

	if($config['test']){
		echo "after:";
		print_r($rows);
	}

?>