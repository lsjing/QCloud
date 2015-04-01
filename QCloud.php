<?php

class QCloud {
	const SUCCESS = 0;	//
	const MISS_PUBLIC_ARG = 2;	//

	private $errcode = 0;

	private $url = '';
	private $access_key_id = 'RTRVPRAKSCERZWUZMXEC';
	private $secret_access_key = 'xuep9bRyLj9FyR67lhsvpbLGNWhlQuUtemMUcLSq';

	public $entrance = 'https://api.qingcloud.com/iaas/?';

	private $public_args = array(
			'action' => '',
			'time_stamp' => '',
			'access_key_id' => '',
			'version' => 1,
			'signature_method' => 'HmacSHA256',
			'signature_version' => 1,
			//'signature' => ''
		);

	public $actionList = array(
		'DescribeInstances' => array('zone'),
		'RunInstances' => array('image_id','login_mode','zone'),
		'TerminateInstances' => array('instances.n','zone'),
		'StartInstances' => array('instances.n','zone'),
		'StopInstances' => array('instances.n','zone'),
		'RestartInstances' => array('instances.n','zone'),
		'ResetInstances' => array('instances.n','zone','login_mode'),
		'ResizeInstances' => array('instances.n'),
		'ModifyInstanceAttributes' => array('instances','zone'),
		);

	function __construct($args){
		date_default_timezone_set("UTC");
		$this->public_args['time_stamp'] = date("Y-m-d").'T'.date('H:i:s').'Z';
		$this->public_args['access_key_id'] = $this->access_key_id;

		$this->public_args = array_merge($this->public_args, $args);

		foreach ($this->public_args as $key => $value) {
			if ($value == "") {
				$this->errcode = QCloud::MISS_PUBLIC_ARG;
			}
		}
	}



	public function run(){
		if ($this->errcode != QCloud::SUCCESS) {
			die('error occured, code:'.$this->errcode);
		}

		$this->url = $this->entrance;

		ksort($this->public_args);

		foreach ($this->public_args as $key => $value) {
			$this->url .= urlencode($key) . '=' . urlencode($value) . '&';
		}

		$this->url .= 'signature=' . $this->doSignature($this->public_args);
		$r = $this->curl_request($this->url);
		$r = json_decode($r, true);
		echo '<pre>';
		print_r($r);
		echo '</pre>';
	}


	public function doSignature($args){
		ksort($args);
		$string = '';
		$sign = '';

		foreach ($args as $key => $value) {
			$string .= urlencode($key) . '=' . urlencode($value) . '&';
		}

		$string = rtrim($string, '&');

		$string = "GET\n/iaas/\n".$string;

		$hash_string = hash_hmac('sha256', $string, $this->secret_access_key, true);

		$base64_string = base64_encode($hash_string);

		$base64_string = str_replace(' ', '+', $base64_string);

		$sign = urlencode($base64_string);

		return $sign;
	}

	 function curl_request($url,$post='',$cookie='', $returnCookie=0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
	}
}
?>
