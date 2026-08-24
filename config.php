<?php
if (!function_exists('env_value')) {
  function env_value($name, $default = '') {
    $value = getenv($name);
    return $value === false ? $default : trim($value);
  }
}

ob_start();
date_default_timezone_set('Africa/Nairobi');
session_start();

require_once('initialize.php');
require_once('connection.php');
$db = new DBConnection;
$conn = $db->conn;

function timeConv($time) {
  return date('d M, Y - h:i A', strtotime($time));
}

function validateimage($image){

	if(file_exists($image)){
		return $image;
	}else{
	return 'assets/images/no-image.webp';
}

}

function order_status($status){
	switch($status) {
		case '1':
		return '<span class="badge bg-primary">Confirmed</span>';
		break;

		case '2': 
			return '<span class="badge bg-danger">Rejected</span>';
			break;

		case '3': 
			return '<span class="badge bg-warning">Out of stock</span>';
			break;

		case '4': 
			return '<span class="badge bg-dark">Shipping</span>';
			break;

		case '5': 
			return '<span class="badge bg-success">Delivered</span>';
			break;

		default :
		return '<span class="badge bg-secondary">Pending</span>';
	}
}

ob_end_flush();
?>