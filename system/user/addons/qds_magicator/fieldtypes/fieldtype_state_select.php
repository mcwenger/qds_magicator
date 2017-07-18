<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FF Composer Notify fieldtype parse class
 *
 * @package        ff_composer_notify
 * @author         Mike Wenger @ Q Digital Studio
 */
 


class Fieldtype_state_select {
  
  private $states = array();
  
  /**
   * __construct()
   * @param params = array()
   * @return void
  **/
  public function __construct()
  {
    require(PATH_THIRD . 'freeform/language/english/freeform_lang.php');
		
		$states 	= array_map(
			'trim',
			preg_split(
				'/[\n\r]+/',
				$lang['list_of_us_states'],
				-1,
				PREG_SPLIT_NO_EMPTY
			)
		);

		//need matching key => value pairs for the select values to be correct
		//for the output value we are removing the ' (AZ)' code for the value and the 'Arizona' code for the key
		foreach ($states as $key => $value)
		{
			$this->states[
				preg_replace('/[\w|\s]+\(([a-zA-Z\-_]+)\)$/', "$1", $value)
			] = preg_replace('/\s+\([a-zA-Z\-_]+\)$/', '', $value);
		}
  }

// ----------------------------------------------------------------

  public function parse( $params = array() )
  {
    $val        = $params['val'];
    $delineator = $params['delineator'];
    
    return $this->states[$val];
  }

} //end class