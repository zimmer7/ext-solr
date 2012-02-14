<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Ingo Renner <ingo@typo3.org>
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
 * Facet renderer factory, creates facet renderers depending on the configured
 * type of a facet.
 *
 * @author Ingo Renner <ingo@typo3.org>
 * @package TYPO3
 * @subpackage solr
 */
class tx_solr_facet_FacetRendererFactory {

	/**
	 * Registration information for facet types.
	 *
	 * @var array
	 */
	protected static $facetTypes = array();

	/**
	 * The default facet render, good for most cases.
	 *
	 * @var string
	 */
	private $defaultFacetRendererClassName = 'tx_solr_facet_SimpleFacetRenderer';

	/**
	 * Facets configuration from plugin.tx_solr.search.faceting.facets
	 *
	 * @var array
	 */
	protected $facetsConfiguration = array();


	/**
	 * Constructor.
	 *
	 * @param array $facetsConfiguration Facets configuration from plugin.tx_solr.search.faceting.facets
	 */
	public function __construct(array $facetsConfiguration) {
		$this->facetsConfiguration = $facetsConfiguration;
	}

	/**
	 * Register a facet type with its helper classes.
	 *
	 * @param string $facetType Facet type that can be used in a TypoScript facet configuration
	 * @param string $rendererClassName Class used to render the facet UI
	 * @param string $filterParserClassName Class used to translate filter parameter from the URL to Lucene filter syntax
	 */
	public static function registerFacetType($facetType, $rendererClassName, $filterParserClassName = '') {
		self::$facetTypes[$facetType] = array(
			'type'         => $facetType,
			'renderer'     => $rendererClassName,
			'filterParser' => $filterParserClassName
		);
	}

	/**
	 * Looks up a facet's configuration and creates a facet renderer accordingly.
	 *
	 * @param string $facetName Facet name
	 * @return tx_solr_FacetRenderer Facet renderer as definied by the facet's configuration
	 */
	public function getFacetRendererByFacetName($facetName) {
		$facetRenderer      = NULL;
		$facetConfiguration = $this->facetsConfiguration[$facetName . '.'];

		$facetRendererClassName = $this->defaultFacetRendererClassName;
		if (isset($facetConfiguration['type'])) {
			$facetRendererClassName = $this->getFacetRendererClassNameByFacetType($facetConfiguration['type']);
		}

		$facetRenderer = t3lib_div::makeInstance($facetRendererClassName, $facetName);
		$this->validateObjectIsFacetRenderer($facetRenderer);

		return $facetRenderer;
	}

	/**
	 * Gets the facet renderer class name for a given facet type.
	 *
	 * @param string $facetType Facet type
	 * @return string Facet renderer class name
	 * @throws InvalidArgumentException
	 */
	protected function getFacetRendererClassNameByFacetType($facetType) {
		if (!array_key_exists($facetType, self::$facetTypes)) {
			throw new InvalidArgumentException(
				'No renderer configured for facet type "' . $facetType .'"',
				1328041286
			);
		}

		return self::$facetTypes[$facetType]['renderer'];
	}

	/**
	 * Validates an object for implementing the tx_solr_FacetRenderer interface.
	 *
	 * @param object $object A potential facet renderer object to check for implementing the tx_solr_FacetRenderer interface
	 * @throws UnexpectedValueException if $object does not implement tx_solr_FacetRenderer
	 */
	protected function validateObjectIsFacetRenderer($object) {
		if (!($object instanceof tx_solr_FacetRenderer)) {
			throw new UnexpectedValueException(
				get_class($object) . ' is not an implementation of tx_solr_FacetRenderer',
				1328038100
			);
		}
	}

	/**
	 * Looks up a facet's configuration and gets an instance of a filter parser
	 * if one is configured.
	 *
	 * @param string $facetName Facet name
	 * @return NULL|tx_solr_QueryFilterParser NULL if no filter parser is configured for the facet's type or an instance of tx_solr_QueryFilterParser otherwise
	 */
	public function getFacetFilterParserByFacetName($facetName) {
		$filterParser       = NULL;
		$facetConfiguration = $this->facetsConfiguration[$facetName . '.'];

		if (isset($facetConfiguration['type'])
		&& !empty(self::$facetTypes[$facetConfiguration['type']]['filterParser'])) {
			$filterParserClassName = self::$facetTypes[$facetConfiguration['type']]['filterParser'];

			$filterParser = t3lib_div::makeInstance($filterParserClassName);
			$this->validateObjectIsQueryFilterParser($filterParser);
		}

		return $filterParser;
	}

	/**
	 * Validates an object for implementing the tx_solr_QueryFilterParser interface.
	 *
	 * @param object $object A potential filter parser object to check for implementing the tx_solr_QueryFilterParser interface
	 * @throws UnexpectedValueException if $object does not implement tx_solr_QueryFilterParser
	 */
	protected function validateObjectIsQueryFilterParser($object) {
		if (!($object instanceof tx_solr_QueryFilterParser)) {
			throw new UnexpectedValueException(
				get_class($object) . ' is not an implementation of tx_solr_QueryFilterParser',
				1328105893
			);
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/solr/classes/facet/class.tx_solr_facet_facetrendererfactory.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/solr/classes/facet/class.tx_solr_facet_facetrendererfactory.php']);
}

?>