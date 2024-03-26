<?php

use Luracast\Restler\RestException;

/**
 * API class for enjoyholidays
 *
 * @access protected
 * @class  DolibarrApiAccess {@requires user,external}
 */
class EnjoyHolidaysApi extends DolibarrApi
{
	/**
	 * @var array   $FIELDS     Mandatory fields, checked when create and update object
	 */
	public static $FIELDS = array(
		'ref',
		'destinationCountry'
	);

	/**
	 * @var TravelPackage $travelPackage {@type TravelPackage}
	 */
	public $travelPackage;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $db, $conf;
		$this->db = $db;
		$this->travelPackage = new TravelPackage($this->db);
	}

	/**
	 * Get properties of an order object by id
	 *
	 * Return an array with order informations
	 *
	 * @param       int         $id            ID of order
	 * @param       int         $contact_list  0: Returned array of contacts/addresses contains all properties, 1: Return array contains just id
	 * @return	array|mixed data without useless information
	 *
	 * @throws	RestException
	 */
	public function get($id, $contact_list = 1)
	{
		return $this->_fetch($id, '', '', $contact_list);
	}

	/**
	 * Get properties of an order object by ref
	 *
	 * Return an array with order informations
	 *
	 * @param       string		$ref			Ref of object
	 * @param       int         $contact_list  0: Returned array of contacts/addresses contains all properties, 1: Return array contains just id
	 * @return	array|mixed data without useless information
	 *
	 * @url GET    ref/{ref}
	 *
	 * @throws	RestException
	 */
	public function getByRef($ref, $contact_list = 1)
	{
		return $this->_fetch('', $ref, '', $contact_list);
	}

	/**
	 * Get properties of an order object by ref_ext
	 *
	 * Return an array with order informations
	 *
	 * @param       string		$ref_ext			External reference of object
	 * @param       int         $contact_list  0: Returned array of contacts/addresses contains all properties, 1: Return array contains just id
	 * @return	array|mixed data without useless information
	 *
	 * @url GET    ref_ext/{ref_ext}
	 *
	 * @throws	RestException
	 */
	public function getByRefExt($ref_ext, $contact_list = 1)
	{
		return $this->_fetch('', '', $ref_ext, $contact_list);
	}

	/**
	 * Get properties of an order object
	 *
	 * Return an array with order informations
	 *
	 * @param       int         $id				ID of order
	 * @param		string		$ref			Ref of object
	 * @param		string		$ref_ext		External reference of object
	 * @param       int         $contact_list	0: Returned array of contacts/addresses contains all properties, 1: Return array contains just id
	 * @return		Object						Object with cleaned properties
	 *
	 * @throws	RestException
	 */
	private function _fetch($id, $ref = '', $ref_ext = '', $contact_list = 1)
	{
		if (!DolibarrApiAccess::$user->hasRight('commande', 'lire')) {
			throw new RestException(401);
		}

		$result = $this->commande->fetch($id, $ref, $ref_ext);
		if (!$result) {
			throw new RestException(404, 'Order not found');
		}

		if (!DolibarrApi::_checkAccessToResource('commande', $this->commande->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		// Add external contacts ids
		$tmparray = $this->commande->liste_contact(-1, 'external', $contact_list);
		if (is_array($tmparray)) {
			$this->commande->contacts_ids = $tmparray;
		}
		$this->commande->fetchObjectLinked();

		// Add online_payment_url, cf #20477
		require_once DOL_DOCUMENT_ROOT.'/core/lib/payments.lib.php';
		$this->commande->online_payment_url = getOnlinePaymentUrl(0, 'order', $this->commande->ref);

		return $this->_cleanObjectDatas($this->commande);
	}

	/**
	 * List travel packages
	 *
	 * Get a list of travel package
	 *
	 * @param string		   $sortfield			Sort field
	 * @param string		   $sortorder			Sort order
	 * @param int			   $limit				Limit for list
	 * @param int			   $page				Page number
	 * @param string		   $thirdparty_ids		Thirdparty ids to filter orders of (example '1' or '1,2,3') {@pattern /^[0-9,]*$/i}
	 * @param string           $sqlfilters          Other criteria to filter answers separated by a comma. Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
	 * @param string           $sqlfilterlines      Other criteria to filter answers separated by a comma. Syntax example "(tl.fk_product:=:'17') and (tl.price:<:'250')"
	 * @param string		   $properties			Restrict the data returned to theses properties. Ignored if empty. Comma separated list of properties names
	 * @return  array                               Array of order objects
	 *
	 * @throws RestException 404 Not found
	 * @throws RestException 503 Error
	 */
	public function index($sortfield = "t.rowid", $sortorder = 'ASC', $limit = 100, $page = 0, $thirdparty_ids = '', $sqlfilters = '', $sqlfilterlines = '', $properties = '')
	{
		if (!DolibarrApiAccess::$user->hasRight('enjoyholidays', 'travelpackage', 'read')) {
			throw new RestException(401);
		}

		$obj_ret = array();

		// case of external user, $thirdparty_ids param is ignored and replaced by user's socid
		$socids = DolibarrApiAccess::$user->socid ? DolibarrApiAccess::$user->socid : $thirdparty_ids;

		// If the internal user must only see his customers, force searching by him
		$search_sale = 0;
		if (!DolibarrApiAccess::$user->rights->societe->client->voir && !$socids) {
			$search_sale = DolibarrApiAccess::$user->id;
		}

		$sql = "SELECT t.rowid";
		if ((!DolibarrApiAccess::$user->rights->societe->client->voir && !$socids) || $search_sale > 0) {
			$sql .= ", sc.fk_soc, sc.fk_user"; // We need these fields in order to filter by sale (including the case where the user can only see his prospects)
		}
		$sql .= " FROM ".MAIN_DB_PREFIX."commande AS t LEFT JOIN ".MAIN_DB_PREFIX."commande_extrafields AS ef ON (ef.fk_object = t.rowid)"; // Modification VMR Global Solutions to include extrafields as search parameters in the API GET call, so we will be able to filter on extrafields

		if ((!DolibarrApiAccess::$user->rights->societe->client->voir && !$socids) || $search_sale > 0) {
			$sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc"; // We need this table joined to the select in order to filter by sale
		}

		$sql .= ' WHERE t.entity IN ('.getEntity('commande').')';
		if ((!DolibarrApiAccess::$user->rights->societe->client->voir && !$socids) || $search_sale > 0) {
			$sql .= " AND t.fk_soc = sc.fk_soc";
		}
		if ($socids) {
			$sql .= " AND t.fk_soc IN (".$this->db->sanitize($socids).")";
		}
		if ($search_sale > 0) {
			$sql .= " AND t.rowid = sc.fk_soc"; // Join for the needed table to filter by sale
		}
		// Insert sale filter
		if ($search_sale > 0) {
			$sql .= " AND sc.fk_user = ".((int) $search_sale);
		}
		// Add sql filters
		if ($sqlfilters) {
			$errormessage = '';
			$sql .= forgeSQLFromUniversalSearchCriteria($sqlfilters, $errormessage);
			if ($errormessage) {
				throw new RestException(400, 'Error when validating parameter sqlfilters -> '.$errormessage);
			}
		}
		// Add sql filters for lines
		if ($sqlfilterlines) {
			$errormessage = '';
			$sql .= " AND EXISTS (SELECT tl.rowid FROM ".MAIN_DB_PREFIX."commandedet AS tl WHERE tl.fk_commande = t.rowid";
			$sql .= forgeSQLFromUniversalSearchCriteria($sqlfilterlines, $errormessage);
			$sql .=	")";
			if ($errormessage) {
				throw new RestException(400, 'Error when validating parameter sqlfilterlines -> '.$errormessage);
			}
		}
		$sql .= $this->db->order($sortfield, $sortorder);
		if ($limit) {
			if ($page < 0) {
				$page = 0;
			}
			$offset = $limit * $page;

			$sql .= $this->db->plimit($limit + 1, $offset);
		}

		dol_syslog("API Rest request");
		$result = $this->db->query($sql);

		if ($result) {
			$num = $this->db->num_rows($result);
			$min = min($num, ($limit <= 0 ? $num : $limit));
			$i = 0;
			while ($i < $min) {
				$obj = $this->db->fetch_object($result);
				$commande_static = new Commande($this->db);
				if ($commande_static->fetch($obj->rowid)) {
					// Add external contacts ids
					$tmparray = $commande_static->liste_contact(-1, 'external', 1);
					if (is_array($tmparray)) {
						$commande_static->contacts_ids = $tmparray;
					}
					// Add online_payment_url, cf #20477
					require_once DOL_DOCUMENT_ROOT.'/core/lib/payments.lib.php';
					$commande_static->online_payment_url = getOnlinePaymentUrl(0, 'order', $commande_static->ref);

					$obj_ret[] = $this->_filterObjectProperties($this->_cleanObjectDatas($commande_static), $properties);
				}
				$i++;
			}
		} else {
			throw new RestException(503, 'Error when retrieve commande list : '.$this->db->lasterror());
		}

		return $obj_ret;
	}

	/**
	 * Create a sale order
	 *
	 * Exemple: { "socid": 2, "date": 1595196000, "type": 0, "lines": [{ "fk_product": 2, "qty": 1 }] }
	 *
	 * @param   array   $request_data   Request data
	 * @return  int     ID of order
	 */
	public function post($request_data = null)
	{
		if (!DolibarrApiAccess::$user->rights->commande->creer) {
			throw new RestException(401, "Insuffisant rights");
		}
		// Check mandatory fields
		$result = $this->_validate($request_data);

		foreach ($request_data as $field => $value) {
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again whith the caller
				$this->commande->context['caller'] = $request_data['caller'];
				continue;
			}

			$this->commande->$field = $value;
		}
		/*if (isset($request_data["lines"])) {
		  $lines = array();
		  foreach ($request_data["lines"] as $line) {
			array_push($lines, (object) $line);
		  }
		  $this->commande->lines = $lines;
		}*/

		if ($this->commande->create(DolibarrApiAccess::$user) < 0) {
			throw new RestException(500, "Error creating order", array_merge(array($this->commande->error), $this->commande->errors));
		}

		return $this->commande->id;
	}

	/**
	 * Get contacts of given order
	 *
	 * Return an array with contact informations
	 *
	 * @param	int		$id			ID of order
	 * @param	string	$type		Type of the contact (BILLING, SHIPPING, CUSTOMER)
	 * @return	Object				Object with cleaned properties
	 *
	 * @url	GET {id}/contacts
	 *
	 * @throws	RestException
	 */
	public function getContacts($id, $type = '')
	{
		if (!DolibarrApiAccess::$user->hasRight('commande', 'lire')) {
			throw new RestException(401);
		}

		$result = $this->commande->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Order not found');
		}

		if (!DolibarrApi::_checkAccessToResource('commande', $this->commande->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$contacts = $this->commande->liste_contact(-1, 'external', 0, $type);

		return $this->_cleanObjectDatas($contacts);
	}

	/**
	 * Add a contact type of given order
	 *
	 * @param int    $id             Id of order to update
	 * @param int    $contactid      Id of contact to add
	 * @param string $type           Type of the contact (BILLING, SHIPPING, CUSTOMER)
	 * @return array
	 *
	 * @url	POST {id}/contact/{contactid}/{type}
	 *
	 * @throws RestException 401
	 * @throws RestException 404
	 */
	public function postContact($id, $contactid, $type)
	{
		if (!DolibarrApiAccess::$user->rights->commande->creer) {
			throw new RestException(401);
		}

		$result = $this->commande->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Order not found');
		}

		if (!DolibarrApi::_checkAccessToResource('commande', $this->commande->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$result = $this->commande->add_contact($contactid, $type, 'external');

		if ($result < 0) {
			throw new RestException(500, 'Error when added the contact');
		}

		if ($result == 0) {
			throw new RestException(304, 'contact already added');
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'Contact linked to the order'
			)
		);
	}

	/**
	 * Unlink a contact type of given order
	 *
	 * @param int    $id             Id of order to update
	 * @param int    $contactid      Id of contact
	 * @param string $type           Type of the contact (BILLING, SHIPPING, CUSTOMER).
	 *
	 * @url	DELETE {id}/contact/{contactid}/{type}
	 *
	 * @return array
	 *
	 * @throws RestException 401
	 * @throws RestException 404
	 * @throws RestException 500 System error
	 */
	public function deleteContact($id, $contactid, $type)
	{
		if (!DolibarrApiAccess::$user->rights->commande->creer) {
			throw new RestException(401);
		}

		$result = $this->commande->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Order not found');
		}

		if (!DolibarrApi::_checkAccessToResource('commande', $this->commande->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		$contacts = $this->commande->liste_contact();

		foreach ($contacts as $contact) {
			if ($contact['id'] == $contactid && $contact['code'] == $type) {
				$result = $this->commande->delete_contact($contact['rowid']);

				if (!$result) {
					throw new RestException(500, 'Error when deleted the contact');
				}
			}
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'Contact unlinked from order'
			)
		);
	}

	/**
	 * Update order general fields (won't touch lines of order)
	 *
	 * @param	int		$id             Id of order to update
	 * @param	array	$request_data   Datas
	 * @return	Object					Object with cleaned properties
	 */
	public function put($id, $request_data = null)
	{
		if (!DolibarrApiAccess::$user->rights->commande->creer) {
			throw new RestException(401);
		}

		$result = $this->commande->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Order not found');
		}

		if (!DolibarrApi::_checkAccessToResource('commande', $this->commande->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}
		foreach ($request_data as $field => $value) {
			if ($field == 'id') {
				continue;
			}
			if ($field === 'caller') {
				// Add a mention of caller so on trigger called after action, we can filter to avoid a loop if we try to sync back again whith the caller
				$this->commande->context['caller'] = $request_data['caller'];
				continue;
			}

			$this->commande->$field = $value;
		}

		// Update availability
		if (!empty($this->commande->availability_id)) {
			if ($this->commande->availability($this->commande->availability_id) < 0) {
				throw new RestException(400, 'Error while updating availability');
			}
		}

		if ($this->commande->update(DolibarrApiAccess::$user) > 0) {
			return $this->get($id);
		} else {
			throw new RestException(500, $this->commande->error);
		}
	}

	/**
	 * Delete order
	 *
	 * @param   int     $id         Order ID
	 * @return  array
	 */
	public function delete($id)
	{
		if (!DolibarrApiAccess::$user->rights->commande->supprimer) {
			throw new RestException(401);
		}
		$result = $this->commande->fetch($id);
		if (!$result) {
			throw new RestException(404, 'Order not found');
		}

		if (!DolibarrApi::_checkAccessToResource('commande', $this->commande->id)) {
			throw new RestException(401, 'Access not allowed for login '.DolibarrApiAccess::$user->login);
		}

		if (!$this->commande->delete(DolibarrApiAccess::$user)) {
			throw new RestException(500, 'Error when deleting order : '.$this->commande->error);
		}

		return array(
			'success' => array(
				'code' => 200,
				'message' => 'Order deleted'
			)
		);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 * Clean sensible object datas
	 *
	 * @param   Object  $object			Object to clean
	 * @return  Object					Object with cleaned properties
	 */
	protected function _cleanObjectDatas($object)
	{
		// phpcs:enable
		$object = parent::_cleanObjectDatas($object);

		unset($object->note);
		unset($object->address);
		unset($object->barcode_type);
		unset($object->barcode_type_code);
		unset($object->barcode_type_label);
		unset($object->barcode_type_coder);

		return $object;
	}

	/**
	 * Validate fields before create or update object
	 *
	 * @param   array           $data   Array with data to verify
	 * @return  array
	 * @throws  RestException
	 */
	private function _validate($data)
	{
		$commande = array();
		foreach (Orders::$FIELDS as $field) {
			if (!isset($data[$field])) {
				throw new RestException(400, $field." field missing");
			}
			$commande[$field] = $data[$field];
		}
		return $commande;
	}
}
