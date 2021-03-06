<?php
/**
 * ICE API: init registry class file
 *
 * @author Marshall Sorenson <marshall@presscrew.com>
 * @link http://infinity.presscrew.com/
 * @copyright Copyright (C) 2010-2011 Marshall Sorenson
 * @license http://www.gnu.org/licenses/gpl.html GPLv2 or later
 * @package ICE
 * @subpackage init
 * @since 1.0
 */

/**
 * Make maps of initialization data easy
 *
 * @package ICE
 * @subpackage init
 */
abstract class ICE_Init_Registry extends ICE_Map
{
	/**
	 * Key namespace delimeter 
	 */
	const NAMESPACE_DELIM = '.';

	/**
	 * Default values for other registry namespaces
	 *
	 * @var array
	 */
	private $__ns_defaults__ = array();

	/**
	 */
	public function __get( $name )
	{
		if ( $this->has( $name ) ) {
			return $this->get( $name )->value;
		} else {
			return null;
		}
	}

	/**
	 */
	public function __set( $name, $value )
	{
		throw new Exception( 'No magic setting allowed, call the set() method' );
	}

	/**
	 * Create a new instance of ICE_Init_Data for storing in the registry
	 *
	 * @param string $theme Slug of the theme which is setting this data
	 * @param string $name Name for this data (slug format)
	 * @param mixed $value Value for this data
	 * @param boolean $read_only Set to true to disallow modification of the value once set
	 * @return ICE_Init_Data
	 */
	protected function create( $theme, $name, $value = null, $read_only = false )
	{
		return new ICE_Init_Data( $theme, $name, $value, $read_only );
	}
	
	/**
	 * Set a value
	 *
	 * This method supports delayed locking of a value. Set the read only flags to
	 * true at any time to lock the value(s) from further modification.
	 *
	 * @param string $theme
	 * @param string $name
	 * @param mixed $value
	 * @param boolean $ro_value
	 * @param boolean $ro_theme
	 */
	public function set( $theme, $name, $value, $ro_value = false, $ro_theme = false )
	{
		// does this one have a namespace delimeter?
		if ( strpos( $name, self::NAMESPACE_DELIM ) ) {
			// yep, split it
			$parts = explode( self::NAMESPACE_DELIM, $name, 2 );
			// set it in the namespaces defaults array
			$this->__ns_defaults__[ $parts[0] ][ $parts[1] ] = $value;
			// all done
			return true;
		}
		
		// convert arrays to maps
		if ( is_array( $value ) ) {
			$value = new ICE_Map( $value, $ro_value );
		}

		// check for existing map of theme data
		if ( $this->has( $name ) ) {
			// use existing map
			$theme_map = $this->get_map( $name );
		} else {
			// create new map
			$theme_map = new ICE_Map_Lockable();
			// add theme map to registry (myself)
			$this->add( $name, $theme_map );
		}

		// check for existing data for given theme
		if ( $theme_map->contains( $theme ) ) {
			// get data
			$data = $theme_map->item_at( $theme );
			// update value, save result
			$result = $data->set_value( $value );
		} else {
			// create new data object
			$data = $this->create( $theme, $name, $value );
			// set registry
			$data->registry( $this );
			// add to theme map
			$theme_map->add( $theme, $data );
			// new data, always good result
			$result = true;
		}

		// lock data map if applicable
		if ( $ro_value ) {
			$data->lock();
		}

		// lock from any overriding theme data
		if ( $ro_theme ) {
			$theme_map->lock();
		}

		// return result
		return $result;
	}

	/**
	 * Remove a data key from the registry (for all themes)
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function remove( $name )
	{
		return parent::remove( $name );
	}

	/**
	 * Return true if data key is set
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function has( $name )
	{
		// check for theme data
		return $this->contains( $name );
	}

	/**
	 * Get data by name (key)
	 *
	 * @param string $name Name of key to retrieve (slug)
	 * @return ICE_Init_Data
	 */
	public function get( $name )
	{
		// use existing data map
		$theme_map = $this->get_map( $name );

		// get a map?
		if ( $theme_map ) {
			// get theme stack TOP DOWN
			$themes = ICE_Scheme::instance()->theme_stack( true );
			// did we get a stack?
			if ( is_array( $themes ) && count( $themes ) ) {
				// check for data according to theme stack
				foreach ( $themes as $theme ) {
					// does theme have this data key set?
					if ( $theme_map->contains( $theme ) ) {
						// yes, return it
						return $theme_map->item_at($theme);
					}
				}
			}
		}

		// key not set
		return null;
	}

	/**
	 * Get a data key's entire themes map
	 *
	 * @param string $name
	 * @return ICE_Map|null
	 */
	public function get_map( $name )
	{
		if ( $this->has( $name ) ) {
			return $this->item_at( $name );
		}

		// key not set
		return null;
	}

	/**
	 * Return all data items as an array
	 *
	 * @return array
	 */
	public function get_all()
	{
		return $this->to_array();
	}

	/**
	 * Get defaults for a specific namespace
	 *
	 * @param string $ns
	 * @return array
	 */
	final public function get_ns_defaults( $ns )
	{
		if ( isset( $this->__ns_defaults__[ $ns ] ) ) {
			return $this->__ns_defaults__[ $ns ];
		}

		return array();
	}


	/**
	 * Lock registry from further addition/removal of data
	 */
	public function lock()
	{
		$this->set_read_only( true );
	}

	/**
	 * Return lock state
	 *
	 * @return boolean
	 */
	public function locked()
	{
		return ( $this->get_read_only() === true );
	}
}

?>
