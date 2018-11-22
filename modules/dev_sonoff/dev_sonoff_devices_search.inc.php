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
  $res_online=SQLSelect("SELECT DEVICE_ID, VALUE FROM dev_sonoff_data WHERE TITLE='online'");
  if ($res[0]['ID']) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
	foreach($res_online as $id_online){
		if($res[$i]['ID']==$id_online['DEVICE_ID'] && $id_online['VALUE']==1) $res[$i]['ONLINE']='1';   
	}
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['UPDATED']);
    $res[$i]['UPDATED']=fromDBDate($tmp[0])." ".$tmp[1];
   }
   $out['RESULT']=$res;
  }
