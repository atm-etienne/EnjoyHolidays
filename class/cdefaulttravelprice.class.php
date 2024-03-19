<?php
/**
 * \file        class/cdefaulttravelprice.class.php
 * \ingroup     enjoyholidays
 * \brief       This file is a CRUD class file (Create/Read/Update/Delete) for c_default_travel_price dictionary
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commondict.class.php';


/**
 * 	Class to manage dictionary Default Travel Price means (used by imports)
 */
class CdefaultTravelPrice extends CommonDict
{
	public $fk_country;
	public $amount;

	public $element = 'cdefaulttravelprice'; //!< Id that identify managed objects
	public $table_element = 'c_default_travel_price'; //!< Name of table without prefix where object is stored


	public $fields = array(
		'label' => array('type'=>'varchar(250)', 'label'=>'Label', 'enabled'=>1, 'visible'=>1, 'position'=>15, 'notnull'=>-1, 'showoncombobox'=>'1')
	);


	/**
	 *  Constructor
	 *
	 *  @param      DoliDb		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 *  Create object into database
	 *
	 *  @param      User	$user        User that create
	 *  @param      int		$notrigger   0=launch triggers after, 1=disable triggers
	 *  @return     int      		   	 Return integer <0 if KO, Id of created object if OK
	 */
	public function create($user, $notrigger = 0)
	{
		$error = 0;

		// Clean parameters
		if (isset($this->fk_country)) {
			$this->fk_country = trim($this->fk_country);
		}
		if (isset($this->amount)) {
			$this->label = trim($this->amount);
		}
		if (isset($this->active)) {
			$this->active = trim($this->active);
		}

		// Check parameters
		// Put here code to add control on parameters values

		// Insert request
		$sql = "INSERT INTO ".$this->db->prefix()."c_default_travel_price(";
		$sql .= "rowid,";
		$sql .= "fk_country,";
		$sql .= "amount,";
		$sql .= "active";
		$sql .= ") VALUES (";
		$sql .= " ".(!isset($this->rowid) ? 'NULL' : "'".$this->db->escape($this->rowid)."'").",";
		$sql .= " ".(!isset($this->fk_country) ? 'NULL' : "'".$this->db->escape($this->fk_country)."'").",";
		$sql .= " ".(!isset($this->amount) ? 'NULL' : "'".$this->db->escape($this->amount)."'").",";
		$sql .= " ".(!isset($this->active) ? 'NULL' : "'".$this->db->escape($this->active)."'");
		$sql .= ")";

		$this->db->begin();

		dol_syslog(get_class($this)."::create", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		if (!$error) {
			$this->id = $this->db->last_insert_id($this->db->prefix()."c_default_travel_package");
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return $this->id;
		}
	}


	/**
	 *  Load object in memory from database
	 *
	 *  @param      int		$id    	  Id object
	 *  @param		string	$fk_country	    Country
	 *  @return     int          	>0 if OK, 0 if not found, <0 if KO
	 */
	public function fetch($id, $fk_country = '')
	{
		$sql = "SELECT";
		$sql .= " t.rowid,";
		$sql .= " t.fk_country,";
		$sql .= " t.amount,";
		$sql .= " t.active";
		$sql .= " FROM ".$this->db->prefix()."c_default_travel_package as t";
		if ($id) {
			$sql .= " WHERE t.rowid = ".((int) $id);
		} elseif ($fk_country) {
			$sql .= " WHERE t.fk_country = '".$this->db->escape(strtoupper($fk_country))."'";
		}

		dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);

				if ($obj) {
					$this->id = $obj->rowid;
					$this->fk_country = $obj->fk_country;
					$this->amount = $obj->amount;
					$this->active = $obj->active;
				}

				$this->db->free($resql);
				return 1;
			} else {
				return 0;
			}
		} else {
			$this->error = "Error ".$this->db->lasterror();
			return -1;
		}
	}


	/**
	 *  Update object into database
	 *
	 *  @param      User	$user        User that modify
	 *  @param      int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return     int     		   	 Return integer <0 if KO, >0 if OK
	 */
	public function update($user = null, $notrigger = 0)
	{
		$error = 0;

		// Clean parameters
		if (isset($this->amount)) {
			$this->label = trim($this->amount);
		}
		if (isset($this->active)) {
			$this->active = trim($this->active);
		}


		// Check parameters
		// Put here code to add control on parameters values

		// Update request
		$sql = "UPDATE ".$this->db->prefix()."c_default_travel_package SET";
		$sql .= " amount=".(isset($this->amount) ? "'".$this->db->escape($this->amount)."'" : "null").",";
		$sql .= " active=".(isset($this->active) ? $this->active : "null");
		$sql .= " WHERE rowid=".((int) $this->id);

		$this->db->begin();

		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}


	/**
	 *  Delete object in database
	 *
	 *	@param  User	$user        User that delete
	 *  @param	int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 Return integer <0 if KO, >0 if OK
	 */
	public function delete($user, $notrigger = 0)
	{
		$error = 0;

		$sql = "DELETE FROM ".$this->db->prefix()."c_default_travel_package";
		$sql .= " WHERE rowid=".((int) $this->id);

		$this->db->begin();

		dol_syslog(get_class($this)."::delete", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (!$resql) {
			$error++;
			$this->errors[] = "Error ".$this->db->lasterror();
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
				$this->error .= ($this->error ? ', '.$errmsg : $errmsg);
			}
			$this->db->rollback();
			return -1 * $error;
		} else {
			$this->db->commit();
			return 1;
		}
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *	@param	int		$withpicto					Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *	@param	string	$option						On what the link point to ('nolink', ...)
	 *  @param	int  	$notooltip					1=Disable tooltip
	 *  @param  string  $morecss            		Add more css on link
	 *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *	@return	string								String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $langs;
		return $langs->trans($this->label);
	}
}
