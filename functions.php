<?php
	//打印数据
	function p($data){
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		die;
	}

	//post请求
	function curlPost($url, $data = array()) {
		$curl = curl_init($url);
		$parse_arr = parse_url($url);
		if( !empty($parse_arr['scheme']) && $parse_arr['scheme'] == 'https' ) { 
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		}   
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_POST, 1); 
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$rtn = curl_exec($curl);
	    if($errno = curl_errno($curl)) {
	        throw new Exception(curl_error($curl), $errno);
	    }
		curl_close($curl);
		return $rtn;
	}

	//get请求
	function curlGet($url) {
	  	$ch = curl_init($url);
	  	$parse_arr = parse_url($url);
		if( !empty($parse_arr['scheme']) && $parse_arr['scheme'] == 'https' ) { 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
	  	curl_setopt($ch, CURLOPT_HEADER, false);
	  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	  	$result = curl_exec($ch); 
	  	curl_close($ch);
	  
	  	return $result;
	}
?>