<?php
/**
* CG Meteo Module  - Joomla 4.0.0 Module 
* Version			: 2.1.0
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Version;
use Joomla\Filesystem\File;

class mod_cg_meteoInstallerScript
{
	private $min_joomla_version      = '3.10.0';
	private $min_php_version         = '7.4';
	private $name                    = 'CG Meteo';
	private $exttype                 = 'module';
	private $extname                 = 'cg_meteo';
	private $previous_version        = '';
	private $dir           = null;
	private $installerName = 'cg_meteoinstaller';
	public function __construct()
	{
		$this->dir = __DIR__;
	}

    function preflight($type, $parent)
    {
		if ( ! $this->passMinimumJoomlaVersion())
		{
			$this->uninstallInstaller();
			return false;
		}

		if ( ! $this->passMinimumPHPVersion())
		{
			$this->uninstallInstaller();
			return false;
		}
		// To prevent installer from running twice if installing multiple extensions
		if ( ! file_exists($this->dir . '/' . $this->installerName . '.xml'))
		{
			return true;
		}
    }
    function postflight($type, $parent)
    {
		if (($type=='install') || ($type == 'update')) { // remove obsolete dir/files
			$this->postinstall_cleanup();
		}

		switch ($type) {
            case 'install': $message = Text::_('ISO_POSTFLIGHT_INSTALLED'); break;
            case 'uninstall': $message = Text::_('ISO_POSTFLIGHT_UNINSTALLED'); break;
            case 'update': $message = Text::_('ISO_POSTFLIGHT_UPDATED'); break;
            case 'discover_install': $message = Text::_('ISO_POSTFLIGHT_DISC_INSTALLED'); break;
        }
		return true;
    }
	function postinstall_cleanup() {
		$obsloteFolders = ['icons', 'assets', 'models'];
		foreach ($obsloteFolders as $folder)
		{
			$f = JPATH_SITE . '/modules/mod_'.$this->extname.'/' . $folder;
			if (!@file_exists($f) || !is_dir($f) || is_link($f))
			{
				continue;
			}

			Folder::delete($f);
		}
		$obsloteFiles = [sprintf("%s/modules/mod_%s/helper.php", JPATH_SITE, $this->extname),
						sprintf("%s/modules/mod_%s/meteo.js", JPATH_SITE, $this->extname),
						sprintf("%s/modules/mod_%s/css_admin.css", JPATH_SITE, $this->extname)];

		foreach ($obsloteFiles as $file) {
			if (@is_file($file))
			{
				File::delete($file);
			}
		}
		$j = new Version();
		$version=$j->getShortVersion(); 
		$version_arr = explode('.',$version);
		// Delete older language files
		$langFiles = [
			sprintf("%s/language/en-GB/en-GB.mod_%s.ini", JPATH_SITE, 'simple_meteo'),
			sprintf("%s/language/en-GB/en-GB.mod_%s.sys.ini", JPATH_SITE, 'simple_meteo'),
			sprintf("%s/language/fr-FR/fr-FR.mod_%s.ini", JPATH_SITE, 'simple_meteo'),
			sprintf("%s/language/fr-FR/fr-FR.mod_%s.sys.ini", JPATH_SITE, 'simple_meteo')
		];
		foreach ($langFiles as $file) {
			if (@is_file($file)) {
				File::delete($file);
			}
		}
		// check previous version 
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('extension_id,params')
			->from('#__extensions')
			->where($db->quoteName('element') . ' = "mod_simple_meteo"')
			->where($db->quoteName('type') . ' = ' . $db->quote('module'));
		$db->setQuery($query);
		$old_ext = $db->loadObject();
		if (($old_ext)) {
			// rename modules
	        $query = $db->getQuery(true)
				->update('#__modules')
				->set('module = "mod_cg_meteo"')
				->where($db->quoteName('module') . ' = "mod_simple_meteo"');
	        $db->setQuery($query);
	        $db->execute();
			// delete old extension
			$query = $db->getQuery(true)
			->delete('#__extensions')
			->where($db->quoteName('extension_id') . ' = ' . $old_ext->extension_id);
			$db->setQuery($query);
			$db->execute();
		}	
		// CG Popup is now on Github and is a module, not a package anymore
		$query = $db->getQuery(true)
			->delete('#__update_sites')
			->where($db->quoteName('location') . ' like "%conseilgouz.com/updates/cg_meteo%" OR '.$db->quoteName('location') . ' like "%simple_meteo%"');
		$db->setQuery($query);
		$db->execute();
		
	}

	// Check if Joomla version passes minimum requirement
	function passMinimumJoomlaVersion()
	{
		$j = new Version();
		$version=$j->getShortVersion(); 
		if (version_compare($version, $this->min_joomla_version, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Incompatible Joomla version : found <strong>' . $version . '</strong>, Minimum : <strong>' . $this->min_joomla_version . '</strong>',
				'error'
			);

			return false;
		}

		return true;
	}

	// Check if PHP version passes minimum requirement
	function passMinimumPHPVersion()
	{

		if (version_compare(PHP_VERSION, $this->min_php_version, '<'))
		{
			Factory::getApplication()->enqueueMessage(
					'Incompatible PHP version : found  <strong>' . PHP_VERSION . '</strong>, Minimum <strong>' . $this->min_php_version . '</strong>',
				'error'
			);
			return false;
		}

		return true;
	}
	function uninstallInstaller()
	{
		if ( ! is_dir(JPATH_PLUGINS . '/system/' . $this->installerName)) {
			return;
		}
		$this->delete([
			JPATH_PLUGINS . '/system/' . $this->installerName . '/language',
			JPATH_PLUGINS . '/system/' . $this->installerName,
		]);
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete('#__extensions')
			->where($db->quoteName('element') . ' = ' . $db->quote($this->installerName))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
		$db->setQuery($query);
		$db->execute();
		Factory::getCache()->clean('_system');
	}
	
}