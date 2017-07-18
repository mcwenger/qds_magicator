<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FF Composer Notify fieldtype parse class
 *
 * @package        ff_composer_notify
 * @author         Mike Wenger @ Q Digital Studio
 */

class Fieldtype_freeform_time {

  /**
   * __construct()
   * @param params = array()
   * @return void
  **/
  public function __construct()
  {
  }

// ----------------------------------------------------------------

  public function parse( $params = array() )
  {
    $val        = $params['val'];
    $delineator = $params['delineator'];

    return $this->timeFormat($val);
  }

  private function timeFormat( $increment = 0, $format = 'g:i a')
	{
		$increment_formatted = gmdate( 'H:i', $increment );
		list( $hour, $minutes ) = explode( ':', $increment_formatted );
		$date = new DateTime( $hour . ':' . $minutes );
		return $date->format( $format );
	}


} //end class
