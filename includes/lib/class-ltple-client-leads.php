<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Leads {
	
	var $parent;
	var $slug;

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		$this->slug 	= 'lead';
		
		if( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == $this->slug ){
			
			add_filter( $this->slug . '_custom_fields', array( $this, 'get_fields'));

			add_filter('manage_' . $this->slug . '_posts_columns', array( $this, 'set_columns'));
			add_action('manage_' . $this->slug . '_posts_custom_column', array( $this, 'add_column_content'), 10, 2);		
						
			add_action('admin_head', array($this, 'add_table_css'));
		}
	}

	public function get_fields(){
			
		$fields=[];
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'=>"lead_info"),
			'id'=>"leadAppId",
			'label'=>"From App Id",
			'type'=>'number',
			'placeholder'=>"",
			'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'=>"lead_info"),
			'id'=>"leadTwtName",
			'label'=>"Twitter Screen Name",
			'type'=>'text',
			'placeholder'=>"",
			'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'=>"lead_info"),
			'id'=>"leadNicename",
			'label'=>"Nicename",
			'type'=>'text',
			'placeholder'=>"",
			'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'=>"lead_info"),
			'id'=>"leadPicture",
			'label'=>"Image url",
			'type'=>'text',
			'placeholder'=>"",
			'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'=>"lead_info"),
			'id'=>"leadEmail",
			'label'=>"Email Contact",
			'type'=>'text',
			'placeholder'=>"",
			'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'=>"lead_info"),
			'id'=>"leadTwtFollowers",
			'label'=>"Twitter Followers",
			'type'=>'number',
			'placeholder'=>"",
			'description'=>''
		);	
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'=>"lead_info"),
			'id'=>"leadDescription",
			'label'=>"Description",
			'type'=>'textarea',
			'placeholder'=>"",
			'description'=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'=>"lead_info"),
			'id'=>"leadUrls",
			'name'=>"leadUrls",
			'label'=>"Lead Urls",
			'type'=>'key_value',
			'placeholder'=>"",
			'description'=>''
		);	
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'=>"lead_info"),
			'id'=>"leadCalls",
			'label'=>"List of Calls",
			'type'=>'textarea',
			'placeholder'=>"",
			'description'=>''
		);
	
		return $fields;
	}
	
	public function set_columns($columns){
		
		// Remove description, posts, wpseo columns
		$columns = [];
		
		$columns['cb'] 				= '<input type="checkbox" />';
		$columns['leadPicture'] 	= 'Picture';
		$columns['title'] 			= 'Title';
		$columns['leadEmail'] 		= 'Email';
		$columns['leadTwtFollowers']= 'Followers';
		$columns['leadDescription']	= 'Description';
		$columns['author'] 			= 'Author';
		$columns['date'] 			= 'Date';
		
		return $columns;
	}
	
	public function add_table_css() {
		
		echo '<style>';		

			echo '.column-leadPicture  		{width: 6%}';
			echo '.column-leadTwtFollowers  {width: 10%}';
			
		echo '</style>';
	}
	
	public function add_column_content($column_name, $post_id){

		$meta = get_post_meta($post_id);
	
		if($column_name === 'leadPicture') {

			if( !empty($meta['leadPicture'][0]) ){
				
				echo '<img src="'.$meta['leadPicture'][0].'" height="50" width="50" />';
			}
		}
		elseif($column_name === 'leadEmail') {

			if( !empty($meta['leadEmail'][0]) ){
				
				echo $meta['leadEmail'][0];
			}
		}
		elseif($column_name === 'leadTwtFollowers') {

			if( !empty($meta['leadTwtFollowers'][0]) ){
				
				echo $meta['leadTwtFollowers'][0];
			}
		}
		elseif($column_name === 'leadDescription') {

			if( !empty($meta['leadDescription'][0]) ){
				
				echo $meta['leadDescription'][0];
			}
		}		
	}
	
	/**
	 * Main LTPLE_Client_Leads Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Leads is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Leads instance
	 */
	public static function instance ( $parent ) {
		
		if ( is_null( self::$_instance ) ) {
			
			self::$_instance = new self( $parent );
		}
		
		return self::$_instance;
		
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()	
} 