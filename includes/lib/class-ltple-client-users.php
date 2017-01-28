<?php

	if ( ! defined( 'ABSPATH' ) ) exit;

	class LTPLE_Client_Users {
		
		public $parent;
		public $view;
		public $users;

		public function __construct ( $parent ) {
			
			$this->parent = $parent;
			
			$this->users = new stdClass();
			
			global $pagenow;
			
			if( is_admin() && 'users.php' == $pagenow && isset($_REQUEST[$this->parent->_base .'view']) ){
				
				$this->view = $_REQUEST[$this->parent->_base .'view'];
				
				add_filter('admin_footer-users.php', array($this, 'ltple_add_users_table_view'));
				
				if( method_exists($this, 'ltple_update_' . $this->view . '_table') ){
					
					//remove_filter('manage_users_columns');
					
					add_filter('manage_users_columns', array($this, 'ltple_update_' . $this->view . '_table'), 100, 1);
				}
				
				if( method_exists($this, 'ltple_custom_' . $this->view . '_table_css') ){
					
					add_action('admin_head', array($this, 'ltple_custom_' . $this->view . '_table_css'));
				}
				
				if( method_exists($this, 'ltple_update_' . $this->view . '_manually') ){
					
					add_action('admin_head', array($this, 'ltple_update_' . $this->view . '_manually'));
				}
				
				if( method_exists($this, 'ltple_modify_' . $this->view . '_table_row') ){
					
					add_filter('manage_users_custom_column', array($this, 'ltple_modify_' . $this->view . '_table_row'), 100, 3);	
				}			
				
				if( method_exists($this, 'ltple_add_' . $this->view . '_bulk_action') ){

					add_action( 'admin_footer-users.php', array( $this, 'ltple_add_' . $this->view . '_bulk_action') );				
				}

				if( method_exists($this, 'ltple_load_' . $this->view . '_bulk_action') ){

					add_action('load-users.php', array( $this, 'ltple_load_' . $this->view . '_bulk_action') );				
				}
				
				// custom bulk actions

				add_action( 'restrict_manage_users', function(){
					
					static $instance = 0;   
					do_action( 'ltple_restrict_manage_users', 1 === ++$instance ? 'top' : 'bottom'  );
				});

				add_action( 'ltple_restrict_manage_users', function( $which ){
					
					echo '</div>'; //close previous actions div
					echo '<div class="actions" style="display:inline-block;">';
						
						// add marketing-channel filter
						
						$taxonomy = 'marketing-channel';
						
						$name = 'top' === $which ? $taxonomy.'1' : $taxonomy.'2';
						
						echo '<input type="hidden" name="ltple_view" value="subscribers">';
						
						echo '<span>';
							
							echo wp_dropdown_categories(array(
							
								'show_option_none'  => 'All Channels',
								'taxonomy'     		=> $taxonomy,
								'name'    	  		=> $name,
								'show_count'  		=> false,
								'hierarchical' 		=> true,
								'selected'     		=> ( isset($_REQUEST[$name]) ? $_REQUEST[$name] : ''),
								'echo'		   		=> false,
								'hide_empty'   		=> false
							));	

							echo '<input id="post-query-submit" type="submit" class="button" value="Filter" name="" style="float:left;">';
						
						echo '</span>';
						
						// add plan value filter
						
						echo '<span>';
							
							echo '<label style="padding:7px;float:left;">';
								echo ' Plan';
							echo '</label>';
							
							$filter = 'planValueOperator';
							$name = 'top' === $which ? $filter.'1' : $filter.'2';							
							
							echo'<select name="'.$name.'">';
								echo'<option value="'.htmlentities ('>').'" '.( (isset($_REQUEST[$name]) && $_REQUEST[$name] == htmlentities ('>')) ? ' selected="selected"' : '').'>'.htmlentities ('>').'</option>';
								echo'<option value="'.htmlentities ('<').'" '.( (isset($_REQUEST[$name]) && $_REQUEST[$name] == htmlentities ('<')) ? ' selected="selected"' : '').'>'.htmlentities ('<').'</option>';								
								echo'<option value="'.htmlentities ('=').'" '.( (isset($_REQUEST[$name]) && $_REQUEST[$name] == htmlentities ('=')) ? ' selected="selected"' : '').'>'.htmlentities ('=').'</option>';
							echo'</select>';
							
							$filter = 'userPlanValue';
							$name = 'top' === $which ? $filter.'1' : $filter.'2';

							echo '<input name="'.$name.'" type="number" value="'.( isset($_REQUEST[$name]) ? intval($_REQUEST[$name]) : -1).'" style="width:55px;float:left;">';

							echo '<input id="post-query-submit" type="submit" class="button" value="Filter" name="" style="float:left;">';
						
						echo '</span>';
						
						// add bulk stars
						
						echo '<span>';
							
							echo '<label style="padding:7px;float:left;">';
								echo ' Stars';
							echo '</label>';

							$filter = 'addStars';
							$name = 'top' === $which ? $filter.'1' : $filter.'2';

							echo '<input name="'.$name.'" type="number" value="0" style="width:55px;float:left;">';

							echo '<input id="post-query-submit" type="submit" class="button" value="Add" name="" style="float:left;">';
						
						echo '</span>';
						
						// add bulk email sender
						
						$post_type = 'email-model';
						
						$name = 'top' === $which ? $post_type.'1' : $post_type.'2';

						echo '<span>';
						
							echo $this->parent->ltple_get_dropdown_posts(array(
							
								'show_option_none'  => 'Select an email',
								'post_type'     	=> $post_type,
								'name'    	  		=> $name,
								'style'    	  		=> 'width:130px;',
								'selected'     		=> ( isset($_REQUEST[$name]) ? $_REQUEST[$name] : ''),
								'echo'		   		=> false
							));	

							echo '<input id="post-query-submit" type="submit" class="button" value="Send" name="" style="float:left;">';
						
						echo '</span>';
						
					//echo '</div>';					
					
				} );
				
				add_filter( 'pre_get_users', array( $this, 'ltple_filter_users_by_marketing_channel') );
				add_filter( 'pre_get_users', array( $this, 'ltple_filter_users_by_plan_value') );
				add_filter( 'pre_get_users', array( $this, 'ltple_bulk_send_email_model') );
				add_filter( 'pre_get_users', array( $this, 'ltple_bulk_add_stars') );
			}		
		}
		
		public function time_ago($time_ago) {
			
			$time_ago =  strtotime($time_ago) ? strtotime($time_ago) : $time_ago;
			$time  = time() - $time_ago;

			switch($time):
			// never
			case $time_ago == 0;
			return 'never';
			// seconds
			case $time <= 60;
			return 'now';
			// minutes
			case $time >= 60 && $time < 3600;
			return (round($time/60) == 1) ? '1 min ago' : round($time/60).' mins ago';
			// hours
			case $time >= 3600 && $time < 86400;
			return (round($time/3600) == 1) ? '1 hr ago' : round($time/3600).' hrs ago';
			// days
			case $time >= 86400 && $time < 604800;
			return (round($time/86400) == 1) ? '1 dy ago' : round($time/86400).' dys ago';
			// weeks
			case $time >= 604800 && $time < 2600640;
			return (round($time/604800) == 1) ? '1 wk ago' : round($time/604800).' wks ago';
			// months
			case $time >= 2600640 && $time < 31207680;
			return (round($time/2600640) == 1) ? '1 mth ago' : round($time/2600640).' mths ago';
			// years
			case $time >= 31207680;
			return (round($time/31207680) == 1) ? '1 yr ago' : round($time/31207680).' yrs ago' ;

			endswitch;
		}
		
		public function ltple_add_users_table_view() {
		 
			?>
			<script type="text/javascript">
			
				jQuery(document).ready(function() {
					  
					// find and update all segmentation href
					  
					jQuery('.subsubsub a').each(function() {
						
						this.href += (/\?/.test(this.href) ? '&' : '?') + '<?php echo $this->parent->_base . 'view'; ?>=<?php echo $this->view; ?>';
					});
					
					// add hidden input to form
					  
					jQuery('<input>').attr({type: 'hidden',name: '<?php echo $this->parent->_base . 'view'; ?>',value: '<?php echo $this->view; ?>'}).appendTo('form');
				});
			
			</script>
			<?php
		}
		
		public function ltple_update_subscribers_table($column) {
			
			$column=[];
			$column["cb"]			= '<input type="checkbox" />';
			$column["username"]		= 'Username';
			//$column["name"]		= 'Name';
			$column["email"]		= 'Email';
			$column["seen"]			= 'Seen';
			//$column["role"]		= 'Role';
			//$column["posts"]		= 'Posts';		
			$column["subscription"]	= 'Subscription';
			$column["plan"]			= 'Plan';
			$column["channel"]		= 'Channel';
			$column["stars"]		= 'Stars';
			$column["spam"]			= 'Spam';
			$column["sent"]			= 'Last emails sent';
			
			return $column;
		}
		
		public function ltple_custom_subscribers_table_css() {
			
			echo '<style>';
				
				echo '.column-seen 			{width: 8%}';
				echo '.column-subscription 	{width: 9%}';
				echo '.column-plan 			{width: 10%}';
				echo '.column-channel 		{width: 8%}';
				echo '.column-stars 			{width: 5%}';
				echo '.column-spam 			{width: 5%}';
				
		    echo '</style>';
		}

		public function ltple_modify_subscribers_table_row($val, $column_name, $user_id) {
			
			if(!isset($this->users->{$user_id})){
			
				$this->users->{$user_id} = new stdClass();
				$this->users->{$user_id}->role 		= get_userdata($user_id);
				$this->users->{$user_id}->plan 		= $this->parent->get_user_plan_info( $user_id, true );
				$this->users->{$user_id}->last_seen = get_user_meta($user_id, $this->parent->_base . '_last_seen',true);
				$this->users->{$user_id}->stars 	= $this->parent->stars->get_count($user_id);
				$this->users->{$user_id}->can_spam 	= get_user_meta($user_id, $this->parent->_base . '_can_spam',true);
				$this->users->{$user_id}->sent 		= get_user_meta($user_id, $this->parent->_base . '_email_sent',true);
				
				// user marketing channel
				$terms = wp_get_object_terms( $user_id, 'marketing-channel' );
				$this->users->{$user_id}->channel 	= ( ( !isset($terms->errors) && isset($terms[0]->name) ) ? $terms[0]->name : '');
			}
			
			$user_role = $this->users->{$user_id}->role;
			$user_plan = $this->users->{$user_id}->plan;
			$user_seen = $this->users->{$user_id}->last_seen;
			$user_stars= $this->users->{$user_id}->stars;
			$can_spam  = $this->users->{$user_id}->can_spam;
			$last_sent = $this->users->{$user_id}->sent;
			$channel   = $this->users->{$user_id}->channel;
			
			$search_terms = ( !empty($_REQUEST['s']) ? $_REQUEST['s'] : '' );
			
			$row='';
			
			if ($column_name == "subscription") { 
					
				if ($user_role->roles[0] != "administrator") {
					
					if( $user_plan['info']['total_fee_amount'] > 0 ){
						
						$row .= htmlentities(' ').$user_plan['info']['total_price_currency'].$user_plan['info']['total_fee_amount'].' '.$user_plan['info']['total_fee_period'];
						$row .= '<br>+';
					}
					
					$row .= $user_plan['info']['total_price_currency'].$user_plan['info']['total_price_amount'].'/'.$user_plan['info']['total_price_period'];
				} 
				else {
					
					$row .= "Admin";
				}
			}
			elseif ($column_name == "plan") {
					
				if ($user_role->roles[0] != "administrator") {
					
					$row .= '<pre style="margin: 0px;font-size: 10px;line-height: 14px;">';
					
					//$row .= $user_plan['id'].PHP_EOL;
					
					if( $user_plan['id'] > 0 ){
						
						foreach($user_plan['taxonomies'] as $taxonomy => $tax){
							
							foreach($tax['terms'] as $term){
								
								if($term['has_term']){
									
									$row .= $term['name'].PHP_EOL;
								}
							}
						}						
					}
					else{
						
						$row .= 'NULL'.PHP_EOL;
					}

					$row .= '</pre>';
				} 
				else {
					
					$row .= "-";
				}
			}
			elseif ($column_name == "seen") {
				
				$row .= $this->time_ago( '@' . $user_seen );
			}
			elseif ($column_name == "channel") {
				
				$row .= '<span>';
					
					$row .= $channel;
				
				$row .= '</span>';
			}
			elseif ($column_name == "stars") {

				$row .= $user_stars;
			}
			elseif ($column_name == "spam") {
				
				$row .= '<span>';
					
					if($can_spam==='false'){
						
						$text = "<img src='" . $this->parent->assets_url . "/images/wrong_arrow.png' width=25 height=25>";
						$row .= "<a title=\"Subscribe to mailing lists\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ltple_can_spam"), "ltple_can_spam" => "true" , "ltple_view" => "subscribers", "s" => $search_terms ), get_admin_url() . "users.php") . "\">" . apply_filters("ltple_manual_can_spam", $text) . "</a>";
					}
					else{
						
						$text = "<img src='" . $this->parent->assets_url . "/images/right_arrow.png' width=25 height=25>";
						$row .= "<a title=\"Unsubscribe from mailing lists\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ltple_can_spam"), "ltple_can_spam" => "false" , "ltple_view" => "subscribers", "s" => $search_terms ), get_admin_url() . "users.php") . "\">" . apply_filters("ltple_manual_can_spam", $text) . "</a>";
					}
					
				
				$row .= '</span>';
			}
			elseif ($column_name == "sent") {
				
				$emails = json_decode($last_sent,true);

				if( !empty( $emails ) ){
					
					$emails = array_slice($emails, 0, 5);					
					
					$row .= '<pre style="margin: 0px;font-size: 10px;line-height: 14px;">';

						foreach($emails as $slug => $date){
							
							$row .= ucfirst(substr(str_replace('-',' ',$slug),0,30)).'...'.PHP_EOL;
						}
					
					$row .= '</pre>';
				}
				else{
					
					$row .= '';
				}
			}
			
			return $row;
		}
		
		public function ltple_update_subscribers_manually() {
			
			if(isset($_REQUEST["user_id"]) && isset($_REQUEST["wp_nonce"]) && wp_verify_nonce($_REQUEST["wp_nonce"], "ltple_can_spam") && isset($_REQUEST["ltple_can_spam"])) {
				
				if($_REQUEST["ltple_can_spam"] === 'true' || $_REQUEST["ltple_can_spam"] === 'false'){
					
					update_user_meta($_REQUEST["user_id"], $this->parent->_base . '_can_spam', $_REQUEST["ltple_can_spam"]);
				}
			}
		}
		
		public function ltple_add_subscribers_bulk_action() {
		 
			?>
			<script type="text/javascript">
			
				jQuery(document).ready(function() {
					  
					// append to top dropdown
					jQuery('<option>').val('export-emails').text('<?php _e('Export emails')?>').appendTo("select[name='action']");
					
					// append to bottom dropdown
					jQuery('<option>').val('export-emails').text('<?php _e('Export emails')?>').appendTo("select[name='action2']");
				
					jQuery('form').attr('method','post');
				});
			
			</script>
			<?php
		}

		public function ltple_load_subscribers_bulk_action() {
		 
			// get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');
			$action = $wp_list_table->current_action();
			$sendback = '';
			
			// security check
			//check_admin_referer('bulk-users');
			
			switch($action) {
			
				case 'export-emails':
				
					// if we set up user permissions/capabilities, the code might look like:
					//if ( !current_user_can($post_type_object->cap->export_post, $post_id) )
					//  pp_die( __('You are not allowed to export this post.') );
				 
					$exported = 0;
					
					if( !empty($_REQUEST['users']) ){
						
						$user_ids = $_REQUEST['users'];
						
						$users = new WP_User_Query(array(
						
							'include' => $user_ids
						));
						
						if(!empty($users->results)){
							
							ob_get_clean();
							
							echo '<pre>';
							
								echo 'email' . "\t" . 'name'. PHP_EOL;
								
								foreach( $users->results as $user ) {
									
									echo $user->user_email . "\t" . $user->user_nicename . PHP_EOL;
				
									$exported++;
								}
							
							echo '</pre>';
							
							exit;						
						}

						// build the redirect url
						$sendback = add_query_arg( array( 'exported' => $exported, 'ltple_view' => $_REQUEST['ltple_view'] ), $sendback );		
					}
					
				break;
				default: return;
			}
		 
			// redirect client
			//wp_redirect($sendback);
		 
			exit();
		}			
		
		public function ltple_get_filter_value($filter) {
			
			$value=null;
			
			if ( isset( $_REQUEST[$filter.'1'] ) && $_REQUEST[$filter.'1'] != '-1' ) {
				
				$value = $_REQUEST[$filter.'1'];
			}
			elseif ( isset( $_REQUEST[$filter.'2'] ) && $_REQUEST[$filter.'2'] != '-1' ) {
				
				$value = $_REQUEST[$filter.'2'];
			}

			return $value;
		}
		
		public function ltple_filter_users_by_marketing_channel( $query ) {
			
			$taxonomy = 'marketing-channel';
			$term_id = $this->ltple_get_filter_value($taxonomy);
			
			if(!is_null($term_id)){
				
				// alter the user query to add my meta_query
				
				$users = get_objects_in_term( intval($term_id), $taxonomy );
				
				if(!empty($users)){
					
					$query->set( 'include', $users);
				}
				else{
					
					$query->set( 'meta_key', 'something-that-doesnt-exists' ); //to return NULL instead of all
				}
			}
		}
		
		
		public function ltple_filter_users_by_plan_value( $query ) {

			$userPlanValue		= $this->ltple_get_filter_value('userPlanValue');
			$planValueOperator	= $this->ltple_get_filter_value('planValueOperator');
			
			$comparition = [];
			
			$comparition['=']['operator']	= '!=';
			$comparition['=']['action']		= 'exclude';
			
			$comparition['>']['operator']	= '>';
			$comparition['>']['action']		= 'include';
			
			$comparition['<']['operator']	= '>=';
			$comparition['<']['action']		= 'exclude';

			if( !is_null($userPlanValue) && $userPlanValue > -1 ){

				$q = new WP_Query(array(
				
					'posts_per_page'=> -1,
					'post_type'		=> 'user-plan',
					'fields' 		=> 'post_author',
					'meta_query'	=> array(
						array(
							'key'		=> 'userPlanValue',
							'value'		=> $userPlanValue,
							'type'		=> 'NUMERIC',
							'compare'	=> $comparition[$planValueOperator]['operator']
						)
					)
				));

				if(!empty($q->posts)){
					
					$users = [];
					
					foreach($q->posts as $post){
						
						$users[] = $post->post_author;
					}
					
					$query->set( $comparition[$planValueOperator]['action'], $users);
				}
				else{
					
					$query->set( 'meta_key', 'something-that-doesnt-exists' ); //to return NULL instead of all
				}			
			}
		}
		
		public function ltple_bulk_send_email_model( $query ) {
			
			$post_type = 'email-model';
			$model_id=null;
			
			if ( isset( $_REQUEST[$post_type.'1'] ) && is_numeric( $_REQUEST[$post_type.'1'] ) && $_REQUEST[$post_type.'1'] != '-1' ) {
				
				$model_id=intval($_REQUEST[$post_type.'1']);
			}
			elseif ( isset( $_REQUEST[$post_type.'2'] ) && is_numeric( $_REQUEST[$post_type.'2'] ) && $_REQUEST[$post_type.'2'] != '-1' ) {
				
				$model_id=intval($_REQUEST[$post_type.'2']);
			}
			
			if( !is_null( $model_id ) && !empty($_REQUEST['users']) && is_array($_REQUEST['users'])){
				
				$this->email_sent	  =0;
				$this->email_not_sent =0;
				
				foreach( $_REQUEST['users'] as $user_id){
					
					$user = get_userdata($user_id);
				
					if($this->parent->ltple_send_email_model( $model_id, $user)){
						
						++$this->email_sent;	
					}
					else{
						
						++$this->email_not_sent;
					}
				}

				add_action( 'admin_notices', array( $this, 'output_send_email_admin_notice'));				
			}
		}
		
		public function output_send_email_admin_notice(){
			
			if( $this->email_sent > 0 ){
				
				echo'<div class="notice notice-success">';
				
					echo'<p>';
					
						echo $this->email_sent .' email(s) have been succesfully sent';
						
					echo'</p>';
					
				echo'</div>';					
			}
			
			if( $this->email_not_sent > 0 ){
				
				echo'<div class="notice notice-warning">';
				
					echo'<p>';
					
						echo $this->email_not_sent .' email(s) have not been sent...';
						
					echo'</p>';
					
				echo'</div>';					
			}			
		}
		
		
		public function ltple_bulk_add_stars() {
			
			$field = 'addStars';
			$addStars=0;
			
			if ( isset( $_REQUEST[$field.'1'] ) && is_numeric( $_REQUEST[$field.'1'] ) ) {
				
				$addStars = floatval($_REQUEST[$field.'1']);
			}
			elseif ( isset( $_REQUEST[$field.'2'] ) && is_numeric( $_REQUEST[$field.'2'] ) ) {
				
				$addStars = floatval($_REQUEST[$field.'2']);
			}
			
			if( is_numeric( $addStars ) && !empty($_REQUEST['users']) && is_array($_REQUEST['users'])){
				
				$this->stars_added = $addStars;
				
				foreach( $_REQUEST['users'] as $user_id){
					
					$this->parent->stars->add_stars( $user_id, $addStars );
				}

				add_action( 'admin_notices', array( $this, 'output_stars_added_notice'));						
			}
		}
		
		public function output_stars_added_notice(){
			
			echo'<div class="notice notice-success">';
			
				echo'<p>';
				
					echo $this->stars_added .' stars added';
					
				echo'</p>';
				
			echo'</div>';						
		}
		
		/**
		 * Main LTPLE_Client_Users Instance
		 *
		 * Ensures only one instance of LTPLE_Client_Users is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see LTPLE_Client()
		 * @return Main LTPLE_Client_Users instance
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