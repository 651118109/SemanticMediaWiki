<?php

namespace SMW\Test;

use SMW\ParserTextProcessor;

use Title;
use ParserOutput;
use ReflectionClass;

/**
 * Tests for the ParserTextProcessor class
 *
 * @since 1.9
 *
 * @file
 * @ingroup SMW
 * @ingroup Test
 *
 * @licence GNU GPL v2+
 * @author mwjames
 */

/**
 * Tests for the ParserTextProcessor class
 * @covers \SMW\ParserTextProcessor
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 */
class ParserTextProcessorTest extends ParserTestCase {

	/**
	 * Returns the name of the class to be tested
	 *
	 * @return string|false
	 */
	public function getClass() {
		return '\SMW\ParserTextProcessor';
	}

	/**
	 * Provides text sample, following namespace, the settings to be used,
	 * text string, and expected result array with {result text, property count,
	 * property label, and property value}
	 *
	 * @return array
	 */
	public function getTextDataProvider() {
		return array(

			// #0 NS_MAIN; [[FooBar...]] with a different caption
			array(
				NS_MAIN,
				array(
					'smwgNamespacesWithSemanticLinks' => array( NS_MAIN => true ),
					'smwgLinksInValues' => false,
					'smwgInlineErrors' => true,
				),
				'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
				' [[FooBar::dictumst|寒い]] cursus. Nisl sit condimentum Quisque facilisis' .
				' Suspendisse [[Bar::tincidunt semper]] facilisi dolor Aenean. Ut' .
				' Aliquam {{volutpat}} arcu ultrices eu Ut quis [[foo::9001]] et Donec.',
				array(
					'resultText'    => 'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
						' [[:Dictumst|寒い]] cursus. Nisl sit condimentum Quisque facilisis' .
						' Suspendisse [[:Tincidunt semper|tincidunt semper]] facilisi dolor Aenean. Ut' .
						' Aliquam {{volutpat}} arcu ultrices eu Ut quis [[:9001|9001]] et Donec.',
					'propertyCount' => 3,
					'propertyLabel' => array( 'Foo', 'Bar', 'FooBar' ),
					'propertyValue' => array( 'Dictumst', 'Tincidunt semper', '9001' )
				)
			),

			// #1 NS_MAIN; [[FooBar...]] with a different caption and smwgLinksInValues = true
			array(
				NS_MAIN,
				array(
					'smwgNamespacesWithSemanticLinks' => array( NS_MAIN => true ),
					'smwgLinksInValues' => true,
					'smwgInlineErrors' => true,
				),
				'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
				' [[FooBar::dictumst|寒い]] cursus. Nisl sit condimentum Quisque facilisis' .
				' Suspendisse [[Bar::[[tincidunt semper]]]] facilisi dolor Aenean. Ut' .
				' Aliquam {{volutpat}} arcu ultrices eu Ut quis [[foo::[http:://www/foo/9001] ]] et Donec.',
				array(
					'resultText'    => 'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
						' [[:Dictumst|寒い]] cursus. Nisl sit condimentum Quisque facilisis' .
						' Suspendisse [[:Tincidunt semper|tincidunt semper]] facilisi dolor Aenean. Ut' .
						' Aliquam {{volutpat}} arcu ultrices eu Ut quis'.
						' [[:Http:://www/foo/9001|http:://www/foo/9001]] et Donec.',
					'propertyCount' => 3,
					'propertyLabel' => array( 'Foo', 'Bar', 'FooBar' ),
					'propertyValue' => array( 'Dictumst', 'Tincidunt semper', 'Http:://www/foo/9001' )
				)
			),

			// #1 NS_MAIN, [[-FooBar...]] produces an error with inlineErrors = true
			// (only check for an indication of an error in 'resultText' )
			array(
				NS_MAIN,
				array(
					'smwgNamespacesWithSemanticLinks' => array( NS_MAIN => true ),
					'smwgLinksInValues' => false,
					'smwgInlineErrors' => true,
				),
				'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
				' [[-FooBar::dictumst|重い]] cursus. Nisl sit condimentum Quisque facilisis' .
				' Suspendisse [[Bar::tincidunt semper]] facilisi dolor Aenean. Ut' .
				' Aliquam {{volutpat}} arcu ultrices eu Ut quis [[foo::9001]] et Donec.',
				array(
					'resultText'    => 'class="smw-highlighter" data-type="4" data-state="inline"',
					'propertyCount' => 2,
					'propertyLabel' => array( 'Foo', 'Bar' ),
					'propertyValue' => array( 'Tincidunt semper', '9001' )
				)
			),

			// #2 NS_MAIN, [[-FooBar...]] produces an error but inlineErrors = false
			array(
				NS_MAIN,
				array(
					'smwgNamespacesWithSemanticLinks' => array( NS_MAIN => true ),
					'smwgLinksInValues' => false,
					'smwgInlineErrors' => false,
				),
				'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
				' [[-FooBar::dictumst|軽い]] cursus. Nisl sit condimentum Quisque facilisis' .
				' Suspendisse [[Bar::tincidunt semper]] facilisi dolor Aenean. Ut' .
				' Aliquam {{volutpat}} arcu ultrices eu Ut quis [[foo::9001]] et Donec.',
				array(
					'resultText'    => 'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
						' 軽い cursus. Nisl sit condimentum Quisque facilisis' .
						' Suspendisse [[:Tincidunt semper|tincidunt semper]] facilisi dolor Aenean. Ut' .
						' Aliquam {{volutpat}} arcu ultrices eu Ut quis [[:9001|9001]] et Donec.',
					'propertyCount' => 2,
					'propertyLabel' => array( 'Foo', 'Bar' ),
					'propertyValue' => array( 'Tincidunt semper', '9001' )
				)
			),

			// #3 NS_HELP disabled
			array(
				NS_HELP,
				array(
					'smwgNamespacesWithSemanticLinks' => array( NS_HELP => false ),
					'smwgLinksInValues' => false,
					'smwgInlineErrors' => true,
				),
				'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
				' [[FooBar::dictumst|おもろい]] cursus. Nisl sit condimentum Quisque facilisis' .
				' Suspendisse [[Bar::tincidunt semper]] facilisi dolor Aenean. Ut' .
				' Aliquam {{volutpat}} arcu ultrices eu Ut quis [[foo::9001]] et Donec.',
				array(
					'resultText'    => 'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
						' [[:Dictumst|おもろい]] cursus. Nisl sit condimentum Quisque facilisis' .
						' Suspendisse [[:Tincidunt semper|tincidunt semper]] facilisi dolor Aenean. Ut' .
						' Aliquam {{volutpat}} arcu ultrices eu Ut quis [[:9001|9001]] et Donec.',
					'propertyCount' => 0,
					'propertyLabel' => array(),
					'propertyValue' => array()
				)
			),

			// #4 NS_HELP enabled but no properties or links at all
			array(
				NS_HELP,
				array(
					'smwgNamespacesWithSemanticLinks' => array( NS_HELP => true ),
					'smwgLinksInValues' => false,
					'smwgInlineErrors' => true,
				),
				'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
				' Suspendisse tincidunt semper facilisi dolor Aenean.',
				array(
					'resultText'    => 'Lorem ipsum dolor sit &$% consectetuer auctor at quis' .
						' Suspendisse tincidunt semper facilisi dolor Aenean.',
					'propertyCount' => 0,
					'propertyLabel' => array(),
					'propertyValue' => array()
				)
			),
		);
	}

	/**
	 * Provides magic words sample text
	 *
	 * @return array
	 */
	public function getMagicWordDataProvider() {
		return array(
			// #0 __NOFACTBOX__
			array(
				NS_MAIN,
				'Lorem ipsum dolor sit amet consectetuer auctor at quis' .
				' [[Foo::dictumst cursus]]. Nisl sit condimentum Quisque facilisis' .
				' Suspendisse [[Bar::tincidunt semper]] facilisi dolor Aenean. Ut' .
				' __NOFACTBOX__ ',
				array( 'SMW_NOFACTBOX' )
			),

			// #1 __SHOWFACTBOX__
			array(
				NS_HELP,
				'Lorem ipsum dolor sit amet consectetuer auctor at quis' .
				' [[Foo::dictumst cursus]]. Nisl sit condimentum Quisque facilisis' .
				' Suspendisse [[Bar::tincidunt semper]] facilisi dolor Aenean. Ut' .
				' __SHOWFACTBOX__',
				array( 'SMW_SHOWFACTBOX' )
			),
		);
	}

	/**
	 * Helper method that returns a ParserTextProcessor object
	 *
	 * @param $title
	 * @param $parserOutput
	 * @param $settings
	 *
	 * @return ParserTextProcessor
	 */
	private function getInstance( Title $title, ParserOutput $parserOutput, array $settings = array() ) {
		return new ParserTextProcessor(
			$this->getParserData( $title, $parserOutput ),
			$this->newSettings( $settings )
		);
	}

	/**
	 * @test ParserTextProcessor::__construct
	 * @dataProvider getTextDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $namespace
	 */
	public function testConstructor( $namespace ) {
		$instance = $this->getInstance( $this->newTitle( $namespace ), $this->newParserOutput() );
		$this->assertInstanceOf( $this->getClass(), $instance );
	}

	/**
	 * @test ParserTextProcessor::stripMagicWords
	 * @dataProvider getMagicWordDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $namespace
	 * @param $text
	 * @param array $expected
	 */
	public function testStripMagicWords( $namespace, $text, array $expected ) {

		$parserOutput = $this->newParserOutput();
		$title        = $this->newTitle( $namespace );
		$instance     = $this->getInstance( $title, $parserOutput );

		// Make protected method accessible
		$reflector = $this->newReflector();
		$method    = $reflector->getMethod( 'stripMagicWords' );
		$method->setAccessible( true );

		$result = $method->invoke( $instance, array( &$text ) );

		// Check return values
		$this->assertInternalType( 'array', $result );
		$this->assertEquals( $expected, $result );

		// Check values against ParserData/ParserOutput object
		$parserData = $this->getParserData( $title, $parserOutput );

		if ( method_exists( $parserOutput, 'getExtensionData' ) ) {
			$this->assertEquals( $expected, $parserData->getOutput()->getExtensionData( 'smwmagicwords' ) );
		} else {
			$this->assertEquals( $expected, $parserData->getOutput()->mSMWMagicWords );
		}
	}

	/**
	 * @test ParserTextProcessor::parse
	 * @dataProvider getTextDataProvider
	 *
	 * @since 1.9
	 *
	 * @param $namespace
	 * @param array $settings
	 * @param $text
	 * @param array $expected
	 */
	public function testParse( $namespace, array $settings, $text, array $expected ) {

		$parserOutput = $this->newParserOutput();
		$title        = $this->newTitle( $namespace );
		$instance     = $this->getInstance( $title, $parserOutput, $settings );

		// Text parsing
		$instance->parse( $text );

		// Check transformed text
		$this->assertContains( $expected['resultText'], $text );

		// Re-read data from stored parserOutput
		$parserData = $this->getParserData( $title, $parserOutput );

		// Check the returned instance
		$this->assertInstanceOf( '\SMW\SemanticData', $parserData->getData() );
		$this->assertSemanticData( $parserData->getData(), $expected );
	}

	/**
	 * @test ParserTextProcessor::parse
	 *
	 * @since 1.9
	 */
	public function testRedirect() {

		$namespace = NS_MAIN;
		$text      = '#REDIRECT [[:Lala]]';

		// Create text processor instance
		$parserOutput = $this->newParserOutput();
		$title        = $this->newTitle( $namespace );

		$settings = $this->newSettings( array(
			'smwgNamespacesWithSemanticLinks' => array( $namespace => true ),
			'smwgLinksInValues' => false,
			'smwgInlineErrors'  => true,
		) );

		$parserData = $this->getParserData( $title, $parserOutput );

		$instance = new ParserTextProcessor( $parserData, $settings );
		$instance->parse( $text );

		// Build expected results from a successful setRedirect execution
		$expected['propertyCount'] = 1;
		$expected['propertyKey']   = '_REDI';
		$expected['propertyValue'] = ':Lala';

		// Check the returned instance
		$this->assertInstanceOf( '\SMW\SemanticData', $parserData->getData() );
		$this->assertSemanticData( $parserData->getData(), $expected );
	}

	/**
	 * @test ParserTextProcessor::process
	 *
	 * @since 1.9
	 */
	public function testProcess() {

		$parserOutput = $this->newParserOutput();
		$title        = $this->newTitle();
		$instance     = $this->getInstance( $title, $parserOutput );

		// Make protected methods accessible
		$reflection = $this->newReflector();

		$method = $reflection->getMethod( 'process' );
		$method->setAccessible( true );

		$result = $method->invoke( $instance, array() );
		$this->assertEmpty( $result );

		$result = $method->invoke( $instance, array( 'Test::foo', 'SMW' , 'lula' ) );
		$this->assertEmpty( $result );

		$result = $method->invoke( $instance, array( 'Test::bar', 'SMW' , 'on' ) );
		$this->assertEmpty( $result );

		$result = $method->invoke( $instance, array( 'Test::lula', 'SMW' , 'off' ) );
		$this->assertEmpty( $result );
	}

}
