<?php
// Sabai Technology - Apache v2 licence
// copyright 2014 Sabai Technology, LLC

header('Content-Type: application/javascript');

$act=$_REQUEST['act'];

switch ($act) {
	case "proxystart":
        exec("sh /www/bin/proxy.sh $act");
		echo "res={ sabai: true, msg: 'Proxy starting' }";
			break;
	case "proxystop":
        exec("sh /www/bin/proxy.sh $act");
		echo "res={ sabai: true, msg: 'Proxy stopped' }";
		    break;
}

?>
