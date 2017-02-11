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
		
		add_filter( $this->slug . '_custom_fields', array( $this, 'get_fields'));
		
		if( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == $this->slug ){
			
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
			'id'			=>"leadTwtProtected",
			'label'			=>"Twitter Protected",
			'disabled'		=>true,
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
			'id'			=>"leadTwtLastDm",
			'label'			=>"Twitter last DM",
			'type'			=>'text',
			//'disabled'		=>true,
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
	
	public function get_fields_frontend( $check=true, $opportunity=false ){
		
		$fields=[];
		
		if( $check ){
			
			$fields[]=array(
				
				'field' 	=> 'state',
				'checkbox' 	=> 'true',
			
			);
		}
		
		$fields[]=array(
			
			'field' 	=> 'htmlImg',
			'sortable' 	=> 'false',
		
		);
					
		$fields[]=array(
			
			'field' 	=> 'htmlTwtName',
			'sortable' 	=> 'true',
			'content' 	=> 'Name',
		
		);

		$fields[]=array(
			
			'field' 	=> 'leadTwtFollowers',
			'sortable' 	=> 'true',
			'content' 	=> 'Followers',
		
		);			

		if( $this->parent->user->is_admin && !$opportunity ){
			
			$fields[]=array(
				
				'field' 	=> 'leadEmail',
				'sortable' 	=> 'true',
				'content' 	=> 'Email <span class="label label-warning pull-right"> admin </span>',
			);
		}

		$fields[]=array(
			
			'field' 	=> 'leadDescription',
			'sortable' 	=> 'true',
			'content' 	=> 'Description',
		
		);
		
		if($opportunity){

			$fields[]=array(
				
				'field' 	=> 'htmlStars',
				'sortable' 	=> 'false',
				'content' 	=> 'Stars',
			
			);
		
			$fields[]=array(
				
				'field' 	=> 'htmlForm',
				'sortable' 	=> 'false',
				'content' 	=> 'Form',
			
			);
		}
		
		return $fields;
	}
	
	public function list_leads( $user_id, $opportunity='', $app='' ){
		
		$leads 	= [];
		$apps 	= [];
		
		if( empty($app) && !empty($_REQUEST['app'])){
			
			$app = $_REQUEST['app'];
		}
		
		if( empty($opportunity) && !empty($_REQUEST['opportunity'])){
			
			$opportunity = $_REQUEST['opportunity'];
		}

		if(!empty($opportunity)){
			
			//$user_id 	= 1;
			$user_id 	= $this->parent->user->ID;
			$num 		= 5;
			$offset 	= 0;
		}
		else{
			
			$num 		= 1000;
			$offset 	= 0;
			
			if(!is_numeric($user_id)){
				
				$user_id = $this->parent->user->ID;
			}			
			
			if(!empty($_GET['num'])){
				
				$num = floatval($_GET['num']);
			}
			
			if(!empty($_GET['offset'])){
				
				$offset = intval($_GET['offset']);
			}
		}
		
		if(is_numeric($user_id)){
		
			// get args
			
			$args = array(
			
				'post_type'   	=> $this->slug,
				'post_status' 	=> 'publish',
				'numberposts' 	=> $num,
				'offset'		=> $offset,
				'meta_key' 		=> 'leadTwtFollowers',
				'orderby' 		=> 'meta_value_num',
				'order' 		=> 'DESC'
			);

			if(!empty($opportunity)){

				$args['meta_query'] = array(
				
					'relation' => 'AND',
					array(
					
						'relation' => 'OR',
						array(
							'key' 		=> 'leadTwtLastDm',
							'value' 	=> '',
							'compare' 	=> '=',
						),
						array(
							'key' 		=> 'leadTwtLastDm',
							'compare' 	=> 'NOT EXISTS',
						)
					),				
					array(
					
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
			}
			else{
				
				$args['meta_query'] = array(
				
					'relation' => 'AND',				
					array(
					
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
			}	
			
			if( $user_id > -1 ){
				
				if( $this->parent->user->is_admin && $user_id == $this->parent->user->ID ){
					
					$args['author'] = implode(',',get_users('role=administrator&fields=id'));
				}
				else{
					
					$args['author'] = $user_id;
				}
			}
			else{
				
				// exclude current user followers from suggestions
				
				//$args['author'] = '-'.$this->parent->user->ID;
			}

			$q = get_posts( $args );

			if(!empty($q)){
				
				foreach($q as $lead){
					
					$item = new stdClass();

					$meta = get_post_meta($lead->ID);
					
					if(!empty($meta)){
						
						$item->id 			= intval($lead->ID);
						$item->htmlImg 		= ( !empty($lead->leadPicture) ? '<img src="' . $lead->leadPicture . '" height="50" width="50" style="width:50px;min-width:50px;max-width:50px;height:50px;" />' : '' );
						$item->htmlTwtName 	= ( !empty($lead->leadTwtName) ? '<a href="http://twitter.com/' . $lead->leadTwtName . '" target="_blank">' . ( !empty($lead->leadNicename) ? $lead->leadNicename : $lead->leadTwtName ) . '</a>' : ( !empty($lead->leadNicename) ? $lead->leadNicename : '' ) );
						
						if(!empty($opportunity)){
							
							$leadAppId = $meta['leadAppId'][0];
							
							if(!isset($apps[$leadAppId])){
								
								// get lead app
								
								$apps[$leadAppId] = json_decode(get_post_meta( $leadAppId, 'appData', true ),false);
							
								// check lead app

								$apps[$leadAppId]->refresh = ( $this->parent->apps->{$app}->is_valid_token($apps[$leadAppId]) ? false : true );
							}
							
							if(!empty($apps[$leadAppId]->screen_name)){
							
								$item->via = $apps[$leadAppId]->screen_name;
								
								$item->htmlStars 	= '';
								$item->htmlForm 	= '';
								
								if($opportunity == 'dms' ){
									
									//stars
									
									$item->htmlStars .= '<span class="badge">+'.get_option($this->parent->_base . 'ltple_twitter_dm_sent_stars').' <span class="glyphicon glyphicon-star" aria-hidden="true"></span></span> ';
									
									//form
									
									if($apps[$leadAppId]->refresh){
										
										$item->htmlForm 	.= 'Reconnect @'.$apps[$leadAppId]->screen_name . '...';
									}
									else{
									
										$item->htmlForm 	.= '<form action="'.$this->parent->api->get_url('leads/engage').'" method="post">';
											
											$item->htmlForm 	.= '<input type="hidden" name="app" value="twitter" />';
											$item->htmlForm 	.= '<input type="hidden" name="action" value="appSendDm" />';
											$item->htmlForm 	.= '<input type="hidden" name="opportunity" value="dms" />';
											$item->htmlForm 	.= '<input type="hidden" name="appId" value="'.$leadAppId.'" />';
											$item->htmlForm 	.= '<input type="hidden" name="leadAppId" value="'.$lead->ID.'" />';
											$item->htmlForm 	.= '<input type="hidden" name="screen_name" value="'.$lead->leadTwtName.'" />';
											$item->htmlForm 	.= '<input type="hidden" name="skipIt" class="skip" value="false" />';
											
											$item->htmlForm 	.= '<textarea name="message" class="form-control" style="width:300px;height:150px;margin-bottom:5px;">';
											
												$item->htmlForm 	.= 'Hey ' . ucfirst($lead->leadTwtName) . '!' . PHP_EOL;
												$item->htmlForm 	.= PHP_EOL;
												$item->htmlForm 	.= 'Are you in the ' . get_option( $this->parent->_base . 'niche_business' ) . ' business?' . PHP_EOL;
												$item->htmlForm 	.= PHP_EOL;
												
												if(!$this->parent->user->is_admin){
													
													$item->htmlForm 	.= 'I am a ' . get_option( $this->parent->_base . 'niche_single' ) . '. Any new opportunities on your side?' . PHP_EOL;
													$item->htmlForm 	.= PHP_EOL;
												}
												
												$item->htmlForm 	.= 'We should exchange info.' . PHP_EOL;
												$item->htmlForm 	.= PHP_EOL;
												$item->htmlForm 	.= 'Have a nice day,' . PHP_EOL;
												$item->htmlForm 	.= $apps[$leadAppId]->screen_name . PHP_EOL;
												
											$item->htmlForm 	.= '</textarea>';
											
											$item->htmlForm .= '<div class="input-group">';
											
												$item->htmlForm .= '<i class="input-group">via @' . $apps[$leadAppId]->screen_name . '</i>';
											
												//$item->htmlForm .= '<input type="text" class="form-control" value="@'.$item->via.'" disabled="disabled" />';
												
												$item->htmlForm .= '<span class="input-group-btn">';
													
													$item->htmlForm .= '<button style="margin:0 1px;" class="engage btn btn-xs btn-default" type="button" data-skip="true"><i></i> skip</button>';
																							
													$item->htmlForm .= '<button style="margin:0 1px;" class="engage btn btn-xs btn-primary" type="button" data-skip="false"><i class="glyphicon glyphicon-send" aria-hidden="true"></i> DM</button>';

												$item->htmlForm .= '</span>';
												
											$item->htmlForm .= '</div>';								
												
										$item->htmlForm .= '</form>';
									}
								}
							}
						}
						else{
							
							$item->via = intval($lead->post_author);
						}
						
						foreach($meta as $key => $value){
							
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
	
	public function engage_leads(){
		
		//$user_id = 1;
		$user_id = $this->parent->user->ID;
		
		if( is_numeric($user_id) && !empty($_POST['screen_name']) && !empty($_POST['app'])&& !empty($_POST['appId']) && !empty($_POST['message'])  && !empty($_POST['action']) ){
			
			$app 			= $_POST['app'];
			$appId 			= $_POST['appId'];
			$leadAppId 		= $_POST['leadAppId'];
			$screen_name 	= $_POST['screen_name'];
			$message 		= $_POST['message'];
			$action 		= $_POST['action'];
			$opportunity 	= $_POST['opportunity'];
			$skipIt			= ( $_POST['skipIt'] == 'true' ? true : false );
			
			if(isset($this->parent->apps->{$app}) && method_exists($this->parent->apps->{$app}, $action) ){
				
				$response = $this->parent->apps->{$app}->$action($appId,$leadAppId,$screen_name,$message,$skipIt);
				
				if( $response === true ){
					
					return $this->list_leads( $user_id, $opportunity );
				}
				else{
					
					return $response;
				}
			}
			else{
				
				return 'Undefined leads/engage method...';
			}
		}
		else{
			
			return 'Malformed leads/engage request...';
		}
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
	
	public function get_access_message(){
		
		$message = '';

		$message = '<div class="modal-body">'.PHP_EOL;

			$message .=  '<div class="alert alert-info">';
				
				$message .=  '<span class="glyphicon glyphicon-lock" aria-hidden="true"></span> You must be a <b>PRO subscriber</b> to access this feature...';
				
				$message .=  '<div class="pull-right">';

					$message .=  '<a class="btn-sm btn-success" href="' . $this->parent->urls->plans . '" target="_parent">Subscribe now</a>';
					
				$message .=  '</div>';
				
			$message .=  '</div>';	

		$message .= '</div>'.PHP_EOL;

		return 	$message;
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