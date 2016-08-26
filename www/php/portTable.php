<?php
	$act = $_POST['action'];
	$data_post = $_POST["raw"]["data"][0];
	$data_row = $_POST["raw"]["data"][0]["DT_RowId"];
	
	$json_old_raw = file_get_contents("/www/libs/data/port_forwarding.json");
	$json_old = json_decode($json_old_raw, true);
	$json_new = array();
	$length	 = count($json_old["aaData"]) - 1;


	function remove() {
		global $data_row, $json_old, $json_new;
		$string_to_remove = $data_row;
		foreach ($json_old["aaData"] as $key => $value) {
			if ($value["DT_RowId"] != $string_to_remove) {
				$json_new[] = $value; 
			}
		}
		
		$json_old["aaData"] = $json_new;
		$data = json_encode($json_old, true);
		file_put_contents("/www/libs/data/port_forwarding.json", $data);
		echo "Portforwarding rule has been removed.";	
	}

	function edit() {
		global $data_val, $data_row, $json_old, $json_new, $length;
		$string_to_edit = $data_row;
		foreach ($json_old["aaData"] as $key => $value) {
			if ($value["DT_RowId"] == $string_to_edit) {
				if ($value["DT_RowId"] == $string_to_edit) {
 					$value["status"] = $data_row["status"];
 					$value["protocol"] = $data_row["protocol"];
 					$value["gateway"] = $data_row["gateway"];
 					$value["src"] = $data_row["src"];
 					$value["int"] = $data_row["int"];
 					$value["ext"] = $data_row["ext"];
 					$value["address"] = $data_row["address"];
 					$value["description"] = $data_row["description"];
					$res = "Portforwarding rule has been changed.";
 				}
 				$json_new[] = $value;
			}
		}
		$json_old["aaData"] = $json_new;
		$data = json_encode($json_old, true);
		file_put_contents("/www/libs/data/port_forwarding.json", $data);
		echo $res;		
	}

	function add() {
		global $data_val, $data_post, $json_old, $json_new, $length;
		$string_to_add = $data_row;

		foreach ($json_old["aaData"] as $key => $value) {
			$json_new[] = $value;
			if ($key == $length) {
				$json_new[] = $data_post;
				$res = "New portforwarding rule has been added.";
			} 
		}
		$json_old["aaData"] = $json_new;
		$data = json_encode($json_old, true);
		file_put_contents("/www/libs/data/port_forwarding.json", $data);
		echo $res;
	}

	switch ($act) {
		case 'deleteRow':
			remove();
			break;
		case 'editRow':
			edit();
			break;
		case 'addRow':
			add();
			break;
		default:
			echo "Something went wrong!";
			break;
	}
?>