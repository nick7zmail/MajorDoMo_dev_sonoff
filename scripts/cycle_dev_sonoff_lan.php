<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
include_once("./lib/websockets/sonoffws.class.php");
set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
$ctl = new control_modules();
include_once(DIR_MODULES . 'dev_sonoff/dev_sonoff.class.php');
$dev_sonoff_module = new dev_sonoff();
$dev_sonoff_module->getConfig();
$tmp = SQLSelectOne("SELECT ID FROM dev_sonoff_devices LIMIT 1");
if (!$tmp['ID'])
   exit; // no devices added -- no need to run this cycle

include_once(DIR_MODULES . 'dev_sonoff/mdns.php'); 
$mdns=new mDNS();

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;
//переменные
$latest_check=0;
$checkEvery=5*60;

while (1)
{
   setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   $dev_sonoff_module->processLanCycle($mdns);
   if ((time()-$latest_check)>$checkEvery) {
    $latest_check=time();
    echo date('Y-m-d H:i:s')." Check devices...\n";
    $dev_sonoff_module->checkLanAlive();
   }
   if (file_exists('./reboot') || IsSet($_GET['onetime']))
   {
      $db->Disconnect();
      exit;
   }
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));
