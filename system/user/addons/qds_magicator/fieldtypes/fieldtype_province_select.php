<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FF Composer Notify fieldtype parse class
 *
 * @package        ff_composer_notify
 * @author         Mike Wenger @ Q Digital Studio
 */
 


class Fieldtype_province_select {
  
  private $provinces = array();
  
  /**
   * __construct()
   * @param params = array()
   * @return void
  **/
  public function __construct()
  {
    require(PATH_THIRD . 'freeform/language/english/freeform_lang.php');
    
		$provinces 	= array_map(
			'trim',
			preg_split(
				'/[\n\r]+/',
				$lang['list_of_canadian_provinces'],
				-1,
				PREG_SPLIT_NO_EMPTY
			)
		);

		//need matching key => value pairs for the select values to be correct
		//for the output value we are removing the ' (MB)' code for the value and the 'Manitoba' code for the key
		foreach ($provinces as $key => $value)
		{
			$this->provinces[
				preg_replace('/[\w|\s]+\(([a-zA-Z\-_]+)\)$/', "$1", $value)
			] = preg_replace('/\s+\([a-zA-Z\-_]+\)$/', '', $value);
		}
  }

// ----------------------------------------------------------------

  public function parse( $params = array() )
  {
    $val        = $params['val'];
    $delineator = $params['delineator'];

    return $this->provinces[$val];
  }

} //end class