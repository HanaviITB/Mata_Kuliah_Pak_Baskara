<?php
		$path_info = $_SERVER['PATH_INFO'];
		$request = explode('/', trim($path_info,'/'));

				$opts = [
					"http" => [
						"method" => "GET",
						"header" => "Accept-language: en\r\n" .
						"Cookie: foo=bar\r\n" . "X-Token : 00043eb6617434cc5f357bbf692e53be"
					]
				];
				$context = stream_context_create($opts);
				// Open the file using the HTTP headers set above
				if($request[0] == "history"){
					$url = file_get_contents('http://localhost/restFull/api_klinik/index.php/pasien/history/000', false, $context);
					$result = json_decode($url, true); 
				}else{
					$result = $const->pageNotFound($path_info);
				}

		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");

				echo json_encode($result);

?>