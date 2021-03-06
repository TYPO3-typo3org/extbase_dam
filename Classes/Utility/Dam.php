<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nils Blattner <nb@cabag.ch>, cab services ag
*  			
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This Utility
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_ExtbaseDam_Utility_Dam {
	
	/**
	 * Per call cache of the objects taken from the db.
	 * @var array
	 */
	protected static $objectStorage = array();
	
	/**
	 * Will return an array of Tx_ExtbaseDam_Domain_Model_Dam objects for the given table/uid/ident combination.
	 *
	 * @var string $table The table that dam-records have to be fetched for.
	 * @var int $uid The UID of the record for which the dam-records have to be fetched for.
	 * @var string $ident The field ident that dam-records have to be fetched for.
	 * @var string $orderBy Any ordering that should be applied.
	 * @var int $limit The maximum amount of objects to return.
	 * @return array An array of Dam objects.
	 */
	public static function get($table, $uid, $ident, $orderBy = '', $limit = 1000) {
		// check if this exact function call has been made before and give the cached result
		if (isset(self::$objectStorage['ref'][$table][$uid][$ident][$orderBy][$limit])) {
			return self::$objectStorage['ref'][$table][$uid][$ident][$orderBy][$limit];
		}
		
		// gets the data from DAM
		$damArray = tx_dam_db::getReferencedFiles($table, $uid, $ident, 'tx_dam_mm_ref', 'tx_dam.*', array(), '', $orderBy, $limit);
		$rows = $damArray['rows'];
		
		if (count($rows) > 0) {
			// dataMapper is a singleton
			$dataMapper = t3lib_div::makeInstance('Tx_Extbase_Persistence_Mapper_DataMapper');
			$objects = $dataMapper->map('Tx_ExtbaseDam_Domain_Model_Dam', $rows);
			
			// cache the function call
			self::$objectStorage['ref'][$table][$uid][$ident][$orderBy][$limit] = $objects;
			
			return $objects;
		}
		
		self::$objectStorage['ref'][$table][$uid][$ident][$orderBy][$limit] = array();
		
		// no records found
		return array();
	}
	
	/**
	 * Will return one Tx_ExtbaseDam_Domain_Model_Dam object for the given table/uid/ident combination.
	 *
	 * @var string $table The table that dam-records have to be fetched for.
	 * @var int $uid The UID of the record for which the dam-records have to be fetched for.
	 * @var string $ident The field ident that dam-records have to be fetched for.
	 * @var string $orderBy Any ordering that should be applied.
	 * @return Tx_ExtbaseDam_Domain_Model_Dam The (first) Dam object
	 */
	public static function getOne($table, $uid, $ident, $orderBy = '') {
		$objects = Tx_ExtbaseDam_Utility_Dam::get($table, $uid, $ident, 1, $orderBy);
		return count($objects) > 0 ? $objects[0] : null;
	}
	
	/**
	 * Finds all the DAM-records within a specific path.
	 *
	 * @var string $path The file path.
	 * @var string $orderBy Any ordering that should be applied.
	 * @var int $limit The maximum amount of objects to return.
	 * @return array Array of Dam objects.
	 */
	public static function getByPath($path, $orderBy = '', $limit = 1000) {
		// check if this exact function call has been made before and give the cached result
		if (isset(self::$objectStorage['path'][$path][$orderBy][$limit])) {
			return self::$objectStorage['path'][$path][$orderBy][$limit];
		}
		
		// removes the absolute path and adds a trailing '/' if there is none
		$path = preg_replace(array('/^' . PATH_site . '/', '/\/?$/'), array('', '/'), $path);
		$rows = getDataWhere ('*', $path . '%', '', $orderBy, $limit);
		
		if (count($rows) > 0) {
			// dataMapper is a singleton
			$dataMapper = t3lib_div::makeInstance('Tx_Extbase_Persistence_Mapper_DataMapper');
			$objects = $dataMapper->map('Tx_ExtbaseDam_Domain_Model_Dam', $rows);
			
			// cache the function call
			self::$objectStorage['path'][$path][$orderBy][$limit] = $objects;
			
			return $objects;
		}
		
		self::$objectStorage['path'][$path][$orderBy][$limit] = array();
		
		// no records found
		return array();
	}
	
	/**
	 * Will return one Tx_ExtbaseDam_Domain_Model_Dam object for the given table/uid/ident combination.
	 *
	 * @var string $path The file path.
	 * @var string $orderBy Any ordering that should be applied.
	 * @return Tx_ExtbaseDam_Domain_Model_Dam The (first) Dam object
	 */
	public static function getOneByPath($path, $orderBy = '') {
		$objects = Tx_ExtbaseDam_Utility_Dam::getByPath($path, $orderBy, 1);
		return count($objects) > 0 ? $objects[0] : null;
	}
	
	/**
	 * Flushes the per call cache should that be necessary.
	 *
	 * @return void
	 */
	public static function flushCache() {
		self::$objectStorage = array();
	}
}
?>
