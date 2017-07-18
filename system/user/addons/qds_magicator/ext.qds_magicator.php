<?php

/**
 * QDS Email Magicator class
 *
 * @package        qds_magicator
 * @author         Mike Wenger @ Q Digital Studio
 */

use EllisLab\ExpressionEngine\Library\CP\Table;

class Qds_magicator_ext {

	public  $settings = array();
	private $site_id;
	private $class;
	private $pkg;

	private $hooks = array(
		'freeform_recipient_email',
		'freeform_module_admin_notification',
		'freeform_module_user_notification'
	);
	private $default_settings = array(
		'enabled_notifications'  => array(),
		'template_var_name' => ''
	);
	private $composer_var_name = 'composer_layout_output';


	/**
	 * Constructor
	 *
	 * @param   mixed   Settings array or empty string if none exist.
	 */
	public function __construct($settings = array())
	{
		// set the pkg details
		$this->pkg = 'qds_magicator';

		// Set the info
		$this->info = ee('App')->get($this->pkg);

		// Get site id
		$this->site_id = ee()->config->item('site_id');

		// And version
		$this->version = $this->info->getVersion();

		// Set Class name
		$this->class = ucfirst(get_class($this));

		// Set settings
		$this->settings = $this->_get_site_settings( $this->_get_current_settings() );

		// override with setting if available
		$this->composer_var_name = $this->settings['template_var_name'] ? $this->settings['template_var_name'] : $this->composer_var_name;
	}

	// ----------------------------------------------------------------------

	/**
	 * Activate Extension
	 */
	public function activate_extension()
	{
		foreach ($this->hooks AS $hook)
		{
			$this->_add_hook($hook);
		}
	}

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 */
	function disable_extension()
	{
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}

// ----------------------------------------------------------------------

	/**
	 * Add hook to table
	 *
	 * @access	private
	 * @param	string
	 * @return	void
	 */
	private function _add_hook($hook)
	{
		ee()->db->insert('extensions', array(
			'class'    => $this->class,
			'method'   => $hook,
			'hook'     => $hook,
			'settings' => serialize($this->settings),
			'priority' => 5,
			'version'  => $this->version,
			'enabled'  => 'y'
		));
	}

	// ----------------------------------------------------------------------

	/**
	 * Get settings for this site
	 *
	 * @access      private
	 * @return      mixed
	 */
	private function _get_site_settings($current = array())
	{
		$current = (array) (isset($current[$this->site_id]) ? $current[$this->site_id] : $current);

		return array_merge($this->default_settings, $current);
	}

	// ----------------------------------------------------------------------

	/**
	 * Settings form
	 *
	 * @access      public
	 * @param       array     Current settings
	 * @return      string
	 */
	function settings_form($current)
	{
		// --------------------------------------
		// The base URL for this add-on
		// --------------------------------------
		$base_url = ee('CP/URL', 'addons/settings/'.$this->pkg);

		// --------------------------------------
		// Save when posted
		// --------------------------------------
		if ( ! empty($_POST))
		{
			$this->save_settings($current);

			// Redirect back, so we don't get the send POST vars msg on F5.
			ee()->functions->redirect($base_url);
		}

		// --------------------------------------
		// Get current settings for this site
		// --------------------------------------
		$current = $this->_get_site_settings($current);

		// --------------------------------------
		// Set additional vars
		// --------------------------------------
		$is_freeform_installed = $this->_is_freeform_installed();
		$forms = $table_data =  array();

		// --------------------------------------
		// Set base vars
		// --------------------------------------
		$data = array(
			'is_freeform_installed' => $is_freeform_installed,
			'composer_var_name_fallback' => $this->composer_var_name,
			'notifications' => array(),
			'action' => $base_url,
			'current' => $current
		);

		// --------------------------------------
		// make the query
		// --------------------------------------
		if ($is_freeform_installed)
		{
			// get notifications
			$query = ee()->db->select('fnt.notification_id, fnt.notification_label, fnt.notification_description, ff.form_id, ff.form_label')
        ->from('exp_freeform_notification_templates fnt')
        ->join('exp_freeform_forms ff', 'fnt.notification_id = ff.user_notification_id OR fnt.notification_id = ff.admin_notification_id')
        ->where(
          array(
            'fnt.site_id' => $this->site_id,
            'ff.composer_id !=' => ''
          )
        )
        ->order_by('fnt.notification_id', 'asc')
        ->get()
        ->result();

				// echo '<pre>';
				// print_r($query);
				// exit;

			if (!empty($query))
			{
				// prep array for vars
				foreach ($query AS $row)
				{
					$data['notifications'][$row->notification_id]['notification_label'] = $row->notification_label;
					$data['notifications'][$row->notification_id]['notification_description'] = $row->notification_description;
					$data['notifications'][$row->notification_id]['forms'][$row->form_id] = $row->form_label;
				}

				// build table data
				foreach($data['notifications'] as $id => $notification)
				{
					$table_data[] = array(
						$id,
						$notification['notification_label'],
						$notification['notification_description'],
						'&bull; ' . implode('<br>&bull; ', $notification['forms']),
						// we're just going to do this for now...
						'<input name="enabled_notifications[' . $id . ']" value="y" type="checkbox"' . (isset($current['enabled_notifications'][$id]) ? 'checked="true"' : '') . '>'
						// $this->embed('ee:_shared/form/nested_checkbox', array(
						// 	'field_name' => '1',
						// 	'attrs' => '2',
						// 	'choices' => array(),
						// 	'disabled_choices' => array(),
						// 	'value' => '',
						// ))
					);
					 // isset($current['enabled_notifications'][]
				}
			}
		} // end: $is_freeform_installed

		// --------------------------------------
		// call the table builder
		// --------------------------------------
		$table = ee('CP/Table');
		$table->setColumns(
		  array(
		    'col_label_id',
		    'col_label_label',
		    'col_label_description',
		    'col_label_forms' => array(
					'encode' => false
				),
				'col_label_enabled' => array(
					'encode' => false
		    )
		  )
		);
		$table->setNoResultsText('ff_no_notifications', 'ff_name', ee('CP/URL', 'addons/settings/freeform'));
		$table->setData($table_data);
		$data['table'] = $table->viewData();

		// --------------------------------------
		// mount up!
		// --------------------------------------
		return ee('View')->make($this->pkg . ':settings')->render($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Get current settings from DB
	 *
	 * @access      private
	 * @return      mixed
	 */
	private function _get_current_settings()
	{
		$query = ee()->db->select('settings')
		       ->from('extensions')
		       ->where('class', $this->class)
		       ->limit(1)
		       ->get();

		return @unserialize($query->row('settings'));
	}

	// --------------------------------------------------------------------

	/**
	 * Is Freeform Installed?
	 *
	 * @access      private
	 * @return      bool
	 */
	private function _is_freeform_installed()
	{
		$is_ff_installed = ee('Addon')->get('freeform')->isInstalled();
		return $is_ff_installed;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Freeform version
	 *
	 * @access      private
	 * @return      bool
	 */
	private function _freeform_version()
	{

		$ff_version = ee()->db->select('module_version')
			->from('exp_modules')
			->where('module_name', 'Freeform')
			->get()
			->result_array();

		return $ff_version[0]['module_version'] ? $ff_version[0]['module_version'] : false;
	}

	// --------------------------------------------------------------------

	/**
	 * strpos array
	 *
	 * @access      private
	 * @return      bool
	 */
  private function _strposa($haystack, $needles=array(), $offset=0) {
    $chr = array();
    foreach($needles as $needle) {
            $res = strpos($haystack, $needle, $offset);
            if ($res !== false) $chr[$needle] = $res;
    }
    if(empty($chr)) return false;
    return min($chr);
  }

	// --------------------------------------------------------------------

	/**
	 * str_replace_first
	 *
	 * @access      private
	 * @return      bool
	 */
  function _str_replace_first($search, $replace, $subject) {
      $pos = strpos($subject, $search);
      if ($pos !== false) {
          return substr_replace($subject, $replace, $pos, strlen($search));
      }
      return $subject;
  }

	// --------------------------------------------------------------------

	/**
	 * Parse the email body (Meat & Potatoes)
	 *
	 * @access      private
	 * @return      mixed
	 */
  private function _parse_email_body($fields, $e_id, $vars, $f_id, $obj)
  {
		$output = '';

		$secquery = ee()->db->select('ff.composer_id, ff.form_label, fcl.composer_data, fnt.notification_id')
      ->from('exp_freeform_forms ff')
      ->join('exp_freeform_composer_layouts fcl', 'ff.composer_id = fcl.composer_id')
      ->join('exp_freeform_notification_templates fnt', 'ff.admin_notification_id = fnt.notification_id')
      ->where(
        array(
          'ff.site_id' => $this->site_id,
          'ff.form_id' => $f_id,
          'ff.composer_id !=' => ''
        )
      )
      ->get();

    // should we run the parser? must be a composer form with an enabled notification
    if ($secquery->num_rows() > 0)
    {
      $result          = $secquery->result();
      $notification_id = $result[0]->notification_id;
			$nl							 = "\n";

      // is the notification enabled in the extension?
      if (isset($this->settings['enabled_notifications'][$notification_id]) && $this->settings['enabled_notifications'][$notification_id] == 'y')
      {
        $email_type      = isset($obj->mailtype) ? $obj->mailtype : 'text';
        $nl              = ($email_type == 'html') ? '<br/>' : "\n";
        $fields_data     = array();
        $remove          = array('nonfield_submit');
        $composer_data   = json_decode($result[0]->composer_data);

  			// get field data
  			$fields_query = ee()->db->select('field_id, field_label, field_name, field_type, composer_use') // settings
          ->from('exp_freeform_fields')
          ->where('site_id', $this->site_id)
          ->where_in('field_id', $composer_data->fields)
          ->get()
          ->result();

        // since the entry has already been inserted, we need to query for it
  			$entry = ee()->db->select('*')
          ->from('exp_freeform_form_entries_' . $f_id)
          ->where(
            array(
              'site_id' => $this->site_id,
              'entry_id' => $e_id
            )
          )
          ->get()
          ->result_array();

        // re-write with IDs as keys
        foreach($fields_query as $index=>$field)
        {
          $fields_data[$field->field_id] = $fields_query[$index];
        }

        // create output
        foreach($composer_data->rows as $index=>$row)
        {
          foreach($row as $column)
          {
            foreach($column as $field)
            {
              /*
                composer keys:
                  required
                  type
                  fieldId
                  html

                fieldtypes:
                  text
                  checkbox
                  checkbox_group
                  country_select
                  file_upload
                  hidden
                  mailinglist
                  multiselect
                  province_select
                  radio
                  select
                  state_select
                  textarea
              */
              if (!in_array($field->type, $remove))
              {
                $val = isset($field->fieldId) && isset($entry[0]['form_field_' . $field->fieldId]) ? $entry[0]['form_field_' . $field->fieldId] : false;

                // title
                if ($field->type === 'nonfield_title' && isset($result[0]->form_label))
                {
                  // join to output
                  $output .= ($email_type === 'html' ? '<h2>' : '') . $result[0]->form_label . ($email_type === 'html' ? '</h2> ' : $nl.$nl);
                }
                // html
                elseif ($field->type === 'nonfield_paragraph' && isset($field->html))
                {
                  $html = trim($field->html);
                  $starts_with = ($this->_strposa($html, array('<div>','<p>','<ul>','<h1>','<h2>','<h3>','<h4>','<h5>','<h6>')));

                  // join to output
                  $output .= ($email_type === 'html' && $starts_with ? '' : $nl) . $this->_prep_nonfield_html($html, $email_type, $nl);
                }
                // field
                elseif (isset($field->fieldId) && isset($fields_data[$field->fieldId]) && $val && $val != '')
                {
                  $val_parsed = $val;
                  $fieldtype  = $fields_data[$field->fieldId]->field_type;

            		//set params array
            			$params = array(
              			'val' => $val,
              			'delineator' => '|~|',
              			'glue' => ', ',
              			'newline' => $nl
            			);

            			$class_name = 'Fieldtype_' . $fieldtype;

                  // load fieldtype library if class hasn't already been loaded
                  if (!class_exists($class_name))
                  {
                    require __DIR__.'/fieldtypes/fieldtype_' . $fieldtype . '.php';
                  }

                  // parse value
                  $ft = new $class_name;

                  $val_parsed = $ft->parse($params);

                  // join to output
                  $output .= ($email_type === 'html' ? '<strong>' : '') . $fields_data[$field->fieldId]->field_label . ':' . ($email_type === 'html' ? '</strong> ' : ' ') . $val_parsed . $nl;
                }
              }
            }
          }
        }
				return preg_replace('/(<br\/>|<br>|\n|\r|\n\r|\r\n){3,}/', $nl.$nl , $output);
      }
    }
		// return the clean version
    preg_replace('/' . LD . $this->composer_var_name . RD . '/', '', $vars['message']);
  }

	// --------------------------------------------------------------------

	/**
	 * Prep nonfield HTML
	 *
	 * @access      private
	 * @return      array
	 */
  private function _prep_nonfield_html($html, $email_type, $nl)
  {
    $ret = '';
    if ($email_type === 'html')
    {
      $ret = $this->_str_replace_first('<br>', '', $html) . $nl;
    }
    else
    {
      $ret = strip_tags( preg_replace(array('/\<\/h[1-6]\>/', '/\<h[1-6]\>/'), array($nl, $nl . '*'), $html) ) . $nl . $nl;
    }
    return $ret;
	}

	/**
	 * Modify FF submission entry $vars
	 *
	 * @access      private
	 * @return      array
	 */
  private function _modify_email_vars($fields, $e_id, $vars, $f_id, $obj)
  {
  	/*
  		message
  		subject
  		entry_date
  		attachments
  		attachment_count
  		cc_recipients
  		bcc_recipients
  		reply_to_email
  		reply_to_name
  		from_name
  		from_email

  		$this->wordwrap - (boolean)
  		$this->mailtype - (string) 'html' or 'text'
  		$this->field_inputs - (array) key => value array of field inputs (Alteration to this array does not affect output as template parsing is finished by the time these hooks are run.)
  	*/

		$query = ee()->db->select('ff.composer_id')
      ->from('exp_freeform_forms ff')
      ->where(
        array(
          'ff.site_id' => $this->site_id,
          'ff.form_id' => $f_id
        )
      )
      ->get();

    // should we run the parser? must be a composer form with an enabled notification
    if ($query->num_rows() > 0)
    {
  		// Settings config
  	    $tag_vars = array();
  	    $tag_vars[0][$this->composer_var_name] = $this->_parse_email_body($fields, $e_id, $vars, $f_id, $obj);

  		// parse
  		$vars['message'] = ee()->TMPL->parse_variables($vars['message'], $tag_vars);
    }
    return $vars;
  }

	// --------------------------------------------------------------------

	/**
	 * Save extension settings
	 *
	 * @return      void
	 */
	public function save_settings()
	{
		// Get current settings from DB
		$settings = $this->_get_current_settings();

		if ( ! is_array($settings))
		{
			$settings = array($this->site_id => $this->default_settings);
		}

		// Loop through default settings, check for POST values, fallback to default
		foreach ($this->default_settings AS $key => $val)
		{
			if (($post_val = ee()->input->post($key)) !== FALSE)
			{
				$val = $post_val;
			}

			if (is_array($val))
			{
				$val = array_filter($val);
			}

			$settings[$this->site_id][$key] = $val;
		}

		// Save serialized settings
		ee()->db->where('class', $this->class);
		ee()->db->update('extensions', array('settings' => serialize($settings)));
	}

	// --------------------------------------------------------------------

	/**
	 * freeform_recipient_email
	 *
	 * @return      array
	 */
	public function freeform_recipient_email($fields, $e_id, $vars, $f_id, $obj)
	{
	    //Have other extensions already manipulated?
	    if (ee()->extensions->last_call !== FALSE)
	    {
	        $vars = ee()->extensions->last_call;
	    }

	    return $this->_modify_email_vars($fields, $e_id, $vars, $f_id, $obj);
	}

	/**
	 * freeform_module_admin_notification
	 *
	 * @return      array
	 */
	public function freeform_module_admin_notification($fields, $e_id, $vars, $f_id, $obj)
	{
	    //Have other extensions already manipulated?
	    if (ee()->extensions->last_call !== FALSE)
	    {
	        $vars = ee()->extensions->last_call;
	    }

	    return $this->_modify_email_vars($fields, $e_id, $vars, $f_id, $obj);
	}

	/**
	 * freeform_module_user_notification
	 *
	 * @return      array
	 */
	public function freeform_module_user_notification($fields, $e_id, $vars, $f_id, $obj)
	{
	    //Have other extensions already manipulated?
	    if (ee()->extensions->last_call !== FALSE)
	    {
	        $vars = ee()->extensions->last_call;
	    }

	    return $this->_modify_email_vars($fields, $e_id, $vars, $f_id, $obj);
	}

}

/* End of file ext.qds_magicator.php */
/* Location: /adm/user/addons/qds_magicator/ext.qds_magicator.php */
