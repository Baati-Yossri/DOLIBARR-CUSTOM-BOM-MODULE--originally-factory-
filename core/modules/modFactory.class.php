<?php
/* Copyright (C) 2013-2019	Charlene BENKE	<charlie@patas-monkey.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\defgroup   factory	 Module gestion de la fabrication
 *	\brief	  Module pour gerer les process de fabrication
 *	\file	   htdocs/factory/core/modules/modFactory.class.php
 *	\ingroup	factory
 *	\brief	  Fichier de description et activation du module factory
 */
include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';


/**
 *	Classe de description et activation du module Propale
 */
class modfactory extends DolibarrModules
{

	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param	  DoliDB		$db	  Database handler
	 */
	function __construct($db)
	{
		global $conf;

		$this->db = $db;
		$this->numero = 160310;

		$this->family = "products";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' )
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		$this->description = "Gestion de la fabrication";

		$this->editor_name = "<b>Patas-Monkey</b>";
		$this->editor_web = "http://www.patas-monkey.com";

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = $this->getLocalVersion();

		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		$this->special = 0;
		$this->picto = $this->name . '@' . $this->name;

		// Data directories to create when module is enabled
		$this->dirs = array("/" . $this->name . "/temp");

		// Constantes
		$this->const = array();

		// Dependancies
		$this->depends = array();
		$this->requiredby = array();
		$this->config_page_url = array($this->name . ".php@" . $this->name);
		$this->langfiles = array("propal", "order", "project", "companies", "products", "factory@factory");

		$this->need_dolibarr_version = array(3, 4);

		// hook pour la recherche
		$this->module_parts = array();


		// Constants
		$this->const = array();
		$r = 0;

		// contact element setting
		$this->contactelement = 1;

		// Permissions
		$this->rights = array();
		$this->rights_class = $this->name;
		$r = 0;

		$r++;
		$this->rights[$r][0] = 160310; // id de la permission
		$this->rights[$r][1] = 'Lire les fabrications'; // libelle de la permission
		$this->rights[$r][2] = 'r'; // type de la permission (deprecie a ce jour)
		$this->rights[$r][3] = 1; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'lire';
		$r++;
		$this->rights[$r][0] = 160311; // id de la permission
		$this->rights[$r][1] = 'cr&eacute;er une fabrication'; // libelle de la permission
		$this->rights[$r][2] = 'c'; // type de la permission (deprecie a ce jour)
		$this->rights[$r][3] = 1; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'creer';
		$r++;
		$this->rights[$r][0] = 160312; // id de la permission
		$this->rights[$r][1] = 'Annuler la fabrication'; // libelle de la permission
		$this->rights[$r][2] = 'd'; // type de la permission (deprecie a ce jour)
		$this->rights[$r][3] = 1; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'annuler';


		$this->menu[$r] = array(
			'fk_menu' => 'fk_mainmenu=products,fk_leftmenu=factory',
			'type' => 'left',
			'titre' => 'Declinaison',
			'mainmenu' => '',
			'leftmenu' => '',
			'url' => '/factory/declinaison.php',
			'langs' => 'factory@factory',
			'position' => 110,
			'enabled' => '1',
			'perms' => '1',
			'target' => '',
			'user' => 2
		);
		$r++;


		// additional tabs
		$this->tabs = array(
			'product:+factory:Factory:@Produit:/custom/factory/product/index.php?id=__ID__'


		);

	}


	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
	 *	  @param	  string	$options	Options when enabling module ('', 'noboxes')
	 *	  @return	 int			 	1 if OK, 0 if KO
	 */
	function init($options = '')
	{
		global $conf;

		// Permissions
		$this->remove($options);

		$sql = array();

		$result = $this->load_tables();

		return $this->_init($sql, $options);
	}

	/**
	 *		Function called when module is disabled.
	 *	  Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted
	 *
	 *	  @param	  string	$options	Options when enabling module ('', 'noboxes')
	 *	  @return	 int			 	1 if OK, 0 if KO
	 */
	function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}

	/**
	 *		Create tables, keys and data required by module
	 * 		Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * 		and create data commands must be stored in directory /mymodule/sql/
	 *		This function is called by this->init.
	 *
	 * 		@return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables('/factory/sql/');
	}

	function getChangeLog()
	{
		// Libraries
		dol_include_once("/" . $this->name . "/core/lib/patasmonkey.lib.php");
		return getChangeLog($this->name);
	}

	function getVersion($translated = 1)
	{
		global $langs, $conf;
		$currentversion = $this->version;

		if ($conf->global->PATASMONKEY_SKIP_CHECKVERSION == 1)
			return $currentversion;

		if ($this->disabled) {
			$newversion = $langs->trans("DolibarrMinVersionRequiered") . " : " . $this->dolibarrminversion;
			$currentversion = "<font color=red><b>" . img_error($newversion) . $currentversion . "</b></font>";
			return $currentversion;
		}

		$context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$changelog = @file_get_contents(
			str_replace("www", "dlbdemo", $this->editor_web) . '/htdocs/custom/' . $this->name . '/changelog.xml',
			false,
			$context
		);
		//$htmlversion = @file_get_contents($this->editor_web.$this->editor_version_folder.$this->name.'/');

		if ($htmlversion === false)
			return $currentversion;	// not connected
		else {
			$sxelast = simplexml_load_string(nl2br($changelog));
			if ($sxelast === false)
				return $currentversion;
			else
				$tblversionslast = $sxelast->Version;

			$lastversion = $tblversionslast[count($tblversionslast) - 1]->attributes()->Number;

			if ($lastversion != (string) $this->version) {
				if ($lastversion > (string) $this->version) {
					$newversion = $langs->trans("NewVersionAviable") . " : " . $lastversion;
					$currentversion = "<font title='" . $newversion . "' color=orange><b>" . $currentversion . "</b></font>";
				} else
					$currentversion = "<font title='Version Pilote' color=red><b>" . $currentversion . "</b></font>";
			}
		}
		return $currentversion;
	}

	function getLocalVersion()
	{
		global $langs;
		$context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$changelog = @file_get_contents(dol_buildpath($this->name, 0) . '/changelog.xml', false, $context);
		$sxelast = simplexml_load_string(nl2br($changelog));
		if ($sxelast === false)
			return $langs->trans("ChangelogXMLError");
		else {
			$tblversionslast = $sxelast->Version;
			$currentversion = $tblversionslast[count($tblversionslast) - 1]->attributes()->Number;
			$tblDolibarr = $sxelast->Dolibarr;
			$minversionDolibarr = $tblDolibarr->attributes()->minVersion;
			if ((int) DOL_VERSION < (int) $minversionDolibarr) {
				$this->dolibarrminversion = $minversionDolibarr;
				$this->disabled = true;
			}
		}
		return $currentversion;
	}
}