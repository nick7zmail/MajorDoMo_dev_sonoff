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
 if (!$out['HTTPS_API_URL']) {
  $out['HTTPS_API_URL']='https://';
 }
 $out['HTTPS_API_URL']=$this->config['HTTPS_API_URL'];
 $out['WSS_API_URL']=$this->config['WSS_API_URL'];
 $out['TOKEN']=$this->config['TOKEN'];
 $out['POLL_PERIOD']=$this->config['POLL_PERIOD'];
 $out['APIKEY']=$this->config['APIKEY'];
 
 if ($this->view_mode=='update_settings') {
   $this->config['HTTPS_API_URL']=gr('https_api_url');
   $this->config['WSS_API_URL']=gr('wss_api_url');
   $this->config['TOKEN']=gr('token');
   $this->config['POLL_PERIOD']=intval(gr('poll_period'));
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
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
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
 function wssGreatings($sonoffws) {
	$this->getConfig();
	
	$payload['action']='userOnline';
	$payload['userAgent']='app';
	$payload['version']=6;
	$payload['nonce']=base64_encode($sonoffws->generateKey(8));
	$payload['apkVesrion']="1.8";
	$payload['os']='ios';
	$payload['at']=$this->config['TOKEN'];
	$payload['apikey']=$this->config['APIKEY'];
	$payload['ts']=time();
	$payload['model']='iPhone10,6';
	$payload['romVersion']='11.1.2';
	$payload['sequence']=time()*1000;	
	$jsonstring=json_encode($payload);
	if($sonoffws->isConnected()) {
		try {
            $sonoffws->send($jsonstring);
        } catch (BadOpcodeException $e) {
            echo 'Couldn`t sent: ' . $e->getMessage();
        }
	}
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
 dev_sonoff_devices: TYPE varchar(255) NOT NULL DEFAULT ''
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
