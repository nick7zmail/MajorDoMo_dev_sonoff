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
 $out['POLL_PERIOD']=$this->config['POLL_PERIOD'];
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
   $this->config['POLL_PERIOD']=intval(gr('poll_period'));
   $this->config['DEBUG']=gr('debug');
   $this->config['VERSION']=intval(gr('version'));
   $this->config['APKVERSION']=gr('apkversion');
   $this->config['OS']=gr('os');
   $this->config['MODEL']=gr('model');
   $this->config['ROMVERSION']=gr('romVersion');
   $this->config['HTTPS_API_URL'] = $api_url;

   if(!intval(gr('version'))) $out['ERR_VERSION']=1;

   if(!intval(gr('poll_period'))) $out['ERR_POLL_PERIOD']=1;

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
			$device=SQLSelectOne("SELECT DEVICEID FROM dev_sonoff_devices WHERE ID='$dev_id'");
			$param=$properties[$i]['TITLE'];
			
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
			
			
			if($this->config['DEBUG']) {
				$recv=$this->sonoffws->receive();
				debmes('[wss] +++ '.$recv, 'cycle_dev_sonoff_debug');
			}
			$sonoffws->close();
    }
   }
 }
 function processCycle() {
 $this->dev_sonoff_devices_cloudscan();
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
	
	if($this->config['DEBUG']) {
		$recv=$this->sonoffws->receive();
		debmes('[wss] +++ '.$recv, 'cycle_dev_sonoff_debug');
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
 dev_sonoff_devices: UPDATED datetime
 dev_sonoff_data: ID int(10) unsigned NOT NULL auto_increment
 dev_sonoff_data: TITLE varchar(100) NOT NULL DEFAULT ''
 dev_sonoff_data: VALUE varchar(255) NOT NULL DEFAULT ''
 dev_sonoff_data: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 dev_sonoff_data: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 dev_sonoff_data: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
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
