<?php
/**
* Sonoff 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 21:11:19 [Nov 13, 2018])
*/
//
//
class dev_sonoff extends module {
	private $sonoffws;
/**
* dev_sonoff
*
* Module class constructor
*
* @access private
*/

function __construct() {
  $this->name="dev_sonoff";
  $this->title="Sonoff";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();
 $out['HTTPS_API_URL']=$this->config['HTTPS_API_URL'];
 $out['WSS_API_URL']=$this->config['WSS_API_URL'];
 $out['TOKEN']=$this->config['TOKEN'];
 $out['EMAIL']=$this->config['EMAIL'];
 $out['PASS']=$this->config['PASS'];
 $out['DEBUG']=$this->config['DEBUG'];
 $out['APIKEY']=$this->config['APIKEY'];
 $out['VERSION']=$this->config['VERSION'];
 $out['APKVERSION']=$this->config['APKVERSION'];
 $out['OS']=$this->config['OS'];
 $out['MODEL']=$this->config['MODEL'];
 $out['ROMVERSION']=$this->config['ROMVERSION'];

 if ($this->view_mode=='update_settings') {
	$api_url = "";
	
   if(gr('login')) {
	   $login=$this->loginAuth(gr('login'), gr('pass'));
	   $at=$login['at'];
	   $reg=$login['region'];
	   $api_url = "$reg-api.coolkit.cc";
	   $this->config['WSS_API_URL']=$this->getWssSrv($reg, $at);
	   
   } else {
	   $api_url=gr('https_api_url');
	   $this->config['WSS_API_URL']=gr('wss_api_url');
   }
   $this->config['EMAIL']=gr('login');
   $this->config['PASS']=gr('pass');
   
   if($at) {
	   $this->config['TOKEN']=$at;
   } else {
	   $this->config['TOKEN']=gr('token');
   }
   $this->config['DEBUG']=gr('debug');
   $this->config['VERSION']=intval(gr('version'));
   $this->config['APKVERSION']=gr('apkversion');
   $this->config['OS']=gr('os');
   $this->config['MODEL']=gr('model');
   $this->config['ROMVERSION']=gr('romVersion');
   $this->config['HTTPS_API_URL'] = $api_url;

   if(!intval(gr('version'))) $out['ERR_VERSION']=1;


   $this->saveConfig();
   $this->dev_sonoff_devices_cloudscan();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='dev_sonoff_devices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_dev_sonoff_devices') {
   $this->search_dev_sonoff_devices($out);
  }
  if ($this->view_mode=='edit_dev_sonoff_devices') {
   $this->edit_dev_sonoff_devices($out, $this->id);
  }
  if ($this->view_mode=='share_dev_sonoff_devices') {
   $this->share_dev_sonoff_devices($out, $this->id);
  }
  if ($this->view_mode=='delete_dev_sonoff_devices') {
   $this->delete_dev_sonoff_devices($this->id);
   $this->redirect("?data_source=dev_sonoff_devices");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='dev_sonoff_data') {
  if ($this->view_mode=='' || $this->view_mode=='search_dev_sonoff_data') {
   $this->search_dev_sonoff_data($out);
  }
  if ($this->view_mode=='edit_dev_sonoff_data') {
   $this->edit_dev_sonoff_data($out, $this->id);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* dev_sonoff_devices cloud scan
*
* @access public
*/
 function dev_sonoff_devices_cloudscan() {
  require(DIR_MODULES.$this->name.'/dev_sonoff_devices_scan.inc.php');
 }
/**
* dev_sonoff_devices cloud scan
*
* @access public
*/
 function dev_sonoff_devices_wss($recv, $sonoffws) {
  require(DIR_MODULES.$this->name.'/dev_sonoff_devices_wss.inc.php');
 }
/**
* dev_sonoff_devices search
*
* @access public
*/
 function search_dev_sonoff_devices(&$out) {
  require(DIR_MODULES.$this->name.'/dev_sonoff_devices_search.inc.php');
 }
/**
* dev_sonoff_devices edit/add
*
* @access public
*/
 function edit_dev_sonoff_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/dev_sonoff_devices_edit.inc.php');
 }
 
 function share_dev_sonoff_devices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/dev_sonoff_devices_share.inc.php');
 }
/**
* dev_sonoff_devices delete record
*
* @access public
*/
 function delete_dev_sonoff_devices($id) {
  $rec=SQLSelectOne("SELECT * FROM dev_sonoff_devices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM dev_sonoff_devices WHERE ID='".$rec['ID']."'");
 }
/**
* dev_sonoff_data search
*
* @access public
*/
 function search_dev_sonoff_data(&$out) {
  require(DIR_MODULES.$this->name.'/dev_sonoff_data_search.inc.php');
 }
/**
* dev_sonoff_data edit/add
*
* @access public
*/
 function edit_dev_sonoff_data(&$out, $id) {
  require(DIR_MODULES.$this->name.'/dev_sonoff_data_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
  $this->getConfig();
   $table='dev_sonoff_data';
   $properties=SQLSelect("SELECT * FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
			$dev_id=$properties[$i]['DEVICE_ID'];
			$device=SQLSelectOne("SELECT * FROM dev_sonoff_devices WHERE ID='$dev_id'");
			$param=$properties[$i]['TITLE'];
		if(!$device['DEVICE_MODE'] || $device['DEVICE_MODE']=='off') {
			$payload['action']='update';
			$payload['userAgent']='app';
			$payload['apikey']=$this->config['APIKEY'];
			$payload['deviceid']=$device['DEVICEID'];
			if(strpos($param, 'switch.')!==false) {
				$dev_arr=explode('.', $param);
				$payload['params']['switches'][0]['outlet']=intval($dev_arr[1]);
				$payload['params']['switches'][0]['switch']=$this->metricsModify($param, $value, 'to_device');
			} elseif($param=='rfsend') {
				$payload['params']['cmd']='transmit';
				$payload['params']['rfChl']=intval($value);
			} elseif($param=='rflearn') {
				$payload['params']['cmd']='capture';
				$payload['params']['rfChl']=intval($value);
			} elseif($param=='cmdline') {
				$payload['params']=$value;
			} else {
				$payload['params'][$param]=$this->metricsModify($param, $value, 'to_device');
			}
			$payload['sequence']=time()*1000;	
			$jsonstring=json_encode($payload);
			if($this->config['DEBUG']) debmes('[wss] --- '.$jsonstring, 'cycle_dev_sonoff_debug');
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
			$sonoffws->close();
		} elseif($device['DEVICE_MODE']==1) {
			
		//================================LAN MODE================================//
			if ($device['ID']) {
				$params = array();
				if ($properties[$i]["TITLE"] == "switch")
				{
					$cmd = "zeroconf/switch";
					$params['switch'] = $this->metricsModify($param, $value, 'to_device');
					$params['mainSwitch'] = $this->metricsModify($param, $value, 'to_device');
					$params['deviceType'] = 'normal';
					 
				}
				if ($properties[$i]["TITLE"] == "switch.0" ||
					$properties[$i]["TITLE"] == "switch.1" ||
					$properties[$i]["TITLE"] == "switch.2" ||
					$properties[$i]["TITLE"] == "switch.3" )
				{
					$cmd = "zeroconf/switches";
					$params['switches']['outlet'] = intval(substr($properties[$i]["TITLE"],6,1));
					$params['switches']['switch'] = $this->metricsModify($param, $value, 'to_device');
				}
				if ($properties[$i]["TITLE"] == "startup") // on off stay
				{
					$cmd = "zeroconf/startup";
					$params['startup'] = $value;
				}
				if ($properties[$i]["TITLE"] == "sledOnline") 
				{
					$cmd = "zeroconf/sledOnline";
					$params['sledOnline'] = $this->metricsModify($param, $value, 'to_device');
				}
				if ($properties[$i]["TITLE"] == "pulse")
				{
					$cmd = "zeroconf/pulse";
					$table='dev_sonoff_data';
					$pulseWidth=SQLSelectOne("SELECT * FROM $table WHERE DEVICE_ID=". $device['ID'] ." and TITLE = 'pulseWidth'");
					$params['pulse'] = $this->metricsModify($param, $value, 'to_device');
					$params['pulseWidth'] = intval($pulseWidth["VALUE"]);
				}
				if ($properties[$i]["TITLE"] == "pulseWidth")
				{
					$cmd = "zeroconf/pulse";
					$table='dev_sonoff_data';
					$pulse=SQLSelect("SELECT * FROM $table WHERE DEVICE_ID=". $device['ID'] ." and TITLE = 'pulse'");;	
					$params['pulse'] = $this->metricsModify($param, $pulse["VALUE"], 'to_device');;
					$params['pulseWidth'] = intval($value);
				}
				
				
				$res = $this->refDevice($device,$cmd, $params);
				
				if ($res["error"] == 1)
				{
					$data['online'] = '0';
					$this->updateData($device['MDNS_NAME'],$data);
				}
			} 
		 //================================LAN MODE================================//
		}
    }
   }
 }
 
 function processCycle() {
	$this->dev_sonoff_devices_cloudscan();
 }
 function wssRecv($recv, $sonoffws) {
	$this->dev_sonoff_devices_wss($recv, $sonoffws); 
 }
 
 
 function getWssUrl() {
	$this->getConfig();
	$url='wss://'.$this->config['WSS_API_URL'].':8080/api/ws';
	return $url;
 }
 function wssInit($sonoffws) {
	 $this->sonoffws=$sonoffws;
	 $this->wssGreatings();
 }
  
 function wssGreatings() {
	$this->getConfig();
	$payload['action']='userOnline';
	$payload['userAgent']='app';
	$payload['version']=6;
	$payload['nonce']=$this->sonoffws->generateKey(8, false);
	$payload['apkVesrion']=$this->config['APKVERSION'];
	$payload['os']=$this->config['OS'];
	$payload['at']=$this->config['TOKEN'];
	$payload['apikey']=$this->config['APIKEY'];
	$payload['ts']=time();
	$payload['model']= $this->config['MODEL'];
	$payload['romVersion']=$this->config['ROMVERSION'];
	$payload['sequence']=time()*1000;	
	$jsonstring=json_encode($payload);
	if($this->config['DEBUG']) debmes('[wss] --- '.$jsonstring, 'cycle_dev_sonoff_debug');
	if($this->sonoffws->isConnected()) {
		try {
            $this->sonoffws->send($jsonstring);
        } catch (BadOpcodeException $e) {
            echo 'Couldn`t sent: ' . $e->getMessage();
        }
	}
	
 }
 
 function metricsModify($param, $val, $out) {
	if($out=='to_device') { 
		if((strpos($param, 'switch')!==false || $param=='sledOnline') && $param!='switches') {
			$val=($val)? 'on' : 'off';
		} 
	} elseif($out=='from_device') {
		if((strpos($param, 'switch')!==false || $param=='sledOnline') && $param!='switches') {
			$val=($val=='on')? 1 : 0;
		} 
	}
	return $val;
 }
 
 function deviceRename($device) {
	$devid=$device['DEVICEID'];
	$this->getConfig();
	$host='https://'.$this->config['HTTPS_API_URL'].":8080/api/user/device/$devid";
	$payload['group']=' ';
	$payload['deviceid']=$devid;
	$payload['name']=$device['TITLE'];
	$payload['version']=$this->config['VERSION'];
	$payload['ts']=time();
	$payload['os']=$this->config['OS'];
	$payload['model']= $this->config['MODEL'];
	$payload['romVersion']=$this->config['ROMVERSION'];
	$payload['apkVesrion']=$this->config['APKVERSION'];
	
	include_once("./lib/websockets/sonoffws.class.php");
	$sonoffws = new SonoffWS($wssurl, $config);
	$payload['nonce']=$sonoffws->generateKey(8, false);
	$jsonstring=json_encode($payload);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $host);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	 "POST /api/user/device/$devid HTTP/1.1",
	 'Authorization: Bearer '.$this->config['TOKEN'],
	 'Content-Type: application/json',  
	 'Content-Length: ' . strlen($jsonstring)
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstring);
	$response = curl_exec($ch);
	curl_close($ch);
	if($this->config['DEBUG']) debmes('[http] --- '.$jsonstring, 'cycle_dev_sonoff_debug');
	if($this->config['DEBUG']) debmes('[http] +++ '.$response, 'cycle_dev_sonoff_debug');
	
 }
 
 function loginAuth($login, $pass) {
	$this->getConfig();
	$host='https://api.coolkit.cc:8080/api/user/login';
	//содержание файла, идущего со старыми версиями ewelink
	$appid_str="204,208,176,196,204,176,216,192,176,224,176,220,176,200,212,176,228,176,200,196,176,204,192,176,204,196,176,204,204,176,208,224,176,208,192,176,216,196,176,200,192,176,212,228,176,204,228,176,196,228,176,216,176,204,224,176,196,208,176,196,228,176,200,220,176,208,192,176,204,228,176,216,176,212,200,176,200,220,176,208,192,176,216,208,176,212,196,176,204,216 "; //app ID
	$key_str="216,200,176,212,200,176,208,212,176,200,220,176,204,204,176,200,204,176,208,204,176,208,216,176,216,204,176,204,224,176,216,204,176,204,216,176,196,200,176,208,204,176,212,212,176,196,208,176,200,212,176,204,196,176,204,216,176,208,192,176,204,220,176,200,200,176,220,176,200,212,176,204,192,176,204,224,176,216,196,176,216,196,176,204,192,176,212,228,176,208,196,176,212,196";//ключ
	$dict_str='ab!@#$ijklmcdefghBCWXYZ01234DEFGHnopqrstuvwxyzAIJKLMNOPQRSTUV5689%^&*()';//словарь
	//бъем на массивы
	$app_arr=explode(',', $appid_str);
	$key_arr=explode(',', $key_str);
	$dict_arr=str_split($dict_str);	
	//сдвигаем биты
	foreach($key_arr as $key=>$byte) {
		$key_arr[$key]=($byte >> 2);
	}
	foreach($app_arr as $key=>$byte) {
		$app_arr[$key]=($byte >> 2);
	}
	//ещё пару преобразований
    $indexes_str = implode(array_map("chr", $key_arr));
    $indexes_arr = explode(',', $indexes_str);
    $indexes2_str = implode(array_map("chr", $app_arr));
    $indexes2_arr = explode(',', $indexes2_str);
	//ищем индексы в словаре
	foreach($indexes_arr as $index) {$crypt_key.= $dict_arr[$index];}
	foreach($indexes2_arr as $index) {$appid.= $dict_arr[$index];}	
	//формируем запрос
	$payload['password']=$pass;
	$payload['email']=$login;
	$payload['version']=$this->config['VERSION'];
	$payload['ts']=time();
	$payload['os']=$this->config['OS'];
	$payload['model']= $this->config['MODEL'];
	$payload['romVersion']=$this->config['ROMVERSION'];
	$payload['apkVesrion']=$this->config['APKVERSION'];
	$payload['appid']=$appid;
	
	//генерация nonce
	include_once("./lib/websockets/sonoffws.class.php");
	$sonoffws = new SonoffWS($wssurl, $config);
	$payload['nonce']=$sonoffws->generateKey(8, false);
	$jsonstring=json_encode($payload);
	//финальная подпись ключем
	$sign=base64_encode(hash_hmac('sha256',$jsonstring,$crypt_key,true)); //получение конечной подписи

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $host);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	 'POST /api/user/login HTTP/1.1',
	 "Authorization: Sign $sign",
	 'Content-Type: application/json',  
	 'Content-Length: ' . strlen($jsonstring)
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstring);
	$response = curl_exec($ch);
	curl_close($ch);
	if($this->config['DEBUG']) debmes('[http] --- '.$jsonstring, 'cycle_dev_sonoff_debug');
	if($this->config['DEBUG']) debmes('[http] +++ '.$response, 'cycle_dev_sonoff_debug');	 
	$json_resp=json_decode($response, TRUE);	
	return $json_resp;
 }
 
 function getWssSrv($reg, $at) {
	$this->getConfig();
	$host="https://$reg-disp.coolkit.cc:8080/dispatch/app";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $host);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	 'POST /dispatch/app HTTP/1.1',
	 "Authorization: Bearer $at",
	 'Content-Type: application/json'
	)); 
	
	$payload['accept']=$pass;
	$payload['email']=$login;
	$payload['version']=$this->config['VERSION'];
	$payload['ts']=time();
	$payload['os']=$this->config['OS'];
	$payload['model']= $this->config['MODEL'];
	$payload['romVersion']=$this->config['ROMVERSION'];
	$payload['apkVesrion']=$this->config['APKVERSION'];
	$payload['appid']=$appid;

	//генерация nonce
	include_once("./lib/websockets/sonoffws.class.php");
	$sonoffws = new SonoffWS($wssurl, $config);
	$payload['nonce']=$sonoffws->generateKey(8, false);
	$jsonstring=json_encode($payload);	
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonstring);
	$response = curl_exec($ch);
	curl_close($ch);	 
	
	if($this->config['DEBUG']) debmes('[http] --- '.$jsonstring, 'cycle_dev_sonoff_debug');
	if($this->config['DEBUG']) debmes('[http] +++ '.$response, 'cycle_dev_sonoff_debug');
	
	$resp=json_decode($response, TRUE);
	return $resp['domain'];
 }
 
 
 //================================LAN MODE================================//
  function processLanCycle($mdns) {
    $this->getConfig();
    // Search for devices
	// For a bit more surety, send multiple search requests
	//$mdns->query("_ewelink._tcp.local",1,12,"");
	//$mdns->query("_ewelink._tcp.local",1,16,"");
	//$mdns->query("_ewelink._tcp.local",1,33,"");
    $inpacket = $mdns->readIncoming();
    //print_r ($inpacket);
    //echo '<br>';
	//$mdns->printPacket($inpacket);
    // If our packet has answers, then read them
	if (sizeof($inpacket->answerrrs)> 0) {
		for ($x=0; $x < sizeof($inpacket->answerrrs); $x++) {
            if (strpos($inpacket->answerrrs[$x]->name ,"_ewelink._tcp.local")  === false &&
                strpos($inpacket->answerrrs[$x]->name ,"eWeLink")  === false)
                continue;
            //echo date('Y-m-d H:i:s')." ".$inpacket->answerrrs[$x]->name . "\n";
            $name = substr($inpacket->answerrrs[$x]->name, 0, strpos($inpacket->answerrrs[$x]->name,'.'));
            //echo date('Y-m-d H:i:s')." ".$name . "\n";
			//print_r($inpacket->answerrrs[$x]);
			//DebMes($inpacket->answerrrs[$x], 'sonoff_diy');
            // PTR
			if ($inpacket->answerrrs[$x]->qtype == 12) {
                if ($inpacket->answerrrs[$x]->name == "_ewelink._tcp.local") {
					$name = "";
					for ($y = 0; $y < sizeof($inpacket->answerrrs[$x]->data); $y++) {
						$nameMDNS .= chr($inpacket->answerrrs[$x]->data[$y]);
					}
                    $name = substr($nameMDNS, 0, strpos($nameMDNS,'.'));
                    //print_r($name);
					DebMes($name . ' qtype='.$inpacket->answerrrs[$x]->qtype . " nameDns=".$nameMDNS, 'sonoff_lan');
                    // add device 
                    $this->updateDevice($name,"","");
					// Send a a SRV query
					$mdns->query($nameMDNS, 1, 16, "");
				}
			}
            // TXT data
            if ($inpacket->answerrrs[$x]->qtype == 16) {
                //print_r($inpacket->answerrrs[$x]->data);
                $d = array();
                for ($y = 0; $y < sizeof($inpacket->answerrrs[$x]->data); $y++) {
                    $len = $inpacket->answerrrs[$x]->data[$y];
                    $c = $y;
                    $kv = false;
                    $key ="";
                    $value = "";
                    ++$y;
                    while ($y<=$c+$len){
                        $ch = chr($inpacket->answerrrs[$x]->data[$y]);
                        if ($ch == '=')
                            $kv = true;
                        else {
                            if (!$kv)
                                $key .= $ch;
                            else
                                $value .= $ch;
                        }
                        ++$y;
                    }
                    --$y;
                    $d[$key] = $value;
                }
				
				DebMes($name. " txt=" .json_encode($d), 'sonoff_lan');
                $this->updateDevice($name,"DEVICEID",$d['id']);
                $this->updateDevice($name,"UPDATED",date('Y-m-d H:i:s'));
                                
                $df = $d['data1'];
                if (array_key_exists('data2', $d)) $df = $df.$d['data2'];
                if (array_key_exists('data3', $d)) $df = $df.$d['data3'];
                
                //update data device
                if ($d["encrypt"] == "true")
                {
                    $this->updateDevice($name,"DEVICE_MODE",1);
                    $table_name='dev_sonoff_devices';
                    $device=SQLSelectOne("SELECT * FROM $table_name WHERE MDNS_NAME='$name'");
                    $data = json_decode($this->decrypt($device['DEVICEKEY'] ,$d["iv"],$df),true);
                }
                else
                {
                    $this->updateDevice($name,"DEVICE_MODE",2);
                    $data = json_decode($df,true);
                }
                DebMes($name. " decode=" .json_encode($data), 'sonoff_lan');
                if ($d["type"] == 'strip')
				{
                    foreach ($data['switches'] as $key => $val)
					{
						$data['switch'.$val['outlet']] = $val['switch'];
					}
					foreach ($data['configure'] as $key => $val)
					{
						$data['startup'.$val['outlet']] = $val['startup'];
					}
					unset($data['switches']);
					unset($data['pulses']);
					unset($data['configure']);
				}
                $data["online"] = 1;
				//print_r($data);
				DebMes($name. " data=" .json_encode($data), 'sonoff_lan');
                //DebMes($data, 'sonoff_diy');
                $this->updateData($name,$data);
			}
            // SRV
			if ($inpacket->answerrrs[$x]->qtype == 33) {
				$d = $inpacket->answerrrs[$x]->data;
				$port = ($d[4] * 256) + $d[5];
				// We need the target from the data
				$offset = 6;
				$size = $d[$offset];
				$offset++;
				$target = "";
				for ($z=0; $z < $size; $z++) {
					$target .= chr($d[$offset + $z]);
				}
				$target .= ".local";
                // update $port device
				//$port  $target
                //echo "PORT ".$port." ".  $name."\n";
				DebMes($name. " port=" .$port, 'sonoff_lan');
                $this->updateDevice($name,"PORT",$port);
				// We know the name and port. Send an A query for the IP address
				$mdns->query($target,1,1,"");
			}
            // A
			if ($inpacket->answerrrs[$x]->qtype == 1) {
				$d = $inpacket->answerrrs[$x]->data;
				$ip = $d[0] . "." . $d[1] . "." . $d[2] . "." . $d[3];
                // update $IP device
                //echo "IP ".$ip." ".  $name."\n";
				DebMes($name. " ip=" .$ip, 'sonoff_lan');
                $this->updateDevice($name,"IP",$ip);

			}
		}
	}
  
 }
 function checkLanAlive() {
    $this->getConfig();
    $table_name='dev_sonoff_devices';
    $devices=SQLSelect("SELECT * FROM $table_name");
    $total=count($devices);
    for($i=0;$i<$total;$i++) {
        $cmd = "zeroconf/info";
        $params = array();
        $res = $this->refDevice($devices[$i],$cmd,$params);
        if ($res["error"] == 1)
        {
            $data = array();
            $data['online'] = '0';
            $data['error'] = $res["data"]["message"];
            if ($devices[$i]['DEVICE_MODE'] == 1 && $devices[$i]['DEVICEKEY']=='')
                $data['error'] = $data['error'] . " (maybe wrong device key)";
            $this->updateData($devices[$i]['MDNS_NAME'],$data);
        }
        else
        {
            $data = array();
            $data['online'] = '1';
            $data['error'] = '';
            $this->updateData($devices[$i]['MDNS_NAME'],$data);
        }
    }
 }
 function updateDevice($name, $key, $value)
 {
	$table_name='dev_sonoff_devices';
	//вписываем mdns name, если не вписан
	if($key=='DEVICEID') {
		$rec=SQLSelectOne("SELECT * FROM $table_name WHERE $key='$value'");
		if($rec['MDNS_NAME'] != $name) {
			$rec['MDNS_NAME'] = $name;
			SQLUpdate($table_name, $rec);
		}
	}
	//пишем другие параметры
    $rec=SQLSelectOne("SELECT * FROM $table_name WHERE MDNS_NAME='$name'");
	if($key=='DEVICE_MODE') {
		if($rec['DEVICE_MODE']=='off') return;
	}
    if ($key!="")
    {
        if ($rec[$key] != $value)
        {
            $rec[$key] = $value;
            SQLUpdate($table_name, $rec);
        }
    }
 }
 
 function generate_iv()
 {
    $iv = random_bytes(16);
    return base64_encode($iv);
 }        
 
 function encrypt($device_key, $iv, $data)
 {
    $key = md5($device_key, true);
    $encodedData = base64_encode(openssl_encrypt($data, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, base64_decode($iv)));
    return $encodedData;    
 } 
 
 function decrypt($device_key, $iv, $data)
 {
    $key = md5($device_key, true);
    $decryptedData = openssl_decrypt(base64_decode($data), 'aes-128-cbc', $key, OPENSSL_RAW_DATA, base64_decode($iv));
    return $decryptedData;
 }
 function refDevice($device, $cmd, $params)
{
    $ip = $device["IP"];
    $port = $device["PORT"];
    
    $url = "http://$ip:$port/$cmd";
    
    DebMes($name. " params=" .json_encode($params), 'sonoff_lan');
            
    $data = array();
    $data['deviceid'] = $device['DEVICEID'];
    if ($device['DEVICEKEY']!='')
    {
        $data['encrypt']=true;
        $data['sequence']=strval(time());
        $data['selfApiKey']=$device['DEVICEKEY'];
        $iv = $this->generate_iv();
        $data['iv']=$iv;
        if (empty($params))
            $str_params = "{}";
        else
            $str_params = json_encode($params);
        $data['data'] = $this->encrypt($device['DEVICEKEY'],$iv,$str_params);
    }
    else
        $data['data'] = $params;
    
    return $this->sendRequest($url, $data);
}
 
function sendRequest($url, $params = 0)
{
    try
    { 
        $data_string = json_encode($params);
        $ch = curl_init($url); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );

        DebMes('API request - '.$url.' => '. $data_string, 'sonoff_lan');
        
        $result = curl_exec($ch);
        DebMes('API responce - '.$url.' => '. $result, 'sonoff_lan');
        //echo $result . "\n";
        if ($result == "")
        {
            $result = array();
            $result["error"] = 1;
            $result["data"] = array();
            $result["data"]["message"] = "Empty responce result";
        }
        else
            $result = json_decode($result,true);
    }
    catch (Exception $e)
    {
		DebMes('API error - '.$url.' => '. get_class($e) . ', ' . $e->getMessage(), 'sonoff_lan');
        $result = array();
        $result["error"] = 1;
        $result["data"] = array();
        $result["data"]["class"] = get_class($e);
        $result["data"]["message"] = $e->getMessage();
    } 
    return $result;
}
 function updateData($name, $data)
 {
    $table_name='dev_sonoff_devices';
    $rec=SQLSelectOne("SELECT * FROM $table_name WHERE MDNS_NAME='$name'");
    if($rec['DEVICE_MODE']==1) {
		if ($rec['ID']) {
			//print_r($rec);
			$table_name='dev_sonoff_data';
			$id = $rec['ID'];
			$values=SQLSelect("SELECT * FROM $table_name WHERE DEVICE_ID='$id'");
			foreach ($data as $key => $val)
			{
				$value_ind = array_search($key, array_column($values, 'TITLE'));
				if ($value_ind !== False)
					$value = $values[$value_ind];
				else
					$value = array();
				$value["TITLE"] = $key;
				$value["DEVICE_ID"] = $rec['ID'];
				$value["UPDATED"] = date('Y-m-d H:i:s');
				if ($value['ID']) {
					if ($value["VALUE"] != $val)
					{   
						$value["VALUE"] = $val;
						SQLUpdate($table_name, $value);
						if ($value['LINKED_OBJECT'] && $value['LINKED_PROPERTY']) {
							setGlobal($value['LINKED_OBJECT'] . '.' . $value['LINKED_PROPERTY'], $this->metricsModify($key, $val, 'from_device'), array($this->name => '0'));
						}
					}
				}
				else{
					$value["VALUE"] = $val;
					SQLInsert($table_name, $value);
				}
			}
		}
	}
 }

 //================================LAN MODE================================//
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
  $this->getConfig();
  $this->config['VERSION']=6;
  $this->config['APKVERSION']='1.8';
  $this->config['OS']='ios';
  $this->config['MODEL']='iPhone10,6';
  $this->config['ROMVERSION']='11.1.2';
  $this->saveConfig();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS dev_sonoff_devices');
  SQLExec('DROP TABLE IF EXISTS dev_sonoff_data');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
dev_sonoff_devices - 
dev_sonoff_data - 
*/
  $data = <<<EOD
 dev_sonoff_devices: ID int(10) unsigned NOT NULL auto_increment
 dev_sonoff_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 dev_sonoff_devices: DEVICEID varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_devices: BRANDNAME varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_devices: PRODUCTMODEL varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_devices: UIID varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_devices: DEVICEKEY varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_devices: MDNS_NAME varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_devices: IP varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_devices: PORT varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_devices: DEVICE_MODE varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_devices: UPDATED datetime
 dev_sonoff_data: ID int(10) unsigned NOT NULL auto_increment
 dev_sonoff_data: TITLE varchar(100) NOT NULL DEFAULT ''
 dev_sonoff_data: VALUE varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_data: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 dev_sonoff_data: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 dev_sonoff_data: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 dev_sonoff_devices: UPDATED datetime
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTm92IDEzLCAyMDE4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
