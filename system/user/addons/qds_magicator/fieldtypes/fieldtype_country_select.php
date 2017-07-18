<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FF Composer Notify fieldtype parse class
 *
 * @package        ff_composer_notify
 * @author         Mike Wenger @ Q Digital Studio
 */

class Fieldtype_country_select {
  
  /**
   * __construct()
   * @param params = array()
   * @return void
  **/
  public function __construct()
  {
    $this->countries = $this->_get_countries();
  }

// ----------------------------------------------------------------

  public function parse( $params = array() )
  {
    $val        = $params['val'];
    $delineator = $params['delineator'];

    return (array_key_exists($val, $this->countries) ? $this->countries[$val] : $val);
  }
  
	// --------------------------------------------------------------------

	/**
	 * Get countries
	 *
	 * @access	public
	 * @return	mixed
	 */

	public function _get_countries()
	{
		$output         = array();
		$countries_file = APPPATH . 'config/countries.php';

		if (is_file($countries_file))
		{
			include $countries_file;

			if ( ! empty( $countries ) )
			{
				$output = $countries;
			}
		}
		return $output;
	}
  
  
  

} //end class