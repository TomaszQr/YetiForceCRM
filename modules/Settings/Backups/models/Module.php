<?php

/**
 * Settings Backups module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Backups_Module_Model extends Settings_Vtiger_Module_Model
{
	public $name = 'Backups';

	/**
	 * Return catalog with backup files.
	 *
	 * @return string
	 */
	public static function getCatalogPath()
	{
		return \AppConfig::module('Backups', 'BACKUPS_PATH');
	}

	/**
	 * Read catalog with backup files and return catalogs and files list.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 *
	 * @return array
	 */
	public static function readCatalog(\App\Request $request)
	{
		$catalogToRead = $request->getByType('catalog', 'String');
		$catalogPath = self::getCatalogPath();
		$catalogToReadArray = $returnStructure = [];
		$urlDirectory = '';
		if (!empty($catalogToRead)) {
			$catalogToReadArray = explode(DIRECTORY_SEPARATOR, $catalogToRead);
			$catalogPath = self::getCatalogPath() . DIRECTORY_SEPARATOR . $catalogToRead;
			$urlDirectory = $catalogToRead . DIRECTORY_SEPARATOR;
		}
		if (!self::isAllowedDirectory($catalogToRead)) {
			throw new \App\Exceptions\NoPermittedForAdmin(\App\Language::translate('LBL_PERMISSION_DENIED'));
		}
		$catalogs = array_diff(scandir($catalogPath, SCANDIR_SORT_ASCENDING), ['.']);
		foreach ($catalogs as $element) {
			$requestUrl = 'index.php?module=' . $request->getModule() . '&parent=Settings&view=Index';
			if ('..' === $element) {
				if (!empty($catalogToReadArray)) {
					array_pop($catalogToReadArray);
					$parentUrl = implode(DIRECTORY_SEPARATOR, $catalogToReadArray);
					$returnStructure['manage'] = "$requestUrl&catalog=$parentUrl";
				}
			} else {
				$record['name'] = $element;
				if (is_dir($catalogPath . DIRECTORY_SEPARATOR . $element)) {
					$record['directory'] = "$requestUrl&catalog=$urlDirectory$element";
					$returnStructure['catalogs'][] = $record;
				} else {
					$record['directory'] = "$requestUrl&action=downloadFile&mode=download&file=$urlDirectory$element";
					$returnStructure['files'][] = $record;
				}
				unset($record);
			}
		}
		return $returnStructure;
	}

	/**
	 * Check is it an allowed directory.
	 *
	 * @param $dir
	 *
	 * @return bool
	 */
	public static function isAllowedDirectory($dir)
	{
		$isAllowed = true;
		$fullPath = self::getCatalogPath() . DIRECTORY_SEPARATOR . $dir;
		if (!is_writable($fullPath) || !is_dir($fullPath) || is_file($fullPath) || strpos($fullPath, '../') !== false || strpos($fullPath, '..\\') !== false) {
			$isAllowed = false;
		}
		return $isAllowed;
	}

	/**
	 * Check is it an allowed file directory.
	 *
	 * @param $dir
	 *
	 * @return bool
	 */
	public static function isAllowedFileDirectory($dir)
	{
		$isAllowed = true;
		$fullPath = self::getCatalogPath() . DIRECTORY_SEPARATOR . $dir;
		if (!is_writable($fullPath) || is_dir($fullPath) || !is_file($fullPath) || strpos($fullPath, '../') !== false || strpos($fullPath, '..\\') !== false) {
			$isAllowed = false;
		}
		return $isAllowed;
	}
}