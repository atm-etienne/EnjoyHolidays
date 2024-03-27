<?php

/**
/*	\file       htdocs/custom/enjoyholidays/class/con_travelpackage.class.php
/*	\ingroup    EnjoyHolidays
/*	\brief      File of class to set cron for travel packages
/*/

require_once DOL_DOCUMENT_ROOT.'/custom/enjoyholidays/class/travelpackage.class.php';

class TravelPackageCron extends CommonObject {

	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'travelpackage';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'enjoyholidays_travelpackage';

	public $output;

	public function EncloseNonValidatedTravelPackages() {
		global $langs, $db;

		$responseCode = 0;

		$sql = "DELETE FROM llx_enjoyholidays_travelpackage";
		$sql .= " WHERE status = 0";
		$sql .= " 	AND date_creation < DATE_SUB(CURRENT_DATE(), INTERVAL 3 WEEK)";

		$resql = $db->query($sql);
		if ($resql) {
			$this->output = $langs->trans("CronTravelPackagesDeleted", $db->affected_rows($resql));
		} else {
			$error = $db->lasterror();
			$this->output = $error;
			$this->errors[] = $error;
			$responseCode = -1;
		}

		return $responseCode;
	}

}
