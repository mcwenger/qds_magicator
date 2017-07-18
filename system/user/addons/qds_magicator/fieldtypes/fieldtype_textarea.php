<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FF Composer Notify fieldtype parse class
 *
 * @package        ff_composer_notify
 * @author         Mike Wenger @ Q Digital Studio
 */

class Fieldtype_textarea {
  
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
    
    return $val;
  }

} //end class