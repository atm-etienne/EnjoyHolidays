<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';
class ActionsEnjoyHolidays extends CommonHookActions {

	public $db;
	public $error = '';
	public $errors = [];

	public function __construct($db) {
		$this->db = $db;
	}

	public function addMoreActionsButtons($parameters, &$object, &$action, $hookManager) {
		global $langs;

		print '<a class="butAction" href="'.DOL_URL_ROOT.'/custom/enjoyholidays/travelpackage_card.php?action=create&propalId='.$object->id.'">'. $langs->trans('NewTravelPackage') .'</a>';

		return 0;
	}

}
