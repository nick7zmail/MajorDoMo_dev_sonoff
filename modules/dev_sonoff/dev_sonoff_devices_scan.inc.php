<?php
$this->getConfig();
$host='https://'.$this->config['HTTPS_API_URL'].':8080/api/user/device';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $host);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
 'GET /api/user/device HTTP/1.1',
 'Authorization: Bearer '.$this->config['TOKEN'],
 'Content-Type: application/json'
)); 
$response = curl_exec($ch);
curl_close($ch);
if($this->config['DEBUG']) debmes('[http] +++ '.$response, 'cycle_dev_sonoff_debug');
$decoded_res=json_decode($response, TRUE);
if(!$decoded_res['error']){
	foreach($decoded_res as $device) {
		$this->config['APIKEY']=$device['apikey'];
		$rec['TITLE']=$device['name'];
		$rec['DEVICEID']=$device['deviceid'];
		$id=$device['deviceid'];
		$rec['BRANDNAME']=$device['brandName'];
		$rec['PRODUCTMODEL']=$device['productModel'];
		$rec['UIID']=$device['uiid'];
		$findrec=SQLSelectOne("SELECT ID FROM dev_sonoff_devices WHERE DEVICEID='$id'");
		$rec['UPDATED']=date('Y-m-d H:i:s');
		if($findrec['ID']) {
			$rec['ID']=$findrec['ID'];
			SQLUpdate('dev_sonoff_devices', $rec);
		} else {
			unset($rec['ID']);
			$rec['ID']=SQLInsert('dev_sonoff_devices', $rec);
		}
		$id=$rec['ID'];
		$findparams=SQLSelect("SELECT * FROM dev_sonoff_data WHERE DEVICE_ID='$id'");
		$device['params']['online']=$device['online'];
		$device['params']['cmdline']='';
		foreach($device['params'] as $param=>$val) {
			$rec_params['DEVICE_ID']=$rec['ID'];
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
					$rec_params['ID']='';	
					$rec_params['DEVICE_ID']=$rec['ID'];
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
				$rec_params['ID']='';	
				$rec_params['DEVICE_ID']=$rec['ID'];
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
				$rec_params['ID']='';	
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

	$this->saveConfig();
}
?>
