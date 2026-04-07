<?php
/* Copyright (C) 2026 Dolicraft <contact@dolicraft.com>
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
 * \defgroup dolicrafts3 Module DolicraftS3
 * \brief S3-compatible cloud storage for Dolibarr (AWS, OVH, Scaleway, Wasabi, MinIO, etc.)
 * \file core/modules/modDolicraftS3.class.php
 * \ingroup dolicrafts3
 * \brief Module descriptor for DolicraftS3
 */

include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 * Class modDolicraftS3
 * Module descriptor for S3-compatible cloud storage
 */
class modDolicraftS3 extends DolibarrModules
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;

		$this->db = $db;

		// Module ID (use a unique number > 100000 to avoid conflicts, range 95000-99999 reserved for DoliStore)
		$this->numero = 500100;

		// Key text used to identify module (for permissions, menus, etc.)
		$this->rights_class = 'dolicrafts3';

		// Family
		$this->family = "technic";

		// Module position
		$this->module_position = '90';

		// Module label
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// Module description (translated via lang files Module500100Desc)
		$this->description = "Module500100Desc";

		// Editor
		$this->editor_name = 'Dolicraft';
		$this->editor_url = 'https://dolicraft.com';
		$this->editor_email = 'contact@dolicraft.com';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0.0';

		// Url to the file with your last numberversion of this module
		// $this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled
		$this->const_name = 'MAIN_MODULE_DOLICRAFTS3';

		// Module icon
		$this->picto = 'folder-open';

		// Define module parts
		$this->module_parts = array(
			'triggers' => 0,
			'login' => 0,
			'substitutions' => 0,
			'menus' => 0,
			'tpl' => 0,
			'barcode' => 0,
			'models' => 0,
			'printing' => 0,
			'theme' => 0,
			'css' => array('/dolicrafts3/css/dolicrafts3.css'),
			'js' => array(),
			'hooks' => array(
				'data' => array(
					'fileslib',
					'ecaborddocument',
				),
				'entity' => '0',
			),
			'moduleforexternal' => 0,
		);

		// Data directories to create when module is enabled
		$this->dirs = array(
			'/dolicrafts3/temp',
		);

		// Config page
		$this->config_page_url = array("setup.php@dolicrafts3");

		// Dependencies
		$this->hidden = false;
		$this->depends = array();
		$this->requiredby = array();
		$this->conflictwith = array();
		$this->langfiles = array("dolicrafts3@dolicrafts3");

		// PHP minimum version
		$this->phpmin = array(7, 4);
		$this->need_dolibarr_version = array(16, 0);

		// Constants
		$this->const = array(
			array('DOLICRAFTS3_PROVIDER', 'chaine', 'custom', 'S3 provider type', 0, 'current', 1),
			array('DOLICRAFTS3_ENDPOINT', 'chaine', '', 'S3 endpoint URL', 0, 'current', 1),
			array('DOLICRAFTS3_REGION', 'chaine', 'us-east-1', 'S3 region', 0, 'current', 1),
			array('DOLICRAFTS3_ACCESS_KEY', 'chaine', '', 'S3 access key', 0, 'current', 1),
			array('DOLICRAFTS3_SECRET_KEY', 'chaine', '', 'S3 secret key (encrypted)', 0, 'current', 1),
			array('DOLICRAFTS3_BUCKET', 'chaine', '', 'S3 bucket name', 0, 'current', 1),
			array('DOLICRAFTS3_PATH_PREFIX', 'chaine', '', 'Prefix path inside bucket', 0, 'current', 1),
			array('DOLICRAFTS3_USE_PATH_STYLE', 'chaine', '0', 'Use path-style endpoint (required for MinIO, some providers)', 0, 'current', 1),
			array('DOLICRAFTS3_SYNC_MODE', 'chaine', 'manual', 'Sync mode: manual, auto_upload, full_sync', 0, 'current', 1),
		);

		// Permissions
		$this->rights = array();
		$r = 0;

		$this->rights[$r][0] = $this->numero + 1;
		$this->rights[$r][1] = 'Read S3 files';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'read';
		$r++;

		$this->rights[$r][0] = $this->numero + 2;
		$this->rights[$r][1] = 'Upload files to S3';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'upload';
		$r++;

		$this->rights[$r][0] = $this->numero + 3;
		$this->rights[$r][1] = 'Delete files from S3';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'delete';
		$r++;

		$this->rights[$r][0] = $this->numero + 4;
		$this->rights[$r][1] = 'Configure S3 settings';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'configure';
		$r++;

		// Menus
		$this->menu = array();
		$r = 0;

		// Top menu
		$this->menu[$r] = array(
			'fk_menu'  => '',
			'type'     => 'top',
			'titre'    => 'DolicraftS3',
			'prefix'   => '<span class="fas fa-cloud pictofixedwidth" style="font-size: 1.4em;"></span>',
			'mainmenu' => 'dolicrafts3',
			'leftmenu' => '',
			'url'      => '/dolicrafts3/index.php',
			'langs'    => 'dolicrafts3@dolicrafts3',
			'position' => 1000 + $r,
			'enabled'  => 'isModEnabled("dolicrafts3")',
			'perms'    => '$user->hasRight("dolicrafts3", "read")',
			'target'   => '',
			'user'     => 0,
		);
		$r++;

		// Left menu - Browser
		$this->menu[$r] = array(
			'fk_menu'  => 'fk_mainmenu=dolicrafts3',
			'type'     => 'left',
			'titre'    => 'S3Browser',
			'mainmenu' => 'dolicrafts3',
			'leftmenu' => 'dolicrafts3_browser',
			'url'      => '/dolicrafts3/index.php',
			'langs'    => 'dolicrafts3@dolicrafts3',
			'position' => 1000 + $r,
			'enabled'  => 'isModEnabled("dolicrafts3")',
			'perms'    => '$user->hasRight("dolicrafts3", "read")',
			'target'   => '',
			'user'     => 0,
		);
		$r++;

		// Left menu - Upload
		$this->menu[$r] = array(
			'fk_menu'  => 'fk_mainmenu=dolicrafts3',
			'type'     => 'left',
			'titre'    => 'S3Upload',
			'mainmenu' => 'dolicrafts3',
			'leftmenu' => 'dolicrafts3_upload',
			'url'      => '/dolicrafts3/upload.php',
			'langs'    => 'dolicrafts3@dolicrafts3',
			'position' => 1000 + $r,
			'enabled'  => 'isModEnabled("dolicrafts3")',
			'perms'    => '$user->hasRight("dolicrafts3", "upload")',
			'target'   => '',
			'user'     => 0,
		);
		$r++;

		// Left menu - Settings
		$this->menu[$r] = array(
			'fk_menu'  => 'fk_mainmenu=dolicrafts3',
			'type'     => 'left',
			'titre'    => 'S3Setup',
			'mainmenu' => 'dolicrafts3',
			'leftmenu' => 'dolicrafts3_setup',
			'url'      => '/dolicrafts3/admin/setup.php',
			'langs'    => 'dolicrafts3@dolicrafts3',
			'position' => 1000 + $r,
			'enabled'  => 'isModEnabled("dolicrafts3")',
			'perms'    => '$user->hasRight("dolicrafts3", "configure")',
			'target'   => '',
			'user'     => 0,
		);
		$r++;
	}

	/**
	 * Function called when module is enabled.
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		$result = $this->_load_tables('/dolicrafts3/sql/');

		$sql = array();

		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 *
	 * @param string $options Options when disabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}
}
