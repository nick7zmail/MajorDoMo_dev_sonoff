<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='dev_sonoff_devices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='share') {
	$ok=1;
	$this->getConfig();
	$payload['action']='share';
	$payload['userAgent']='app';
	$payload['deviceid']=$rec['DEVICEID'];;
	$payload['apikey']=$this->config['APIKEY'];
	
	$payload['params']['uid']=gr('uid');
	$payload['params']['deviceName']=$rec['TITLE'];
	$username=$this->config['EMAIL'];
	if(!$username) $username='MajorDoMo';
	$payload['params']['userName']= $username;
	$payload['params']['permit']=15;
	$payload['params']['note']=gr('note');
		$appid_str="204,208,176,196,204,176,216,192,176,224,176,220,176,200,212,176,228,176,200,196,176,204,192,176,204,196,176,204,204,176,208,224,176,208,192,176,216,196,176,200,192,176,212,228,176,204,228,176,196,228,176,216,176,204,224,176,196,208,176,196,228,176,200,220,176,208,192,176,204,228,176,216,176,212,200,176,200,220,176,208,192,176,216,208,176,212,196,176,204,216 "; //app ID
		$dict_str='ab!@#$ijklmcdefghBCWXYZ01234DEFGHnopqrstuvwxyzAIJKLMNOPQRSTUV5689%^&*()';//словарь
		$app_arr=explode(',', $appid_str);
		$dict_arr=str_split($dict_str);	
		foreach($app_arr as $key=>$byte) {
			$app_arr[$key]=($byte >> 2);
		}
		$indexes2_str = implode(array_map("chr", $app_arr));
		$indexes2_arr = explode(',', $indexes2_str);
		foreach($indexes2_arr as $index) {$appid.= $dict_arr[$index];}	
	$payload['params']['sender_appid']=$appid;
	$payload['timeout']=18000;
	$payload['sequence']=time()*1000;
	$jsonstring=json_encode($payload);
	debmes('[wss] --- '.$jsonstring, 'cycle_dev_sonoff_debug');
	if(isset($this->sonoffws)) {
		if($this->sonoffws->isConnected()) {
			try {
				$this->sonoffws->send($jsonstring);
			} catch (BadOpcodeException $e) {
				echo 'Couldn`t sent: ' . $e->getMessage();
			}
		}				
	} else {
		include_once("./lib/websockets/sonoffws.class.php");
		$wssurl=$this->getWssUrl();
		$sonoffws = new SonoffWS($wssurl, $config);
		$sonoffws->socketUrl=$wssurl;
		$sonoffws->connect();
		$this->sonoffws=$sonoffws;
		$this->wssGreatings();
		if($this->sonoffws->isConnected()) {
			try {
				$this->sonoffws->send($jsonstring);
			} catch (BadOpcodeException $e) {
				echo 'Couldn`t sent: ' . $e->getMessage();
			}
		}
	}
	$this->redirect("?data_source=");
	if($this->config['DEBUG']) {
		$recv=$this->sonoffws->receive();
		debmes('[wss] +++ '.$recv, 'cycle_dev_sonoff_debug');
	}
	$sonoffws->close();	
	
  }	
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
