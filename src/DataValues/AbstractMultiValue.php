<?php

namespace SMW\DataValues;

use SMWDataValue as DataValue;

/**
 * @private
 *
 * @license GNU GPL v2+
 * @since 2.5
 *
 * @author mwjames
 */
abstract class AbstractMultiValue extends DataValue {

	/**
	 * @since 2.5
	 *
	 * @param string $userValue
	 *
	 * @return array
	 */
	abstract public function getValuesFromString( $userValue );

	/**
	 * @since 2.5
	 *
	 * @param DIProperty[] $properties
	 *
	 * @return DIProperty[]|null
	 */
	abstract public function setFieldProperties( array $properties );

	/**
	 * @since 2.5
	 *
	 * @return DIProperty[]|null
	 */
	abstract public function getProperties();

	/**
	 * Create a list (array with numeric keys) containing the datavalue
	 * objects that this SMWRecordValue object holds. Values that are not
	 * present are set to null. Note that the first index in the array is
	 * 0, not 1.
	 *
	 * @since 2.5
	 *
	 * @return DataItem[]|null
	 */
	abstract public function getDataItems();

	/**
	 * Return the array (list) of properties that the individual entries of
	 * this datatype consist of.
	 *
	 * @since 2.5
	 *
	 * @return DIProperty[]|null
	 */
	abstract public function getPropertyDataItems();

	/**
	 * @note called by SMWResultArray::loadContent for matching an index as denoted
	 * in |?Foo=Bar|+index=1 OR |?Foo=Bar|+index=Bar
	 *
	 * @see https://www.semantic-mediawiki.org/wiki/Help:Type_Record#Semantic_search
	 *
	 * @since 2.5
	 *
	 * @param string|integer $index
	 *
	 * @return DataItem[]|null
	 */
	public function getDataItemByIndex( $index ) {

		if ( is_numeric( $index ) ) {
			$pos = $index - 1;
			$dataItems = $this->getDataItems();
			return isset( $dataItems[$pos] ) ? $dataItems[$pos] : null;
		}

		if ( ( $property = $this->getPropertyDataItemByIndex( $index ) ) !== null ) {
			$values = $this->getDataItem()->getSemanticData()->getPropertyValues( $property );
			return reset( $values );
		}

		return null;
	}

	/**
	 * @note called by SMWResultArray::getNextDataValue to match an index
	 * that has been denoted using |?Foo=Bar|+index=1 OR |?Foo=Bar|+index=Bar
	 *
	 * @since 2.5
	 *
	 * @param string|integer $index
	 *
	 * @return DIProperty|null
	 */
	public function getPropertyDataItemByIndex( $index ) {

		$properties = $this->getPropertyDataItems();

		if ( is_numeric( $index ) ) {
			$pos = $index - 1;
			return isset( $properties[$pos] ) ? $properties[$pos] : null;
		}

		foreach ( $properties as $property ) {
			if ( $property->getLabel() === $index ) {
				return $property;
			}
		}

		return null;
	}

}
