<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Leads {
	
	var $parent;
	var $slug;
	var $leads;

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		$this->slug 	= 'lead';
		
		add_action ('init', array($this,'leads_init'));
		
		if( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == $this->slug ){
			
			add_filter( $this->slug . '_custom_fields', array( $this, 'get_fields'));

			add_filter('manage_' . $this->slug . '_posts_columns', array( $this, 'set_columns'));
			add_action('manage_' . $this->slug . '_posts_custom_column', array( $this, 'add_column_content'), 10, 2);		
						
			add_action('admin_head', array($this, 'add_table_css'));
			
			add_action('admin_head', array($this, 'update_manually'));
		}
	}
	
	public function leads_init(){

	   if( isset($_REQUEST['app']) &&  !is_admin() ){

		   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		   require_once( ABSPATH . 'wp-admin/includes/screen.php' );
		   require_once( ABSPATH . 'wp-admin/includes/class-wp-screen.php' );
		   require_once( ABSPATH . 'wp-admin/includes/template.php' );
	   }
	}
	
	public function get_fields($fields=[]){
			
		$fields[]=array(
		
			"metabox" =>
			
			array('name'	=>"lead_info"),
			'id'			=>"leadAppId",
			'label'			=>"From App Id",
			'type'			=>'number',
			'placeholder'	=>"",
			'description'	=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'	=>"lead_info"),
			'id'			=>"leadTwtName",
			'label'			=>"Twitter Screen Name",
			'type'			=>'text',
			'placeholder'	=>"",
			'description'	=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'	=>"lead_info"),
			'id'			=>"leadNicename",
			'label'			=>"Nicename",
			'type'			=>'text',
			'placeholder'	=>"",
			'description'	=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'		=>"lead_info"),
			'id'				=>"leadPicture",
			'label'				=>"Image url",
			'type'				=>'text',
			'placeholder'		=>"",
			'description'		=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'		=>"lead_info"),
			'id'				=>"leadEmail",
			'label'				=>"Email Contact",
			'type'				=>'text',
			'placeholder'		=>"",
			'description'		=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'	=>"lead_info"),
			'id'			=>"leadCanSpam",
			'label'			=>"Can Spam",
			'type'			=>'text',
			'placeholder'	=>'true',
			'default'		=>'true',
			'description'	=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'	=>"lead_info"),
			'id'			=>"leadTwtProtected",
			'label'			=>"Can DM",
			'type'			=>'text',
			'placeholder'	=>'true',
			'default'		=>'true',
			'description'	=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'	=>"lead_info"),
			'id'			=>"leadTwtFollowers",
			'label'			=>"Twitter Followers",
			'type'			=>'number',
			'placeholder'	=>"",
			'description'	=>''
		);	
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'	=>"lead_info"),
			'id'			=>"leadDescription",
			'label'			=>"Description",
			'type'			=>'textarea',
			'placeholder'	=>"",
			'description'	=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'	=>"lead_info"),
			'id'			=>"leadUrls",
			'name'			=>"leadUrls",
			'label'			=>"Lead Urls",
			'type'			=>'key_value',
			'placeholder'	=>"",
			'description'	=>''
		);
		
		$fields[]=array(
		
			"metabox" =>
			
			array('name'	=>"lead_info"),
			'id'			=>"leadCalls",
			'label'			=>"List of Calls",
			'type'			=>'textarea',
			'placeholder'	=>"",
			'description'	=>''
		);
	
		return $fields;
	}
	
	public function list_leads( $user_id, $num=1000, $offset=0 ){
		
		$leads = [];
		
		if(is_numeric($user_id)){
		
			// get args
			
			$args = array(
			
				'post_type'   	=> $this->slug,
				'post_status' 	=> 'publish',
				'numberposts' 	=> $num,
				'offset'		=> $offset,
				'meta_key' 		=> 'leadTwtFollowers',
				'orderby' 		=> 'meta_value_num',
				'order' 		=> 'DESC',
				'meta_query' 	=> array(
					'relation' => 'OR',
					array(
						'key' 		=> 'leadCanSpam',
						'value' 	=> 'false',
						'compare' 	=> '!=',
					),
					array(
						'key' 		=> 'leadCanSpam',
						'compare' 	=> 'NOT EXISTS',
					)
				)
			);		
			
			if( $user_id > -1 ){
				
				$args['author'] = $user_id;
			}

			$q = get_posts( $args );
			
			if(!empty($q)){
				
				foreach($q as $lead){
					
					$meta = get_post_meta($lead->ID);
					
					if(!empty($meta)){
						
						$item = new stdClass();
						
						foreach($meta as $key => $value){
							
							if( ( !isset($lead->leadCanSpam) || $lead->leadCanSpam === 'true' )){
								
								$item->id 			= intval($lead->ID);
								$item->via 			= intval($lead->post_author);
								$item->htmlImg 		= ( !empty($lead->leadPicture) ? '<img src="' . $lead->leadPicture . '" height="50" width="50" />' : '' );
								$item->htmlTwtName 	= ( !empty($lead->leadTwtName) ? '<a href="http://twitter.com/' . $lead->leadTwtName . '" target="_blank">' . ( !empty($lead->leadNicename) ? $lead->leadNicename : $lead->leadTwtName ) . '</a>' : ( !empty($lead->leadNicename) ? $lead->leadNicename : '' ) );
							
								if( strpos($key,'_') !== 0 ){
									
									if( is_numeric($value[0]) ){
										
										$item->{$key} = floatval($value[0]);
									}
									else{
										
										$item->{$key} = $value[0];
									}
								}
								
								
							}
						}
						
						if(isset($item->id)){
						
							$leads[] = $item;
						}
					}
				}
			}
		}

		return $leads;
	}
	
	public function destroy_leads( $user_id ){
		
		if( is_numeric($user_id) && !empty($_POST['rows']) ){
		
			foreach( $_POST['rows'] as $lead ){
				
				if(!empty($lead['id'])){
					
					update_post_meta( $lead['id'], 'leadCanSpam', 'false' );
				}
			}
		}
		
		return $this->list_leads( $user_id );
	}	
	
	public function set_columns($columns){
		
		// Remove description, posts, wpseo columns
		$columns = [];
		
		$columns['cb'] 				= '<input type="checkbox" />';
		$columns['leadPicture'] 	= 'Picture';
		$columns['title'] 			= 'Title';
		$columns['author'] 			= 'Via';
		$columns['leadTwtFollowers']= 'Followers';
		$columns['leadDescription']	= 'Description';
		$columns['leadTwtProtected']= 'Protect';
		$columns['leadCanSpam']		= 'Spam';
		$columns['date'] 			= 'Date';
		
		if( $this->user->is_admin ){
			
			$columns['leadEmail'] 	= 'Email';
		}		
		
		return $columns;
	}
	
	public function add_table_css() {
		
		echo '<style>';		

			echo '.column-leadPicture  		{width: 6%}';
			echo '.column-leadTwtFollowers  {width: 10%}';
			echo '.column-leadTwtProtected  {width: 6%}';
			echo '.column-leadCanSpam  		{width: 5%}';
			
		echo '</style>';
	}
	
	public function add_column_content($column_name, $post_id){
		
		if(empty($this->leads[$post_id])){
			
			$this->leads[$post_id] = get_post_meta($post_id);
		}

		$search_terms = ( !empty($_REQUEST['s']) ? $_REQUEST['s'] : '' );
	
		if($column_name === 'leadPicture') {

			if( !empty($this->leads[$post_id]['leadPicture'][0]) ){
				
				echo '<img src="'.$this->leads[$post_id]['leadPicture'][0].'" height="50" width="50" />';
			}
		}
		elseif($column_name === 'leadEmail') {

			if( !empty($this->leads[$post_id]['leadEmail'][0]) ){
				
				echo $this->leads[$post_id]['leadEmail'][0];
			}
		}
		elseif($column_name === 'leadTwtFollowers') {

			if( !empty($this->leads[$post_id]['leadTwtFollowers'][0]) ){
				
				echo $this->leads[$post_id]['leadTwtFollowers'][0];
			}
		}
		elseif($column_name === 'leadDescription') {

			if( !empty($this->leads[$post_id]['leadDescription'][0]) ){
				
				echo $this->leads[$post_id]['leadDescription'][0];
			}
		}
		elseif ($column_name == 'leadCanSpam') {
			
			echo '<span>';
				
				if( !empty($this->leads[$post_id]['leadCanSpam'][0]) && $this->leads[$post_id]['leadCanSpam'][0]==='false'){
					
					$text = "<img src='" . $this->parent->assets_url . "/images/wrong_arrow.png' width=25 height=25>";
					echo "<a title=\"Subscribe to mailing lists\" href=\"" . add_query_arg(array("post_id" => $post_id, "wp_nonce" => wp_create_nonce( 'leadCanSpam' ), "post_type" => $this->slug, 'leadCanSpam' => "true", "s" => $search_terms ), get_admin_url() . "edit.php") . "\">" . apply_filters("ltple_manual_lead_can_spam", $text) . "</a>";
				}
				else{
					
					$text = "<img src='" . $this->parent->assets_url . "/images/right_arrow.png' width=25 height=25>";
					echo "<a title=\"Unsubscribe from mailing lists\" href=\"" . add_query_arg(array("post_id" => $post_id, "wp_nonce" => wp_create_nonce( 'leadCanSpam' ), "post_type" => $this->slug, 'leadCanSpam' => "false", "s" => $search_terms ), get_admin_url() . "edit.php") . "\">" . apply_filters("ltple_manual_lead_can_spam", $text) . "</a>";
				}
				
			
			echo '</span>';
		}
		elseif($column_name === 'leadTwtProtected') {

			if( !empty($this->leads[$post_id]['leadTwtProtected'][0]) ){
				
				echo $this->leads[$post_id]['leadTwtProtected'][0];
			}
		}		
	}

	public function get_users_orderby_leads($order = 'DESC', $num = -1){
		
		$users = [];
		
		// get customers only
		
		$q = new WP_Query(array(
		
			'posts_per_page'=> -1,
			'post_type'		=> 'user-plan',
			'fields' 		=> 'post_author',
			'meta_query'	=> array(
				array(
					'key'		=> 'userPlanValue',
					'value'		=> 0,
					'type'		=> 'NUMERIC',
					'compare'	=> '>'
				)
			)
		));

		if(!empty($q->posts)){
			
			$ids = [];
			
			foreach($q->posts as $post){
				
				$ids[] = $post->post_author;
			}
			
			add_action('pre_user_query', array($this, 'user_query_count_leads'));
			
			$args = array(
			
				'orderby'      => 'post_count',
				'order'        => $order,
				'count_total'  => false,
				'number'       => $num,
				'include'      => $ids,
			);
			
			$users = get_users($args);
			
			remove_action('pre_user_query', array($this, 'user_query_count_leads'));
		}
		
		return $users;
	}

	public function user_query_count_leads($args){
	
		$args->query_from = str_replace("post_type = 'post' AND", "post_type IN ('lead') AND", $args->query_from);	
	}
	
	public function update_manually() {
		
		if(isset($_REQUEST["wp_nonce"]) && wp_verify_nonce($_REQUEST["wp_nonce"], 'leadCanSpam') && isset($_REQUEST['leadCanSpam'])) {
			
			if($_REQUEST['leadCanSpam'] === 'true' || $_REQUEST['leadCanSpam'] === 'false'){

				update_post_meta($_REQUEST["post_id"], 'leadCanSpam', $_REQUEST['leadCanSpam']);
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