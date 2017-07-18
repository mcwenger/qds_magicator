<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FF Composer Notify fieldtype parse class
 *
 * @package        ff_composer_notify
 * @author         Mike Wenger @ Q Digital Studio
 */

class Fieldtype_file_upload {
  
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
    $newline     = $params['newline'];
    
    $files = explode("\n", $val);
    
    if (count($files) > 1)
    {
      $return = '';
      
      foreach($files as $file)
      {
        $return .= $newline . $file;
      }
    }
    else
    {
      $return = $files[0];
    }
    
    return ($return);
  }

} //end class