<?php
/**
 * PIE API: features registry
 *
 * @author Marshall Sorenson <marshall.sorenson@gmail.com>
 * @link http://marshallsorenson.com/
 * @copyright Copyright (C) 2010 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package PIE
 * @subpackage features
 * @since 1.0
 */

Pie_Easy_Loader::load( 'base/registry', 'features/factory' );

/**
 * Make keeping track of features easy
 *
 * @package PIE
 * @subpackage features
 */
abstract class Pie_Easy_Features_Registry extends Pie_Easy_Registry
{
	/**
	 * Init ajax requirements
	 */
	public function init_ajax()
	{
		// call parent
		parent::init_ajax();

		// init ajax for each registered feature
		foreach ( $this->get_all() as $feature ) {
			$feature->init_ajax();
		}
	}

	/**
	 * Init screen dependencies for all applicable features to be rendered
	 */
	public function init_screen()
	{
		// call parent
		parent::init_screen();

		// init screen for each registered feature
		foreach ( $this->get_all() as $feature ) {
			$feature->init_screen();
		}
	}

	/**
	 * Enqueue required styles
	 */
	public function init_styles()
	{
		// call parent
		parent::init_styles();

		// init styles for each registered feature
		foreach ( $this->get_all() as $feature ) {
			$feature->init_styles();
		}
	}

	/**
	 * Enqueue required scripts
	 */
	public function init_scripts()
	{
		// call parent
		parent::init_scripts();

		// init scripts for each registered feature
		foreach ( $this->get_all() as $feature ) {
			$feature->init_scripts();
		}
	}
}

?>
