<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FF Composer Notify fieldtype parse class
 *
 * @package        ff_composer_notify
 * @author         Mike Wenger @ Q Digital Studio
 */

class Fieldtype_checkbox_group {
  
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
    $val         = $params['val'];
    $delineator  = $params['delineator'];
    $glue        = $params['glue'];
    $return_data = array();
    
    $opts = explode("\n", $val);
    
    foreach($opts as $opt)
    {
      if (preg_match($delineator, $opt))
      {
        $val_arr = explode($delineator, $opt);
        
        $return_data[] = $val_arr[0];
      }
    }
    
    return (is_array($return_data) ? implode($return_data, $glue) : $val);
  }

} //end class