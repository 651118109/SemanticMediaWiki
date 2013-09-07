<?php

namespace SMW\Test;

use SMW\ApiAskArgs;

/**
 * Tests for the ApiAskArgs class
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * @covers \SMW\ApiAskArgs
 * @covers \SMW\ApiBase
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 * @group API
 */
class ApiAskArgsTest extends ApiTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SMW\ApiAskArgs';
	}

	/**
	 * @test ApiAskArgs::execute
	 * @dataProvider queryDataProvider
	 *
	 * This test only verifies if either an error result or
	 * a "normal" query result array is returned. The test makes
	 * only an assymptions about a predefinied property
	 * "Modification date" printrequests
	 *
	 * @since 1.9
	 *
	 * @param array $query
	 * @param array $expected
	 */
	public function testExecuteOnDefaultStore( array $query, array $expected ) {

		$results = $this->doApiRequest( array(
			'action'     => 'askargs',
			'conditions' => $query['conditions'],
			'printouts'  => $query['printouts'],
			'parameters' => $query['parameters']
		) );

		$this->assertInternalType( 'array', $results );

		if ( isset( $expected['error'] ) ) {
			$this->assertArrayHasKey( 'error', $results );
		} else {
			$this->assertEquals( $expected, $results['query']['printrequests'] );
		}

	}

	/**
	 * @test ApiAskArgs::execute
	 *
	 * Test against a mock store to ensure that methods are executed
	 * regardless whether a "real" Store is available or not
	 *
	 * @since 1.9
	 */
	public function testExecuteOnMockStore() {

		$requestParameters = array(
			'conditions' => 'Foo::+',
			'printouts'  => 'Bar',
			'parameters' => 'sort=asc'
		);

		$expected = array(
			'query-continue-offset' => 10,
			'query' => array(
				'results' => array(
					'Foo' => array(
						'printouts' => array( 'lula' => array( 'lila' ) )
					)
				),
				'printrequests' => array( 'Bar' ),
				'meta' => array( 'count' => 5, 'offset' => 5 )
			)
		);

		$mockStore = $this->newMockBuilder()->newObject( 'Store', array(
			'getQueryResult' => array( $this, 'mockStoreQueryResultCallback' )
		) );

		$api = new ApiAskArgs( $this->getApiMain( $requestParameters ), 'askargs' );
		$api->setStore( $mockStore );
		$api->execute();

		$result = $api->getResultData();

		$this->assertInternalType( 'array', $result );
		$this->assertEquals( $expected, $result );
	}

	/**
	 * Use a callback injection to control the return value of the
	 * induced mock object
	 *
	 * @return SMWQueryResult
	 */
	public function mockStoreQueryResultCallback( $query ) {

		$result = '';

		if ( $query->getQueryString() === '[[Foo::+]]' ) {
			$result = array(
				'results' => array(
					'Foo' => array(
						'printouts' => array( 'lula' => array( 'lila' ) )
					)
				),
				'printrequests' => array( 'Bar' ),
				'meta' => array( 'count' => 5, 'offset' => 5 )
			);
		}

		return $this->newMockBuilder()->newObject( 'QueryResult', array(
			'toArray'           => $result,
			'hasFurtherResults' => true
		) );

	}

	/**
	 * Provides a query array and its expected printrequest array
	 *
	 * @return array
	 */
	public function queryDataProvider() {
		return array(

			// #0 Query producing an error result
			array(
				array(
					'conditions' => '[[Modification date::+]]',
					'printouts'  => null,
					'parameters' => null
				),
				array(
					'error'      => true
				)
			),

			// #1 Query producing an error result
			array(
				array(
					'conditions' => '[[Modification date::+]]',
					'printouts'  => null,
					'parameters' => 'limit=10'
				),
				array(
					'error'      => true
				)
			),

			// #2 Query producing an error result
			array(
				array(
					'conditions' => '[[Modification date::+]]',
					'printouts'  => 'Modification date',
					'parameters' => 'limit=10'
				),
				array(
					'error'      => true
				)
			),

			// #3 Query producing a return result
			array(
				array(
					'conditions' => 'Modification date::+',
					'printouts'  => null,
					'parameters' => null
				),
				array(
					array(
						'label'=> '',
						'typeid' => '_wpg',
						'mode' => 2,
						'format' => false
					)
				)
			),

			// #4 Query producing a return result
			array(
				array(
					'conditions' => 'Modification date::+',
					'printouts'  => 'Modification date',
					'parameters' => null
				),
				array(
					array(
						'label'=> '',
						'typeid' => '_wpg',
						'mode' => 2,
						'format' => false
					),
					array(
						'label'=> 'Modification date',
						'typeid' => '_dat',
						'mode' => 1,
						'format' => ''
					)
				)
			),

			// #5 Query producing a return result
			array(
				array(
					'conditions' => 'Modification date::+',
					'printouts'  => 'Modification date',
					'parameters' => 'limit=1'
				),
				array(
					array(
						'label'=> '',
						'typeid' => '_wpg',
						'mode' => 2,
						'format' => false
					),
					array(
						'label'=> 'Modification date',
						'typeid' => '_dat',
						'mode' => 1,
						'format' => ''
					)
				)
			),
		);
	}
}
