<?php

namespace SMW\SQLStore;

use SMW\Store\CacheableObjectCollector;

use SMW\SimpleDictionary;
use SMW\DIProperty;
use SMW\Profiler;
use SMW\Settings;
use SMW\Store;

use DatabaseBase;

/**
 * Collects wanted properties from a store entity
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 * @author Nischay Nahata
 */

/**
 * Collects wanted properties from a store entity
 *
 * @ingroup CacheableObjectCollector
 * @ingroup SQLStore
 */
class WantedPropertiesCollector extends CacheableObjectCollector {

	/** @var Store */
	protected $store;

	/** @var Settings */
	protected $settings;

	/** @var DatabaseBase */
	protected $dbConnection;

	/**
	 * @since 1.9
	 *
	 * @param Store $store
	 * @param DatabaseBase $dbw
	 * @param Settings $settings
	 */
	public function __construct( Store $store, DatabaseBase $dbw, Settings $settings ) {
		$this->store = $store;
		$this->dbConnection = $dbw;
		$this->settings = $settings;
	}

	/**
	 * Factory method for an immediate instantiation of a WantedPropertiesCollector object
	 *
	 * @par Example:
	 * @code
	 *  $properties = \SMW\SQLStore\WantedPropertiesCollector::newFromStore( $store )
	 *  $properties->getResults();
	 * @endcode
	 *
	 * @since 1.9
	 *
	 * @param Store $store
	 * @param $dbw Boolean or DatabaseBase:
	 * - Boolean: whether to use a dedicated DB or Slave
	 * - DatabaseBase: database connection to use
	 *
	 * @return ObjectCollector
	 */
	public static function newFromStore( Store $store, $dbw = false ) {

		$dbw = $dbw instanceof DatabaseBase ? $dbw : wfGetDB( DB_SLAVE );
		$settings = Settings::newFromGlobals();
		return new self( $store, $dbw, $settings );
	}

	/**
	 * @see CacheableObjectCollector::cacheSetup
	 *
	 * @since 1.9
	 *
	 * @return ObjectDictionary
	 */
	protected function cacheSetup() {
		return new SimpleDictionary( array(
			'id'      => array( 'smwgWantedPropertiesCache', $this->settings->get( 'smwgPDefaultType' ), $this->requestOptions ),
			'type'    => $this->settings->get( 'smwgCacheType' ),
			'enabled' => $this->settings->get( 'smwgWantedPropertiesCache' ),
			'expiry'  => $this->settings->get( 'smwgWantedPropertiesCacheExpiry' )
		) );
	}

	/**
	 * Returns unused properties
	 *
	 * @since 1.9
	 *
	 * @return DIProperty[]
	 */
	protected function doCollect() {

		$result = array();

		// Wanted Properties must have the default type
		$this->propertyTables = $this->getPropertyTables( $this->settings->get( 'smwgPDefaultType' ) );

		// anything else would be crazy, but let's fail gracefully even if the whole world is crazy
		if ( !$this->propertyTables->isFixedPropertyTable() ) {
			$result = $this->doQuery();
		}

		return $result;
	}

	/**
	 * Returns wanted properties
	 *
	 * @note This function is very resource intensive and needs to be cached on
	 * medium/large wikis.
	 *
	 * @since 1.9
	 *
	 * @return DIProperty[]
	 */
	protected function doQuery() {
		Profiler::In( __METHOD__ );

		$result = array();

		$options = $this->store->getSQLOptions( $this->requestOptions, 'title' );
		$options['ORDER BY'] = 'count DESC';

		// TODO: this is not how JOINS should be specified in the select function
		$res = $this->dbConnection->select(
			$this->dbConnection->tableName( $this->propertyTables->getName() ) . ' INNER JOIN ' .
				$this->dbConnection->tableName( $this->store->getObjectIds()->getIdTable() ) . ' ON p_id=smw_id LEFT JOIN ' .
				$this->dbConnection->tableName( 'page' ) . ' ON (page_namespace=' .
				$this->dbConnection->addQuotes( SMW_NS_PROPERTY ) . ' AND page_title=smw_title)',
			'smw_title, COUNT(*) as count',
			'smw_id > 50 AND page_id IS NULL GROUP BY smw_title',
			__METHOD__,
			$options
		);

		foreach ( $res as $row ) {
			$result[] = array( new DIProperty( $row->smw_title ), $row->count );
		}

		Profiler::Out( __METHOD__ );
		return $result;
	}
}
