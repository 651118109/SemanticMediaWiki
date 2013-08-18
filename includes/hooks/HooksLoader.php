<?php

namespace SMW;

/**
 * Convenience class to load a MediaWiki hook
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * Convenience class to load a MediaWiki hook
 *
 * @ingroup Hooks
 */
class HooksLoader {

	/**
	 * Convenience method to load a MediaWiki hook object
	 *
	 * @since  1.9
	 *
	 * @param HookBase $hook
	 *
	 * @return HookBase
	 */
	public static function register( HookBase $hook ) {
		return $hook;
	}

	/**
	 * This being temporary to demonstrate the injection of the DependencyBuilder
	 *
	 * @since  1.9
	 */
	public static function prepare( $hook ) {
		$hook->setDependencyBuilder( new SimpleDependencyBuilder( new CommonDependencyContainer() ) );
		return $hook;
	}

}
