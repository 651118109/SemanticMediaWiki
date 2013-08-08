<?php

namespace SMW\Test;

use SMW\ConceptParserFunction;
use SMW\MessageFormatter;
use SMW\ParserData;

use Title;
use ParserOutput;

/**
 * Tests for the ConceptParserFunction class
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * @covers \SMW\ConceptParserFunction
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 */
class ConceptParserFunctionTest extends ParserTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string
	 */
	public function getClass() {
		return '\SMW\ConceptParserFunction';
	}

	/**
	 * Provides data sample, the first array contains parametrized input
	 * value while the second array contains expected return results for the
	 * instantiated object.
	 *
	 * @return array
	 */
	public function getDataProvider() {
		return array(

			// #0
			// {{#concept: [[Modification date::+]]
			// }}
			array(
				array(
					'[[Modification date::+]]'
				),
				array(
					'result' => true,
					'propertyCount' => 1,
					'conceptQuery'  => '[[Modification date::+]]',
					'conceptDocu'   => '',
					'conceptSize'   => 1,
					'conceptDepth'  => 1,
				)
			),

			// #1
			// {{#concept: [[Modification date::+]]
			// |Foooooooo
			// }}
			array(
				array(
					'[[Modification date::+]]',
					'Foooooooo'
				),
				array(
					'result' => true,
					'propertyCount' => 1,
					'conceptQuery'  => '[[Modification date::+]]',
					'conceptDocu'   => 'Foooooooo',
					'conceptSize'   => 1,
					'conceptDepth'  => 1,
				)
			)
		);
	}

	/**
	 * NameSpaceDataProvider
	 *
	 * @return array
	 */
	public function getNameSpaceDataProvider() {
		return array(
			array( NS_MAIN, NS_HELP, SMW_NS_CONCEPT )
		);
	}

	/**
	 * Helper method that returns a instance
	 *
	 * @return ConceptParserFunction
	 */
	private function getInstance( Title $title, ParserOutput $parserOutput = null ) {
		return new ConceptParserFunction(
			$this->getParserData( $title, $parserOutput ),
			new MessageFormatter( $title->getPageLanguage() )
		);
	}

	/**
	 * Helper method that returns a text
	 *
	 * @return string
	 */
	private function getMessageText( Title $title, $error ) {
		$message = new MessageFormatter( $title->getPageLanguage() );
		return $message->addFromKey( $error )->getHtml();
	}

	/**
	 * @test ConceptParserFunction::__construct
	 *
	 * @since 1.9
	 */
	public function testConstructor() {
		$instance = $this->getInstance(
			$this->newTitle( SMW_NS_CONCEPT ),
			$this->getParserOutput()
		);
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test ConceptParserFunction::__construct (Test instance exception)
	 *
	 * @since 1.9
	 */
	public function testConstructorException() {
		$this->setExpectedException( 'PHPUnit_Framework_Error' );
		$instance = $this->getInstance( $this->newTitle( SMW_NS_CONCEPT ) );
	}

	/**
	 * @test ConceptParserFunction::parse (Test error on wrong namespace)
	 * @dataProvider getNameSpaceDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $namespace
	 */
	public function testErrorOnNamespace( $namespace ) {
		$title = $this->newTitle( $namespace );
		$errorMessage = $this->getMessageText( $title, 'smw_no_concept_namespace' );
		$instance = $this->getInstance( $title, $this->getParserOutput() );

		$this->assertEquals( $errorMessage, $instance->parse( array() ) );
	}

	/**
	 * @test ConceptParserFunction::parse (Test error on double {{#concept}} use)
	 * @dataProvider getDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $params
	 */
	public function testErrorOnDoubleParse( array $params ) {
		$title = $this->newTitle( SMW_NS_CONCEPT );
		$errorMessage = $this->getMessageText( $title, 'smw_multiple_concepts' );

		$instance = $this->getInstance( $title, $this->getParserOutput() );
 		$instance->parse( $params );

		// First call
		$instance->parse( $params );

		// Second call raises the error
		$this->assertEquals( $errorMessage, $instance->parse( $params ) );
	}

	/**
	 * @test ConceptParserFunction::parse
	 * @dataProvider getDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $params
	 * @param $expected
	 */
	public function testParse( array $params, array $expected ) {
		$parserOutput =  $this->getParserOutput();
		$title = $this->newTitle( SMW_NS_CONCEPT );

		// Initialize and parse
		$instance = $this->getInstance( $title, $parserOutput );
		$instance->parse( $params );

		// Re-read data from stored parserOutput
		$parserData = $this->getParserData( $title, $parserOutput );

		// Check the returned instance
		$this->assertInstanceOf( 'SMWSemanticData', $parserData->getData() );
		$this->assertCount( $expected['propertyCount'], $parserData->getData()->getProperties() );

		// Confirm concept property
		foreach ( $parserData->getData()->getProperties() as $key => $diproperty ){
			$this->assertInstanceOf( 'SMWDIProperty', $diproperty );
			$this->assertEquals( '_CONC' , $diproperty->getKey() );

			// Confirm concept property values
			foreach ( $parserData->getData()->getPropertyValues( $diproperty ) as $dataItem ){
				$this->assertEquals( $expected['conceptQuery'], $dataItem->getConceptQuery() );
				$this->assertEquals( $expected['conceptDocu'], $dataItem->getDocumentation() );
				$this->assertEquals( $expected['conceptSize'], $dataItem->getSize() );
				$this->assertEquals( $expected['conceptDepth'], $dataItem->getDepth() );
			}
		}
	}

	/**
	 * @test ConceptParserFunction::render
	 *
	 * @since 1.9
	 */
	public function testStaticRender() {
		$parser = $this->getParser( $this->newTitle(), $this->getUser() );
		$result = ConceptParserFunction::render( $parser );
		$this->assertInternalType( 'string', $result );
	}
}
