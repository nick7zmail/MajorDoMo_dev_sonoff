<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['dev_sonoff_devices_qry'];
  } else {
   $session->data['dev_sonoff_devices_qry']=$qry;
  }
  if (!$qry) $qry="1";
  $sortby_dev_sonoff_devices="ID DESC";
  $out['SORTBY']=$sortby_dev_sonoff_devices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM dev_sonoff_devices WHERE $qry ORDER BY ".$sortby_dev_sonoff_devices);
  $res_online=SQLSelect("SELECT DEVICE_ID, VALUE, TITLE FROM dev_sonoff_data WHERE TITLE='online' OR TITLE='rssi' OR TITLE='gsm_rssi'");
  if ($res[0]['ID']) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
	foreach($res_online as $id_online){
		if($res[$i]['ID']==$id_online['DEVICE_ID'] && $id_online['VALUE']==1 && $id_online['TITLE']=='online') $res[$i]['ONLINE']='1';
		if($res[$i]['ID']==$id_online['DEVICE_ID'] && ($id_online['TITLE']=='rssi' || $id_online['TITLE']=='gsm_rssi')) {
			if($res[$i]['ID']==$id_online['DEVICE_ID'] && $id_online['TITLE']=='rssi') {
				if ($id_online['VALUE']>= -50) {$res[$i]['RSSI_LVL']=100; $res[$i]['RSSI_COLOR']='#5cb85c';} 
				elseif ($id_online['VALUE']>= -61) {$res[$i]['RSSI_LVL']=77; $res[$i]['RSSI_COLOR']='#f0ad4e';} 
				elseif ($id_online['VALUE']>= -72) {$res[$i]['RSSI_LVL']=52; $res[$i]['RSSI_COLOR']='#f0ad4e';} 
				elseif ($id_online['VALUE']>= -83) {$res[$i]['RSSI_LVL']=26; $res[$i]['RSSI_COLOR']='#d9534f';} 
				else {$res[$i]['RSSI_LVL']=0; $res[$i]['RSSI_COLOR']='#d9534f';}
			}
		}
	}
	$res[$i]['IMG']='/img/sonoff/'.$res[$i]['UIID'].'.jpg';
	if (!file_exists(ROOT.$res[$i]['IMG'])) $res[$i]['IMG']='/img/sonoff/unknown.png';
    // some action for every record if required
	$res[$i]['UPDATED']=date( 'H:i d/m', strtotime($res[$i]['UPDATED']) );
    //$tmp=explode(' ', $res[$i]['UPDATED']);
    //$res[$i]['UPDATED']=fromDBDate($tmp[0])." ".$tmp[1];
   }
   $out['RESULT']=$res;
  }
