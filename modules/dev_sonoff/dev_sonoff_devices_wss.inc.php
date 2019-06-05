<?php
$this->getConfig();
/*if($recv=='ping') {
	$sonoffws->send('pong');
	return false;
}*/
$decoded_res=json_decode($recv, TRUE);
if($decoded_res['action']=='sysmsg') {
	
} 

if($decoded_res['action']=='update' ) {
	$device=$decoded_res;

	$rec['DEVICEID']=$device['deviceid'];
	$id=$device['deviceid'];
	$findrec=SQLSelectOne("SELECT ID FROM dev_sonoff_devices WHERE DEVICEID='$id'");
	if($findrec['ID']) {
	$rec['UPDATED']=date('Y-m-d H:i:s');
	SQLUpdate('dev_sonoff_devices', $rec);
	$id=$findrec['ID'];
	$findparams=SQLSelect("SELECT * FROM dev_sonoff_data WHERE DEVICE_ID='$id'");
		foreach($device['params'] as $param=>$val) {
			$rec_params['DEVICE_ID']=$id;
			$rec_params['TITLE']=$param;
			$rec_params['VALUE']=$val;
			$need_insert=true;
			unset($rec_params['ID']);
			foreach ($findparams as $findparam) {
				if($rec_params['TITLE']==$findparam['TITLE']) {
					$need_insert=false;
					$rec_params['ID']=$findparam['ID'];
					if(isset($findparam['LINKED_OBJECT']) && isset($findparam['LINKED_PROPERTY'])) {
						sg($findparam['LINKED_OBJECT'].'.'.$findparam['LINKED_PROPERTY'], $this->metricsModify($param, $val, 'from_device'), array($this->name => '0'));
					}
				}
			}
			if($need_insert) {
				sqlInsert('dev_sonoff_data', $rec_params);
			} else {
				SQLUpdate('dev_sonoff_data', $rec_params);
			}
			
			if($param=='switches') {			
				foreach ($val as $devpart) {
					$need_insert=true;
					unset($rec_params['ID']);	
					$rec_params['DEVICE_ID']=$id;
					$rec_params['TITLE']='switch.'.$devpart['outlet'];
					$rec_params['VALUE']=$devpart['switch'];
					foreach ($findparams as $findparam) {
						if($rec_params['TITLE']==$findparam['TITLE']) {
							$need_insert=false;
							$rec_params['ID']=$findparam['ID'];
							if(isset($findparam['LINKED_OBJECT']) && isset($findparam['LINKED_PROPERTY'])) {
								sg($findparam['LINKED_OBJECT'].'.'.$findparam['LINKED_PROPERTY'], $this->metricsModify($rec_params['TITLE'], $devpart['switch'], 'from_device'), array($this->name => '0'));
							}
						}
					}
					if($need_insert) {
						sqlInsert('dev_sonoff_data', $rec_params);
					} else {
						SQLUpdate('dev_sonoff_data', $rec_params);
					}
				}
			}
			
			if($param=='rfList') {			
				$need_insert=true;
				unset($rec_params['ID']);	
				$rec_params['DEVICE_ID']=$id;
				$rec_params['TITLE']='rfsend';
				foreach ($findparams as $findparam) {
					if($rec_params['TITLE']==$findparam['TITLE']) {
						$need_insert=false;
						$rec_params['ID']=$findparam['ID'];
					}
				}
				if($need_insert) {
					sqlInsert('dev_sonoff_data', $rec_params);
				} else {
					SQLUpdate('dev_sonoff_data', $rec_params);
				}
				
				$need_insert=true;
				unset($rec_params['ID']);	
				$rec_params['DEVICE_ID']=$rec['ID'];
				$rec_params['TITLE']='rflearn';
				foreach ($findparams as $findparam) {
					if($rec_params['TITLE']==$findparam['TITLE']) {
						$need_insert=false;
						$rec_params['ID']=$findparam['ID'];
					}
				}
				if($need_insert) {
					sqlInsert('dev_sonoff_data', $rec_params);
				} else {
					SQLUpdate('dev_sonoff_data', $rec_params);
				}
			}
			
		}
	}
}
if($decoded_res['action']=='share') {
	//addToOperationsQueue($this->name, 'device_to_add', $decoded_res['deviceid']);
	$payload['result']=2;
	$payload['deviceid']=$decoded_res['deviceid'];
	$payload['apikey']=$this->config['APIKEY'];
	$payload['error']=0;
	$payload['userAgent']='app';
	$payload['sequence']=time()*1000;
	$payload['withoutTimeout']=true;
	$jsonstring=json_encode($payload);
	$sonoffws->send($jsonstring);
	if($this->config['DEBUG']) debmes('[wss] --- '.$jsonstring, 'cycle_dev_sonoff_debug');
	say('Пользователь '.$decoded_res['userName'].' поделился с вами устройством '.$decoded_res['deviceName'].'. Предложение принято в автоматическом режиме.');
} 
?>