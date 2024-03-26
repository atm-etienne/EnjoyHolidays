<?php

$res = 0;

$res = @include("../../main.inc.php");
if (!$res) {
	$res = @include("../../../main.inc.php");
}
if (!$res) die("Include of master fails");

top_httphead('application/json');


$action = GETPOST('action', 'aZ09');
$apiResult = [];

switch ($action) {
	case 'getDefaultCountryTravelPrice':
		$countryId = GETPOST('countryId', 'aZ09');
		$apiResult = array_merge_recursive($apiResult, getDefaultCountryTravelPrice($countryId));
		break;
	case 'getTravelPackagesList':
		$apiResult = array_merge_recursive($apiResult, getTravelPackagesList());
		break;
	default:
		http_response_code(404);
		$apiResult = array_merge_recursive($apiResult, [
			'statusCode'	=> 404,
			'error'		=> 'Action not found'
		]);
}

echo json_encode($apiResult);
exit();

function getDefaultCountryTravelPrice($countryId) {
	global $db, $conf, $user;

	if (!$user->hasRight('enjoyholidays', 'travelpackage', 'read')) {
		http_response_code(403);
		return [
			'statusCode'	=> 403,
			'error'			=> 'Access Forbidden'
		];
	}

	$response = [
		'countryId'	=> null,
		'country'	=> null,
		'amount'	=> null
	];

	$globalConfValue = $conf->global->ENJOYHOLIDAYS_DEFAULT_TRAVEL_PRICE;
	if (!$countryId || !strlen($countryId)) {
		$response['amount'] = $globalConfValue;
		return $response;
	}

	$response['countryId'] = $countryId;
	$sql = "SELECT p.amount, c.label country";
	$sql .= " FROM llx_c_default_travel_price p";
	$sql .= " JOIN llx_c_country c ON p.fk_country = c.rowid";
	$sql .= " WHERE p.active = 1 AND c.rowid = '".$countryId."'";

	$resql = $db->query($sql);

	if ($resql && $db->num_rows($resql)) {
		$obj = $db->fetch_object($resql);
		$response['amount'] = $obj->amount;

		if (isset($obj->country)) {
			$response['country'] = $obj->country;
		}
	} else {
		$response['amount'] = $globalConfValue;
	}

	return $response;
}

function getTravelPackagesList() {
	global $db, $user;

	if (!$user->hasRight('enjoyholidays', 'travelpackage', 'read')) {
		http_response_code(403);
		return [
			'statusCode'	=> 403,
			'error'			=> 'Access Forbidden'
		];
	}

	$response = [];

	$sql = "SELECT DISTINCT tp.rowid rowid, tp.ref ref, tp.label label, cc.label desinationCountry, tp.amount amount, tp.travelDepartureDate travelDepartureDate, tp.travelBackDate travelBackDate, ctm.label transportMean, tp.date_creation dateCreation, tp.tms dateModification, tp.status status";
	$sql .= " FROM llx_enjoyholidays_travelpackage tp";
	$sql .= " 	LEFT JOIN llx_c_country cc ON (tp.destinationCountry = cc.rowid)";
	$sql .= " 	LEFT JOIN llx_c_transportmean ctm ON (tp.transportMean = ctm.rowid)";
	$sql .= " ORDER BY tp.rowid";

	$resql = $db->query($sql);

	if ($resql) {
		for ($i=0 ; $i<$db->num_rows($resql) ; $i++) {
			$obj = $db->fetch_object($resql);

			$response[$obj->rowid] = $obj;
		}
	}

	return $response;
}
