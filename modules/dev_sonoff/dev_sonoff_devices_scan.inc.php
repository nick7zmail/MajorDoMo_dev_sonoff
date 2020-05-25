<?php
$this->getConfig();

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
	include_once("./lib/websockets/sonoffws.class.php");
	$sonoffws = new SonoffWS($wssurl, $config);
	$nonce=$sonoffws->generateKey(8, false);

//. '?lang=en&apiKey=' . $this->config['APIKEY'] . '&version=' . $this->config['VERSION'] . '&ts=' . time() . '&nonce=' . $this->sonoffws->generateKey(8, false) . '&appid=' . $appid . '&os=' . $this->config['OS'] . '&model=' . $this->config['MODEL'] . '&romVersion=' . $this->config['ROMVERSION'] . '&appVersion=' . $this->config['APKVERSION']
$host='https://'.$this->config['HTTPS_API_URL'].':8080/api/user/device'. '?lang=en&apiKey=' . $this->config['APIKEY'] . '&version=' . $this->config['VERSION'] . '&ts=' . time() . '&nonce=' . $nonce . '&appid=' . $appid . '&os=' . $this->config['OS'] . '&model=' . $this->config['MODEL'] . '&romVersion=' . $this->config['ROMVERSION'] . '&appVersion=' . $this->config['APKVERSION'];
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
	foreach($decoded_res['devicelist'] as $device) {
		$this->config['APIKEY']=$device['apikey'];
		$rec['TITLE']=$device['name'];
		$rec['DEVICEID']=$device['deviceid'];
		$id=$device['deviceid'];
		$rec['BRANDNAME']=$device['brandName'];
		$rec['PRODUCTMODEL']=$device['productModel'];
		$rec['UIID']=$device['uiid'];
		$rec['DEVICEKEY']=$device['devicekey'];
		$findrec=SQLSelectOne("SELECT ID FROM dev_sonoff_devices WHERE DEVICEID='$id'");
		$rec['UPDATED']=date('Y-m-d H:i:s');
		if($findrec['ID']) {
			$rec['ID']=$findrec['ID'];
			if(!$findrec['DEVICE_MODE'] || $findrec['DEVICE_MODE']=='off') {
				SQLUpdate('dev_sonoff_devices', $rec);
			}
		} else {
			unset($rec['ID']);
			$rec['ID']=SQLInsert('dev_sonoff_devices', $rec);
		}
		if(!$findrec['DEVICE_MODE'] || $findrec['DEVICE_MODE']=='off') {
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
							sg($findparam['LINKED_OBJECT'].'.'.$findparam['LINKED_PROPERTY'], $this->metricsModify($param, $val, 'from_device'), array($this->name => '0'), '[https] Cloud cycle');
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
						$rec_params['DEVICE_ID']=$rec['ID'];
						$rec_params['TITLE']='switch.'.$devpart['outlet'];
						$rec_params['VALUE']=$devpart['switch'];
						foreach ($findparams as $findparam) {
							if($rec_params['TITLE']==$findparam['TITLE']) {
								$need_insert=false;
								$rec_params['ID']=$findparam['ID'];
								if(isset($findparam['LINKED_OBJECT']) && isset($findparam['LINKED_PROPERTY'])) {
									sg($findparam['LINKED_OBJECT'].'.'.$findparam['LINKED_PROPERTY'], $this->metricsModify($rec_params['TITLE'], $devpart['switch'], 'from_device'), array($this->name => '0'), '[https] Cloud cycle (2+ch)');
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

	$this->saveConfig();
}
?>
