<?php
/* Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modEnjoyHolidays_TravelPackageTriggers.class.php
 * \ingroup enjoyholidays
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modEnjoyHolidays_TravelPackageTriggers.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';


/**
 *  Class of triggers for MyModule module
 */
class InterfaceTravelPackageTriggers extends DolibarrTriggers
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "EnjoyHolidays triggers.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'enjoyholidays.png@enjoyholidays';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}


	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		Return integer <0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (!isModEnabled('enjoyholidays')) {
			return 0; // If module is not enabled, we do nothing
		}

		// Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action

		// You can isolate code for each action in a separate method: this method should be named like the trigger in camelCase.
		// For example : COMPANY_CREATE => public function companyCreate($action, $object, User $user, Translate $langs, Conf $conf)
		$methodName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($action)))));
		$callback = array($this, $methodName);
		if (is_callable($callback)) {
			dol_syslog(
				"Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id
			);

			return call_user_func($callback, $action, $object, $user, $langs, $conf);
		}

		return 0;
	}

	public function propalDelete($action, $object, User $user, Translate $langs, Conf $conf) {

		return $this->deleteFromElementElement($object);

	}

	public function travelpackageDelete($action, $object, User $user, Translate $langs, Conf $conf) {

		return $this->deleteFromElementElement($object, false);

	}

	/**
	 * @param $object
	 * @param $fromPropal    bool   true: delete elements from propalDelete (select fk_target)	false: delete elements from travelpackageDelete (select fk_source)
	 * @return int
	 */
	private function deleteFromElementElement($object, bool $fromPropal = true) {
		$sql = "DELETE ee, ".($fromPropal ? 'tp' : 'p');
		$sql .= " FROM ".$this->db->prefix()."element_element ee";
		$sql .= " 	JOIN ".$this->db->prefix().($fromPropal ? 'enjoyholidays_travelpackage tp' : 'propal p');
		$sql .= "		ON " .($fromPropal ? 'ee.fk_target = tp.rowid'
											: 'ee.fk_source = p.rowid');
		$sql .= " WHERE sourcetype = 'propal' AND ".($fromPropal ? 'fk_source' : 'fk_target'). " = ".$this->db->escape($object->id);
		$sql .= " 	AND targettype = 'enjoyholidays_travelpackage'";

		$resql = $this->db->query($sql);
		if (!$resql) {
			return -1;
		}
		return 0;
	}
}
