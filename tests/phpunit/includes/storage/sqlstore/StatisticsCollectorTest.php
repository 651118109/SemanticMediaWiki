<?php

namespace SMW\Test\SQLStore;

use SMW\SQLStore\StatisticsCollector;
use SMW\StoreFactory;
use SMW\Settings;
use SMW\Store;

use FakeResultWrapper;

/**
 *Test for the StatisticsCollector class
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * @covers \SMW\SQLStore\StatisticsCollector
 *
 * @ingroup SQLStoreTest
 *
 * @group SMW
 * @group SMWExtension
 */
class StatisticsCollectorTest extends \SMW\Test\SemanticMediaWikiTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string|false
	 */
	public function getClass() {
		return '\SMW\SQLStore\StatisticsCollector';
	}

	/**
	 * Helper method that returns a StatisticsCollector object
	 *
	 * @since 1.9
	 *
	 * @param $count
	 * @param $cacheEnabled
	 *
	 * @return StatisticsCollector
	 */
	private function newInstance( $count = 1, $cacheEnabled = false, $hash = 'foo' ) {

		// $store = $this->newMockObject( array( 'getPropertyTables' => array( 'smw_test' ) ) )->getMockStore();
		$store = StoreFactory::getStore();

		$result = array(
			'count'  => $count,
			'o_hash' => $hash
		);

		$resultWrapper = new FakeResultWrapper( array( (object)$result ) );
		$resultWrapper->count = $count;

		// Database stub object which makes the test
		// independent from the real DB
		$connection = $this->getMock( 'DatabaseMysql' );

		// Override methods with expected return objects
		$connection->expects( $this->any() )
			->method( 'select' )
			->will( $this->returnValue( $resultWrapper ) );

		$connection->expects( $this->any() )
			->method( 'selectRow' )
			->will( $this->returnValue( $resultWrapper ) );

		$connection->expects( $this->any() )
			->method( 'fetchObject' )
			->will( $this->returnValue( $resultWrapper ) );

		$connection->expects( $this->any() )
			->method( 'estimateRowCount' )
			->will( $this->returnValue( $count ) );

		// Settings to be used
		$settings = $this->newSettings( array(
			'smwgCacheType' => 'hash',
			'smwgStatisticsCache' => $cacheEnabled,
			'smwgStatisticsCacheExpiry' => 3600
		) );

		return new StatisticsCollector( $store, $connection, $settings );
	}

	/**
	 * @test StatisticsCollector::__construct
	 *
	 * @since 1.9
	 */
	public function testConstructor() {
		$instance = $this->newInstance();
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test StatisticsCollector::newFromStore
	 *
	 * @since 1.9
	 */
	public function testNewFromStore() {
		$instance = StatisticsCollector::newFromStore( StoreFactory::getStore() );
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test StatisticsCollector::getUsedPropertiesCount
	 * @test StatisticsCollector::getPropertyUsageCount
	 * @test StatisticsCollector::getDeclaredPropertiesCount
	 * @test StatisticsCollector::getSubobjectCount
	 * @test StatisticsCollector::getConceptCount
	 * @test StatisticsCollector::getQueryFormatsCount
	 * @test StatisticsCollector::getQuerySize
	 * @test StatisticsCollector::getQueryCount
	 * @test StatisticsCollector::getPropertyPageCount
	 * @dataProvider getFunctionDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $function
	 * @param $expectedType
	 */
	public function testFunctions( $function, $expectedType ) {

		$count = rand();
		$hash  = 'Quxxey';
		$expectedCount = $expectedType === 'array' ? array( $hash => $count ) : $count;

		$instance = $this->newInstance( $count, false, $hash );

		$result = call_user_func( array( &$instance, $function ) );

		$this->assertInternalType( $expectedType, $result );
		$this->assertEquals( $expectedCount, $result );
	}

	/**
	 * @test StatisticsCollector::getResults
	 * @dataProvider getCollectorDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $segment
	 * @param $expectedType
	 */
	public function testResultsOnStore( $segment, $expectedType ) {

		$instance = $this->newInstance();
		$result   = $instance->getResults();

		$this->assertInternalType( $expectedType, $result[$segment] );
	}

	/**
	 * @test StatisticsCollector::getResults
	 * @dataProvider getCacheNonCacheDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $test
	 * @param $expected
	 */
	public function testCachNoCache( array $test, array $expected ) {

		// Sample A
		$instance = $this->newInstance( $test['A'], $test['cacheEnabled'] );
		$result = $instance->getResults();
		$this->assertEquals( $expected['A'], $result['OWNPAGE'] );

		// Sample B
		$instance = $this->newInstance( $test['B'], $test['cacheEnabled'] );
		$result = $instance->getResults();
		$this->assertEquals( $expected['B'], $result['OWNPAGE'] );

		$this->assertEquals( $test['cacheEnabled'], $instance->isCached() );

	}

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function getFunctionDataProvider() {
		return array(
			array( 'getUsedPropertiesCount',     'integer' ),
			array( 'getPropertyUsageCount',      'integer' ),
			array( 'getDeclaredPropertiesCount', 'integer' ),
			array( 'getSubobjectCount',          'integer' ),
			array( 'getConceptCount',            'integer' ),
			array( 'getQueryFormatsCount',       'array'   ),
			array( 'getQuerySize',               'integer' ),
			array( 'getQueryCount',              'integer' ),
			array( 'getPropertyPageCount',       'integer' )
		);
	}

	/**
	 * DataProvider
	 *
	 * @return array
	 */
	public function getCollectorDataProvider() {
		return array(
			array( 'OWNPAGE',      'integer' ),
			array( 'QUERY',        'integer' ),
			array( 'QUERYSIZE',    'integer' ),
			array( 'QUERYFORMATS', 'array'   ),
			array( 'CONCEPTS',     'integer' ),
			array( 'SUBOBJECTS',   'integer' ),
			array( 'DECLPROPS',    'integer' ),
			array( 'USEDPROPS',    'integer' ),
			array( 'PROPUSES',     'integer' )
		);
	}

	/**
	 * Cache and non-cache data tests sample
	 *
	 * @return array
	 */
	public function getCacheNonCacheDataProvider() {
		return array(
			array(

				// #0 Invoke different A & B count but expect that
				// A value is returned for both since cache is enabled
				array( 'cacheEnabled' => true,  'A' => 1001, 'B' => 9001 ),
				array( 'A' => 1001, 'B' => 1001 )
			),
			array(

				// #1 Invoke different A & B count and expect that since
				// cache is disabled the original result is returned
				array( 'cacheEnabled' => false, 'A' => 2001, 'B' => 9001 ),
				array( 'A' => 2001, 'B' => 9001 )
			)
		);
	}
}
