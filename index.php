<?php
	include_once 'config/vendor/autoload.php';
	include_once 'config/Constant.php';
	$const = new Constant();

	$segments = preg_split('@index.php@', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), NULL, PREG_SPLIT_NO_EMPTY);
	$method = $_SERVER['REQUEST_METHOD'];

	//app.get("/master/user");

	$path_info = "/";
	$result = $const->pageNotFound($path_info);
	$last = $segments[count($segments) - 1];
	if( count($segments) == 1 || $last == "index.php" || $last == ""  || $last == " "){
		$result = $const->getIndex();
	}else{
		$path_info = $_SERVER['PATH_INFO'];
		$request = explode('/', trim($path_info,'/'));

		//authentication check
		$header = apache_request_headers();
		if (array_key_exists("X-Token", $header)){
			if($header['X-Token'] != "00043eb6617434cc5f357bbf692e53be" ){
				$result = $const->invalidKey();
			}else{

				//versions
				if($request[0] == "versions"){
					$result = array("code" => 200,"message" => "OK","data" => array( array( "ver_code" => 100, "ver_name" => "1.0.0" )));
				}elseif($request[0] == "pasien"){
					include_once 'model/Pasien.php';
					$m_user = new Pasien();

					if($request[1] == "get"){
						if($method == 'GET'){
							$result = $m_user->all($request[2]);
						}else{
							$result = $const->notAllowed( $method );
						}				
					}elseif($request[1] == "one"){
						if($method == 'GET'){
							$result = $m_user->get($request[2]);
						}else{
							$result = $const->notAllowed( $method );
						}				
					}elseif($request[1] == "save"){
						if($method == 'POST' ){
							$post = json_decode(file_get_contents('php://input'), true);
							$result = $m_user->save($post);
						}else{
							$result = $const->notAllowed( $method );
						}
					}elseif ($request[1] == "update") {
						if($method == "POST"){
							$post = json_decode(file_get_contents('php://input'), true);
							$result = $m_user->update($post, $post['Noauto']);					
						}else{
							$result = $const->notAllowed( $method );
						}
					}elseif ($request[1] == "delete") {
						if($method == "DELETE"){
							$result = $m_user->delete($request[2]);					
						}else{
							$result = $const->notAllowed( $method );
						}
					}elseif ($request[1] == "history") {
						if($method == "GET"){
							$result = $m_user->history($request[2]);
						}else{
							$result = $const->notAllowed( $method );
						}
					}elseif ($request[1] == "profile") {
						if($method == "GET"){
							$result = $m_user->profile($request[2]);
						}else{
							$result = $const->notAllowed( $method );
						}
					}elseif ($request[1] == "deases") {
						if($method == "GET"){
							if(count($request) == 2){
								//satu parameter : deases
								//eg: /pasien/deases
								$result = $m_user->topDeases();
							}elseif (count($request) == 3) {
								//dua parameter : deases dan tahun
								//eg: /pasien/deases/2018
								$result = $m_user->topDeasesTahun($request[2]);
							}elseif (count($request) == 4) {
								//satu parameter : deases, bulan dan tahun
								//eg: /pasien/deases/11/2018
								$result = $m_user->topDeasesBulanTahun($request[2], $request[3]);
							}

						}else{
							$result = $const->notAllowed( $method );
						}
					}else{
						$result = $const->pageNotFound($path_info);
					}

				}elseif ($request[0] == "obat") {
					include_once 'model/Obat.php';
					$m_obat = new Obat();
					if(count($request) > 1){
						if($request[1] == "get"){
							if($method == 'GET'){
								$result = $m_obat->all();
							}else{
								$result = $const->notAllowed( $method );
							}				
						}elseif($request[1] == "one"){
							if($method == 'GET'){
								$result = $m_obat->get($request[2]);
							}else{
								$result = $const->notAllowed( $method );
							}				
						}elseif($request[1] == "save"){
							if($method == 'POST' ){
								$post = json_decode(file_get_contents('php://input'), true);
								$result = $m_obat->save($post);
							}else{
								$result = $const->notAllowed( $method );
							}
						}elseif ($request[1] == "update") {
							if($method == "POST"){
								$post = json_decode(file_get_contents('php://input'), true);
								$result = $m_obat->update($post, $post['usr_code']);					
							}else{
								$result = $const->notAllowed( $method );
							}
						}elseif ($request[1] == "delete") {
							if($method == "DELETE"){
								$result = $m_obat->delete($request[2]);					
							}else{
								$result = $const->notAllowed( $method );
							}
						}elseif ($request[1] == "exp") {
							if($method == "GET"){
								if(count($request) == 2){
									//satu parameter : exp
									//eg: /obat/exp
									$result = $m_obat->execute("SELECT * FROM tbobat WHERE ED < CURDATE()");
								}elseif(count($request) == 3){
									//dua parameter : exp dan kategori
									//eg: /obat/exp/narkotika
									$kateg = $request[2];
									$result = $m_obat->execute("SELECT * FROM tbobat WHERE ED < CURDATE() AND Kategori = '$kateg' ");
								}elseif(count($request) == 4){
									//tiga parameter : exp dan kategori dan waktu
									//eg: /obat/exp/narkotika/2
									$kateg = $request[2];
									$waktu = $request[3];
									$result = $m_obat->execute("SELECT *, datediff(CURDATE(), ED) as waktu FROM tbobat WHERE ED < CURDATE() AND Kategori = '$kateg' AND datediff(CURDATE(), ED) < '$waktu' ");
								}else{
									$result = $const->pageNotFound($path_info);
								}
							}else{
								$result = $const->notAllowed( $method );
							}
						}elseif ($request[1] == "empty") {
							if($method == "GET"){
								if(count($request) == 2){
									//satu parameter : empty
									//eg: /obat/empty
									$result = $m_obat->execute("SELECT * FROM (
																SELECT * FROM tbobat) a
																WHERE Stock <= 10 ");
								}elseif(count($request) == 3){
									//dua parameter : empty dan kategori
									//eg: /obat/empty/narkotika
									$kateg = $request[2];
									$result = $m_obat->execute("SELECT * FROM (
																SELECT * FROM tbobat) a
																WHERE Stock <= 10 AND Kategori = '$kateg' ");
								}elseif(count($request) == 4){
									//tiga parameter : empty, kategori dan jenis
									//eg: /obat/empty/narkotika/kapsul
									$kateg = $request[2];
									$jenis = $request[3];
									$result = $m_obat->execute("SELECT * FROM (
																SELECT * FROM tbobat) a
																WHERE Stock <= 10 AND Kategori = '$kateg' AND Jenis_Obat = '$jenis' ");
								}else{
									$result = $const->pageNotFound($path_info);

								}
							}else{
								$result = $const->notAllowed( $method );
							}
						}else{
							$result = $const->pageNotFound($path_info);
						}
					}else{
						$result = $const->pageNotFound($path_info);
					}
				}elseif($request[0] == "auth") {
				    $result = "Token Key : X-Token, Value : 00043eb6617434cc5f357bbf692e53be. Autentifikasi Token Untuk Layanan Data Khusus Dapat Mengajukan Permintaan Melalui Wa : 081266245533. TERIMA KASIH. SALAM 1 DATA";
				}elseif($request[0] == "temen"){
			        $opts = [
			            "http" => [
			                "method" => "GET",
			                "header" => "Accept-language: en\r\n" .
			                "Cookie: foo=bar\r\n" . "X-Tokenjj : 00043eb6617434cc5f357bbf692e53be"
			            ]
			        ];
			        $context = stream_context_create($opts);
			        // Open the file using the HTTP headers set above
			        if($request[1] == "history"){
			            $url = file_get_contents('http://klinik.mookaps.com/index.php/pasien/history/000', false, $context);
			            $result = json_decode($url, true); 
			        }else{
			            $result = $const->pageNotFound($path_info);
			        }
				}else{
					$result = $const->pageNotFound($path_info);
				}

			}
		}else{
			if($request[0] == "auth"){
				$result = "Token Key : X-Token, Value : 00043eb6617434cc5f357bbf692e53be. Autentifikasi Token Untuk Layanan Data Khusus Dapat Mengajukan Permintaan Melalui Wa : 081266245533. TERIMA KASIH. SALAM 1 DATA";
			}elseif($request[0] == "temen"){
				$opts = [
					"http" => [
						"method" => "GET",
						"header" => "Accept-language: en\r\n" .
						"Cookie: foo=bar\r\n" . "X-Tokenjj : 00043eb6617434cc5f357bbf692e53be"
					]
				];
				$context = stream_context_create($opts);
				// Open the file using the HTTP headers set above
				if($request[1] == "history"){
					$url = file_get_contents('http://klinik.mookaps.com/index.php/pasien/history/000', false, $context);
					$result = json_decode($url, true); 
				}else{
					$result = $const->pageNotFound($path_info);
				}

			}else {
				$result = $const->invalidKey();

			}

		}


	}

	$const->renderJSON($result);

?>