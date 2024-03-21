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

		print '<a class="butAction">'. $langs->trans('NewTravelPackage') .'</a>';

		return 0;
	}

}
