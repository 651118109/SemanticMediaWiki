<?php

namespace SMW\Test;

use SMW\BeforePageDisplay;

use OutputPage;
use Title;

/**
 * Tests for the BeforePageDisplay class
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * @covers \SMW\BeforePageDisplay
 * @covers \SMW\FunctionHook
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 */
class BeforePageDisplayTest extends SemanticMediaWikiTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string|false
	 */
	public function getClass() {
		return '\SMW\BeforePageDisplay';
	}

	/**
	 * Helper method that returns a OutputPage object
	 *
	 * @since 1.9
	 *
	 * @return OutputPage
	 */
	private function newOutputPage( Title $title = null ) {

		if ( $title === null ) {
			$title = $this->newTitle();
		}

		$context = $this->newContext();
		$context->setTitle( $title );
		$context->setLanguage( $this->getLanguage() );

		return new OutputPage( $context );
	}

	/**
	 * Returns a BeforePageDisplay object
	 *
	 * @since 1.9
	 */
	public function newInstance( OutputPage $outputPage ) {

		$skin     = $this->newMockObject()->getMockSkin();
		$instance = new BeforePageDisplay( $outputPage, $skin );

		$instance->setDependencyBuilder( $this->newDependencyBuilder() );

		return $instance;
	}

	/**
	 * @test BeforePageDisplay::__construct
	 *
	 * @since 1.9
	 */
	public function testConstructor() {
		$this->assertInstanceOf( $this->getClass(), $this->newInstance( $this->newOutputPage() ) );
	}

	/**
	 * @test BeforePageDisplay::process
	 * @dataProvider titleDataProvider
	 *
	 * @since 1.9
	 */
	public function testProcess( $setup, $expected ) {

		$outputPage = $this->newOutputPage( $setup['title'] );
		$result     = $this->newInstance( $outputPage )->process();

		$this->assertInternalType( 'boolean', $result );
		$this->assertTrue( $result );

		// Check if content was added to the output object
		$contains = false;

		if ( method_exists( $outputPage, 'getHeadLinksArray' ) ) {
			foreach ( $outputPage->getHeadLinksArray() as $key => $value ) {
				if ( strpos( $value, 'ExportRDF' ) ){
					$contains = true;
					break;
				};
			}
		} else{
			// MW 1.19
			if ( strpos( $outputPage->getHeadLinks(), 'ExportRDF' ) ){
				$contains = true;
			};
		}

		$expected['result'] ? $this->assertTrue( $contains ) : $this->assertFalse( $contains );
	}

	/**
	 * @return array
	 */
	public function titleDataProvider() {

		$provider = array();

		// #0 Standard title
		$provider[] = array(
			array(
				'title'  => $this->newMockObject( array(
					'isSpecialPage'   => false,
					'getPageLanguage' => $this->getLanguage(),
					'getPrefixedText' => $this->getRandomString()
				) )->getMockTitle()
			),
			array(
				'result' => true
			)
		);

		// #1 Title is SpeciaPage
		$provider[] = array(
			array(
				'title'  => $this->newMockObject( array(
					'isSpecialPage'   => true,
					'getPageLanguage' => $this->getLanguage(),
					'getPrefixedText' => $this->getRandomString()
				) )->getMockTitle()
			),
			array(
				'result' => false
			)
		);

		return $provider;
	}

}
