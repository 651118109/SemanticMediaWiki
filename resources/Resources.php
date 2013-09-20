<?php

namespace SMW;

/**
 * Collection of resource module definitions
 *
 * @since 1.8
 *
 * @file
 * @ingroup SMW
 *
 * @licence GNU GPL v2 or later
 * @author mwjames
 */

global $smwgIP, $smwgScriptPath;

$moduleTemplate = array(
	'localBasePath' => $smwgIP ,
	'remoteBasePath' => $smwgScriptPath,
	'group' => 'ext.smw'
);

return array(
	// SMW core class
	'ext.smw' => $moduleTemplate + array(
		'scripts' => 'resources/smw/ext.smw.js',
		'dependencies' => 'jquery.async',
	),

	// Common styles independent from JavaScript
	// MW 1.22 loading this as 'dependencies' => 'ext.smw.tooltip.styles'
	// was not a choice as it showed flashy hiccups
	'ext.smw.style' => $moduleTemplate + array(
		'styles' => array(
			'resources/smw/util/ext.smw.util.tooltip.css',
			'resources/smw/ext.smw.css'
		),
		'position' => 'top'
	),

	// jStorage was added in MW 1.20
	'ext.jquery.jStorage' => $moduleTemplate + array(
		'scripts' => 'resources/jquery/jquery.jstorage.js',
		'dependencies' => 'jquery.json',
	),

	// md5 hash key generator
	'ext.jquery.md5' => $moduleTemplate + array(
		'scripts' => 'resources/jquery/jquery.md5.js'
	),

	// dataItem representation
	'ext.smw.dataItem' => $moduleTemplate + array(
		'scripts' => array(
			'resources/smw/data/ext.smw.dataItem.wikiPage.js',
			'resources/smw/data/ext.smw.dataItem.uri.js',
			'resources/smw/data/ext.smw.dataItem.time.js',
			'resources/smw/data/ext.smw.dataItem.property.js',
			'resources/smw/data/ext.smw.dataItem.unknown.js',
			'resources/smw/data/ext.smw.dataItem.number.js',
			'resources/smw/data/ext.smw.dataItem.text.js',
		),
		'dependencies' => array(
			'ext.smw',
			'mediawiki.Title',
			'mediawiki.Uri'
		)
	),

	// dataValue representation
	'ext.smw.dataValue' => $moduleTemplate + array(
		'scripts' => array(
			'resources/smw/data/ext.smw.dataValue.quantity.js',
		),
		'dependencies' => 'ext.smw.dataItem'
	),

	// dataItem representation
	'ext.smw.data' => $moduleTemplate + array(
		'scripts' => 'resources/smw/data/ext.smw.data.js',
		'dependencies' => array(
			'ext.smw.dataItem',
			'ext.smw.dataValue'
		)
	),

	// Query
	'ext.smw.query' => $moduleTemplate + array(
		'scripts' => 'resources/smw/query/ext.smw.query.js',
		'dependencies' => array(
			'ext.smw',
			'mediawiki.util'
		)
	),

	// API
	'ext.smw.api' => $moduleTemplate + array(
		'scripts' => 'resources/smw/api/ext.smw.api.js',
		'dependencies' => array(
			'ext.smw.data',
			'ext.smw.query',
			'ext.jquery.jStorage',
			'ext.jquery.md5'
		)
	),

	// Tooltip qtip2 resources
	'ext.jquery.qtip' => $moduleTemplate + array(
		'scripts' => 'resources/jquery/jquery.qtip.js',
		'styles' => 'resources/jquery/jquery.qtip.css',
	),

	// Tooltip
	'ext.smw.tooltip.styles' => $moduleTemplate + array(
		'styles' => 'resources/smw/util/ext.smw.util.tooltip.css',
	),

	// Tooltip
	'ext.smw.tooltip' => $moduleTemplate + array(
		'scripts' => 'resources/smw/util/ext.smw.util.tooltip.js',
		'dependencies' => array(
			'ext.smw.tooltip.styles',
			'ext.smw',
			'ext.jquery.qtip'
		),
		'messages' => array(
			'smw-ui-tooltip-title-property',
			'smw-ui-tooltip-title-quantity',
			'smw-ui-tooltip-title-info',
			'smw-ui-tooltip-title-service',
			'smw-ui-tooltip-title-warning',
			'smw-ui-tooltip-title-parameter',
			'smw-ui-tooltip-title-event',
		)
	),
	// Resource is loaded at the top otherwise the stylesheet will only
	// become active after all content is loaded with icons appearing with a
	// delay due to missing stylesheet definitions at the time of the display
	'ext.smw.tooltips' => $moduleTemplate + array(
		'dependencies' => array(
			'ext.smw.style',
			'ext.smw.tooltip'
		),
		'position' => 'top'
	),
	// Autocomplete resources
	'ext.smw.autocomplete' => $moduleTemplate + array(
		'scripts' => 'resources/smw/util/ext.smw.util.autocomplete.js',
		'dependencies' => 'jquery.ui.autocomplete'
	),
	// Special:Ask
	'ext.smw.ask' => $moduleTemplate + array(
		'scripts' => 'resources/smw/special/ext.smw.special.ask.js',
		'styles' => 'resources/smw/special/ext.smw.special.ask.css',
		'dependencies' => array(
			'ext.smw.tooltip',
			'ext.smw.style',
			'ext.smw.autocomplete'
		),
		'messages' => array(
			'smw-ask-delete',
			'smw-ask-format-selection-help'
		),
		'position' => 'top'
	),
	// Facts and browse
	'ext.smw.browse' => $moduleTemplate + array(
		'scripts' => 'resources/smw/special/ext.smw.special.browse.js',
		'dependencies' => array(
			'ext.smw.style',
			'ext.smw.autocomplete'
		),
		'position' => 'top'
	),
	// Special:SearchByProperty
	'ext.smw.property' => $moduleTemplate + array(
		'scripts' => 'resources/smw/special/ext.smw.special.property.js',
		'dependencies' => 'ext.smw.autocomplete'
	)
);
