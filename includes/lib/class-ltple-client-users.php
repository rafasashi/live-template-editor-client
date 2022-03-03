<?php

	if ( ! defined( 'ABSPATH' ) ) exit;

	class LTPLE_Client_Users {
		
		var $parent;
		var $view;
		var $list;
		
		var $referent;
		var $referral;
		var $referrals;
		
		var $bbpAuthorLinks = array();

		public function __construct ( $parent ) {
			
			$this->parent = $parent;
			
			$this->list = new stdClass();
			
			add_filter('init', array( $this, 'init_periods' ));
			
			add_filter('ltple_user_loaded', array( $this, 'init_user' ));
						
			add_filter('ltple_loaded', array( $this, 'init_users' ));
			
			add_filter('author_link', array($this, 'get_author_url'),9999,2);
			
			add_filter('bbp_suppress_private_author_link', array($this, 'get_bbp_author_link'),9999);
				
			add_filter('template_redirect', array( $this, 'redirect_user_profile' ));
			
			add_filter('bbp_template_before_user_profile', array( $this, 'redirect_bbpress_profile' ));
			
			add_filter('bbp_template_before_user_subscriptions', array( $this, 'redirect_bbpress_profile' ));
			
			add_filter('bbp_template_before_user_topics_created', array( $this, 'redirect_bbpress_profile' ));			
			
			add_filter('bbp_template_before_user_replies', array( $this, 'redirect_bbpress_profile' ));
			
			add_filter('bbp_template_before_user_favorites', array( $this, 'redirect_bbpress_profile' ));
			
			add_filter('bbp_user_edit_before', array( $this, 'redirect_bbpress_edit_profile' ));
			
			add_filter('admin_init', array( $this, 'delete_user_manually' ));
			
			add_action('delete_user', array( $this, 'delete_user' ),1,1);
		}

		public function init_periods(){
			
			if( is_admin() ){
				
				if( !empty($_REQUEST['ltple_update_period']) && is_numeric($_REQUEST['ltple_update_period']) ){
					
					// update subscription periods
					
					$this->update_periods($_REQUEST['ltple_update_period']);
					
					add_action('admin_notices',function(){
						
						echo '<div class="notice notice-success is-dismissible">';
							
							echo '<p>Periods updated</p>';
						
						echo '</div>';
						
					});
				}
				
				// schedule update subscription periods
				
				add_action( $this->parent->_base . 'update_periods', array( $this, 'update_periods'));
				
				if( !wp_next_scheduled( $this->parent->_base . 'update_periods' )) {
					
					wp_schedule_event( time(), 'hourly' , $this->parent->_base . 'update_periods' );
				}
			}
		}
	
		public function is_pro_user($user_id){
			
			$is_pro = false;
			
			$period_end = $this->parent->plan->get_license_period_end($user_id);
			
			$remaining_days = $this->parent->plan->get_license_remaining_days($period_end);

			if( $remaining_days > 0 ){
				
				$is_pro = true;
			}
			
			return $is_pro;
		}

		
		public function get_author_url( $url, $author_id ){
			
			if( !empty($this->parent->urls->profile) ){
				
				$url = $this->parent->urls->profile . $author_id. '/';
			}
			
			return $url;
		}

		public function get_bbp_author_link( $link ){
			
			$md5 = md5($link);
			
			if( isset($this->bbpAuthorLinks[$md5]) ){
				
				$link = $this->bbpAuthorLinks[$md5];
			}
			else{
				
				$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
			  
				if( preg_match_all("/$regexp/siU", $link, $matches) ) {
					
					$link = '';
					
					if( !empty($matches[2][0]) ){
						
						$author_name = basename($matches[2][0]);

						if( $author = get_user_by('slug',$author_name) ){
							
							$url = $this->parent->urls->profile . $author->ID . '/';
							
							$name = ucfirst($author->nickname);
							
							$picture = $this->parent->image->get_avatar_url($author->ID);
							
							$link .= '<a style="display:inline-block;" href="'.$url.'" title="View '.$name.'\'s profile" class="bbp-author-avatar">';
								
								$link .= '<img loading="lazy" style="border:0;border-radius:250px;" alt="" src="'.$picture.'" srcset="'.$picture.'" class="avatar photo" />';
							
							$link .= '</a>';
							
							$link .= '&nbsp;';
							
							$link .= '<a href="'.$url.'" title="View '.$name.'\'s profile" class="bbp-author-name">';
							
								$link .= $name;
							
							$link .= '</a>';					
						}
					}
				}
				
				$this->bbpAuthorLinks[$md5] = $link;
			}
			
			return $link;
		}		
		
		public function redirect_user_profile(){
			
			if( is_author() ){
				
				$author_name = get_query_var('author_name');
				
				if( $author = get_user_by('slug',$author_name) ){
					
					$url = $this->parent->urls->profile . $author->ID . '/';
					
					wp_redirect($url);
				}
				else{
					
					echo 'No profile found...';
				}
				
				exit;
			}
		}
		
		public function redirect_bbpress_profile(){
			
			if( !$author_name = get_query_var('author_name') ){
				
				$author_name = get_query_var('bbp_user');
			}
			
			if(!empty($author_name)){
			
				if( $author = get_user_by('slug',$author_name) ){
					
					$url = $this->parent->urls->profile . $author->ID . '/';
					
					wp_redirect($url);
				}
				else{
					
					echo 'This page doesn\'t exists...';
				}
			}
			else{
				
				echo 'This user doesn\'t exists...';
			}
			
			exit;
		}

		public function redirect_bbpress_edit_profile(){
			
			$url = $this->parent->urls->profile;
				
			wp_redirect($url);
			
			exit;
		}
		
		public function init_user(){
			
			if( $this->parent->user->loggedin ){	

				//update user channel
				
				$this->parent->channels->update_user_channel($this->parent->user->ID);			
				
				//update user image
				
				$this->parent->image->update_user_image();
				
				//get user plan
				
				$this->parent->plan->update_user();
			}
		}
		
		public function init_users(){
			
			if( !is_admin() ){
				
				add_action( 'user_register', array( $this, 'ref_user_register' ));
				
				add_action( 'ltple_users_bulk_imported', array( $this, 'ref_users_bulk_register' ));
			}
			else{
				
				global $pagenow;

				if( is_admin() && $pagenow == 'users.php' ){
					
					if(isset($_REQUEST[$this->parent->_base .'view'])){
					
						$this->view = $_REQUEST[$this->parent->_base .'view'];
					}
					
					add_filter('admin_footer-users.php', array($this, 'add_users_table_view'));
					
					add_filter('get_avatar', array($this, 'get_user_avatar'),9999,5);			
				
					add_action('admin_head', array($this, 'update_user_manually'));				
					
					add_action( 'admin_footer-users.php', array( $this, 'add_bulk_actions') );					
					
					add_action('load-users.php', array( $this, 'load_bulk_action') );					
					
					if( method_exists($this, 'custom_' . $this->view . '_table_css') ){
						
						add_action('admin_head', array($this, 'custom_' . $this->view . '_table_css'));
					}
					else{
						
						add_action('admin_head', array($this, 'custom_users_table_css'));
					}
					
					if( method_exists($this, 'set_' . $this->view . '_table_columns') ){
						
						add_filter('manage_users_columns', array($this, 'set_' . $this->view . '_table_columns'), 100, 3);
					}
					else{
						
						add_filter('manage_users_columns', array($this, 'set_users_table_columns'), 100, 3);
					}

					if( method_exists($this, 'get_' . $this->view . '_table_row') ){
						
						add_filter('manage_users_custom_column', array($this, 'get_' . $this->view . '_table_row'), 100, 3);	
					}
					else{
						
						add_filter('manage_users_custom_column', array($this, 'get_users_table_row'), 100, 3);
					}
					
					// custom bulk actions

					add_action( 'restrict_manage_users', function(){
						
						static $instance = 0;
						
						do_action( 'ltple_restrict_manage_users', 1 === ++$instance ? 'top' : 'bottom'  );
					});

					add_action( 'ltple_restrict_manage_users', function( $which ){
						
						if( $which == 'top' && empty($GLOBALS['ltple_user_tab_added']) ){
							
							$GLOBALS['ltple_user_tab_added'] = true;
							
							echo '</div>'; //close previous actions div
							
							echo '<div style="width:100%;display: inline-block;">';
							
								echo '<h2 class="nav-tab-wrapper" style="margin-bottom: 7px;margin-top: 15px;">';
									
									do_action('ltple_user_tab');
								
								echo '</h2>';				
							
							echo '</div>';
							
							echo '<div class="alignleft actions">';
							
								do_action('ltple_user_filter');
							
							echo '</div>';
							
							echo '<div class="alignleft actions">';
							
								do_action('ltple_user_updater');						
						}
					} );
					
					add_filter( 'ltple_user_tab', array( $this, 'display_user_tab') );
					
					add_filter( 'ltple_user_filter', array( $this, 'display_user_filter') );
					
					add_filter( 'ltple_user_updater', array( $this, 'display_user_updater') );
					
					// query filters
					
					add_filter( 'pre_get_users', array( $this, 'filter_users_by_email_subscription') );					
					add_filter( 'pre_get_users', array( $this, 'filter_users_by_marketing_channel') );
					add_filter( 'pre_get_users', array( $this, 'filter_users_by_plan_value') );

					//add_filter( 'pre_get_users', array( $this, 'filter_users_by_role') );
				}				
			}
		}
		
		public function update_periods($user_id=null){ 
			
			if( $periods = $this->parent->plan->remote_get_periods() ){
				
				$args = array( 
					
					'fields' => array('id','user_email'),
				);
				
				if( !is_null($user_id) ){
					
					$args['include'] = is_numeric($user_id) ? array($user_id) : $user_id;
				}
				else{
					
					// get users with subscription
					
					$args['meta_query'] = array(
						
						'relation' => 'OR',
						
						array(
						
							'key'     	=> 'has_subscription',
							'compare' 	=> '=',
							'value'		=> 'true',
						),	
						array(
						
							'key'     	=> $this->parent->_base . 'period_end',
							'compare' 	=> 'EXISTS',
						),
						/*
						array(
					
							'key'     	=> $this->parent->_base . 'period_end',
							'compare' 	=> '<',
							'value'		=> time(), // for debugging
						)
						*/
					);				
				}
				
				if( $users = get_users($args) ){
					
					foreach( $users as $user ){
						
						$user_email = strtolower($user->user_email);
						
						if( intval($user->id) > 1 && isset($periods[$user_email]) ){
							
							if( !empty($periods[$user_email]) ){
							
								$this->update_user_period($user->id,$periods[$user_email]);
							}
							else{
								
								$remaining_days = $this->get_user_remaining_days($user->id);
								
								if( $remaining_days < -30 ){
								
									// flush user plan
									
									$this->parent->plan->flush_user_plan($user->id);
								}
							}
						}
					}
				}							
			}
			else{
				
				wp_mail($this->parent->settings->options->emailSupport, 'Error updating periods', print_r('',true));
			}
		}

		public function update_user_period($user_id=0,$period_end=0){
			
			$user_id = intval($user_id);
			
			if( $user_id > 0 ){
				
				$period_end = intval($period_end);
			
				update_user_meta($user_id, $this->parent->_base . 'period_end', $period_end);
			
				$user_has_subscription = 'false';
				
				$remaining_days = $this->parent->plan->get_license_remaining_days($period_end);
				
				if( $remaining_days > 0 ){
					
					$user_has_subscription = 'true';
				}
				
				update_user_meta( $user_id , 'has_subscription', $user_has_subscription);
			
				return true;
			}
			
			return false;
		}
		
		public function display_user_tab() {
			
			$s 	= ( !empty($_REQUEST['s']) ? urlencode($_REQUEST['s']) : '' );
			$mc = ( !empty($_REQUEST['marketing-channel1']) ? intval($_REQUEST['marketing-channel1']) : '' );
			
			echo '<a class="nav-tab ' . ( empty($this->view) ? 'nav-tab-active' : '' ) . '" href="users.php?s='.$s.'&marketing-channel1='.$mc.'">Users</a>';
			
			echo '<a class="nav-tab ' . ( $this->view == 'customers' ? 'nav-tab-active' : '' ) . '" href="users.php?ltple_view=customers&s='.$s.'&marketing-channel1='.$mc.'">Customers</a>';
			
			echo '<a class="nav-tab ' . ( $this->view == 'newsletter' ? 'nav-tab-active' : '' ) . '" href="users.php?ltple_view=newsletter&s='.$s.'&marketing-channel1='.$mc.'">Newsletter</a>';
			
			/*
			echo '<a class="nav-tab ' . ( $this->view == 'guests' ? 'nav-tab-active' : '' ) . '" href="users.php?ltple_view=guests&s='.$s.'&marketing-channel1='.$mc.'">Guests</a>';
											
			echo '<a class="nav-tab ' . ( $this->view == 'unsubscribers' ? 'nav-tab-active' : '' ) . '" href="users.php?ltple_view=unsubscribers&s='.$s.'&marketing-channel1='.$mc.'">Unsubscribers</a>';
			
			echo '<a class="nav-tab ' . ( $this->view == 'leads' ? 'nav-tab-active' : '' ) . '" href="users.php?ltple_view=leads&s='.$s.'&marketing-channel1='.$mc.'">Leads</a>';
			*/
		}
		
		public function display_user_filter() {
			
			// add marketing-channel filter
			
			$taxonomy = 'marketing-channel';
			
			$name = $taxonomy.'1';
			
			echo '<input type="hidden" name="ltple_view" value="newsletter">';
			
			echo '<span>';
				
				echo wp_dropdown_categories(array(
				
					'show_option_none'  => 'All Channels',
					'taxonomy'     		=> $taxonomy,
					'name'    	  		=> $name,
					'show_count'  		=> false,
					'hierarchical' 		=> true,
					'selected'     		=> ( isset($_REQUEST[$name]) ? $_REQUEST[$name] : ''),
					'echo'		   		=> false,
					'class'		   		=> 'form-control',
					'hide_empty'   		=> false
				));	

				echo '<input id="post-query-submit" type="submit" class="button" value="Filter" name="" style="float:left;">';
			
			echo '</span>';
			
			// add plan value filter

			if( $this->view == 'customers' ){
			
				echo '<span>';
					
					$filter = 'licenseStatus';
					
					$name = $filter.'1';							
					
					echo'<select name="'.$name.'">';
						
						echo'<option value="all" '.( (isset($_REQUEST[$name]) && $_REQUEST[$name] == htmlentities ('all')) ? ' selected="selected"' : '').'>All Subscriptions</option>';
						
						echo'<option value="active" '.( (isset($_REQUEST[$name]) && $_REQUEST[$name] == htmlentities ('active')) ? ' selected="selected"' : '').'>Active</option>';								
						
						echo'<option value="due" '.( (isset($_REQUEST[$name]) && $_REQUEST[$name] == htmlentities ('due')) ? ' selected="selected"' : '').'>Past Due</option>';
					
					echo'</select>';

					echo '<input id="post-query-submit" type="submit" class="button" value="Filter" name="" style="float:left;">';
				
				echo '</span>';
			}
			
		}
		
		public function display_user_updater() {

			if( $this->view == 'newsletter' ){
			
				// add bulk email sender
				
				$post_type = 'email-model';
				
				$name = $post_type.'1';

				echo '<span>';
				
					echo $this->parent->get_dropdown_posts(array(
					
						'show_option_none'  => 'Select an email',
						'post_type'     	=> $post_type,
						'name'    	  		=> $name,
						'style'    	  		=> 'width:130px;',
						'selected'     		=> ( isset($_REQUEST[$name]) ? $_REQUEST[$name] : ''),
						'echo'		   		=> false
					));

					echo '<input id="post-query-submit" type="submit" class="button" value="Send" name="" style="float:left;">';
				
				echo '</span>';
			}
			else{

				// add bulk stars
				
				echo '<span>';
					
					echo '<label style="padding:7px;float:left;">';
						echo ' Stars';
					echo '</label>';

					$filter = 'addStars';
					$name = $filter.'1';

					echo '<input name="'.$name.'" type="number" value="0" style="width:55px;float:left;">';

					echo '<input id="post-query-submit" type="submit" class="button" value="Add" name="" style="float:left;">';
				
				echo '</span>';
				
				// add plan
				
				$post_type = 'subscription-plan';
				
				$name = $post_type.'1';

				echo '<span>';
				
					echo $this->parent->get_dropdown_posts(array(
					
						'show_option_none'  => 'Select a plan',
						'post_type'     	=> $post_type,
						'name'    	  		=> $name,
						'style'    	  		=> 'width:130px;',
						'selected'     		=> ( isset($_REQUEST[$name]) ? $_REQUEST[$name] : ''),
						'echo'		   		=> false
					));

					echo '<input id="post-query-submit" type="submit" class="button" value="Add" name="" style="float:left;">';
				
				echo '</span>';
				
				// add layer type
				
				/*
				
				$taxonomy = 'layer-type';
				
				$name = $taxonomy.'1';

				echo '<span>';
				
					echo $this->parent->get_dropdown_terms(array(
					
						'show_option_none'  => 'Select a type',
						'taxonomy'     		=> $taxonomy,
						'name'    	  		=> $name,
						'style'    	  		=> 'width:130px;',
						'selected'     		=> ( isset($_REQUEST[$name]) ? $_REQUEST[$name] : ''),
						'echo'		   		=> false
					));

					echo '<input id="post-query-submit" type="submit" class="button" value="Add" name="" style="float:left;">';
				
				echo '</span>';
				*/
				
				// add layer range
				
				$taxonomy = 'layer-range';
				
				$name = $taxonomy.'1';

				echo '<span>';
				
					echo $this->parent->get_dropdown_terms(array(
					
						'show_option_none'  => 'Select a range',
						'taxonomy'     		=> $taxonomy,
						'name'    	  		=> $name,
						'style'    	  		=> 'width:130px;',
						'selected'     		=> ( isset($_REQUEST[$name]) ? $_REQUEST[$name] : ''),
						'echo'		   		=> false
					));

					echo '<input id="post-query-submit" type="submit" class="button" value="Add" name="" style="float:left;">';
				
				echo '</span>';
				
				// add layer option
				
				$taxonomy = 'account-option';
				
				$name = $taxonomy.'1';

				echo '<span>';
				
					echo $this->parent->get_dropdown_terms(array(
					
						'show_option_none'  => 'Select an option',
						'taxonomy'     		=> $taxonomy,
						'name'    	  		=> $name,
						'style'    	  		=> 'width:130px;',
						'selected'     		=> ( isset($_REQUEST[$name]) ? $_REQUEST[$name] : ''),
						'echo'		   		=> false
					));

					echo '<input id="post-query-submit" type="submit" class="button" value="Add" name="" style="float:left;">';
				
				echo '</span>';
			}
		}
		
		public function time_ago($time_ago=0) {
			
			if( !empty($time_ago) && is_string($time_ago) )
				
				$time_ago = strtotime($time_ago);
			
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
		
		public function get_user_avatar($avatar, $user_id, $size, $alt, $args){

			$avatar_url = $this->parent->image->get_avatar_url($user_id);
			
			$avatar = '<img loading="lazy" alt="" class="lazy" data-original="'.$avatar_url.'" disabled-srcset="'.$avatar_url.'" height="32" width="32" src="'.$avatar_url.'" style="border-radius:250px;">';

			return $avatar;
		}
		
		public function get_user_remaining_days($user_id){
			
			// to insure compatibility with children
			
			$period_end = $this->parent->plan->get_license_period_end($user_id);
			
			return $this->parent->plan->get_license_remaining_days($period_end);
		}
		
		public function get_browser( $user_agent ) {
			
			$browser = '';
			
			if (strpos($user_agent, 'MSIE') !== FALSE){
				
				$browser = 'IE';
			}
			elseif (strpos($user_agent, 'Chrome') !== FALSE) {
				
				$browser = 'Chrome';
			}
			elseif (strpos($user_agent, 'Firefox') !== FALSE) {
				
				$browser = 'Firefox';
			}
			elseif (strpos($user_agent, 'Opera') !== FALSE) {
				
				$browser = 'Opera';
			}
			elseif (strpos($user_agent, 'Safari') !== FALSE) {
				
				$browser = 'Safari';
			}

			return $browser;
		}		
		
		public function add_users_table_view() {
		 
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

		public function set_users_table_columns($column) {
			
			$column=[];
			$column["cb"]			= '<input type="checkbox" />';
			$column["username"]		= 'Username';
			//$column["name"]		= 'Name';
			$column["email"]		= 'Email';
			$column["seen"]			= 'Seen';
			$column["channel"]		= 'Channel';
			$column["stars"]		= 'Stars';
			$column["notify"]		= 'Notify';

			return $column;
		}
		
		public function set_customers_table_columns($column) {
			
			$column=[];
			$column["cb"]			= '<input type="checkbox" />';
			$column["username"]		= 'Username';
			//$column["name"]		= 'Name';
			$column["email"]		= 'Email';
			$column["seen"]			= 'Seen';	
			$column["subscription"]	= 'Subscription';
			$column["plan"]			= 'Plan';
			$column["channel"]		= 'Channel';
			$column["stars"]		= 'Stars';

			return $column;
		}

		public function set_newsletter_table_columns($column) {
			
			$column=[];
			$column["cb"]			= '<input type="checkbox" />';
			$column["username"]		= 'Username';
			$column["email"]		= 'Email';
			$column["seen"]			= 'Seen';	
			$column["channel"]		= 'Channel';
			$column["notify"]		= 'Notify';
			$column["sent"]			= 'Last emails sent';
			
			return $column;
		}
		
		public function custom_users_table_css() {
			
			echo '<style>';
							
				echo '.wrap						{margin:0 !important;}';	
				echo '#wpcontent, #wpfooter 	{margin-left: 150px;}';
				echo '.column-username img 	{display: inline-table;}';
				echo '.column-username strong {display: inline-table;width: 100%;}';
				//echo '.column-username  		{width: 15%}';
				echo '.column-email  			{width: 15%}';
				echo '.column-seen 				{width: 8%}';
				echo '.column-subscription 		{width: 8%}';
				echo '.column-plan 				{width: 10%}';
				echo '.column-channel 			{width: 8%}';
				echo '.column-stars 			{width: 5%;text-align:center;}';
				echo '.column-notify 			{width: 8%;text-align:center;}';
				
		    echo '</style>';
		}
		
		public function get_all_user_meta($user_id){
			
			$meta = array();
			
			if( $data = get_user_meta($user_id)){
				
				foreach( $data as $key => $value ){
					
					if(isset($value[0])){
						
						$meta[$key] = maybe_unserialize($value[0]);
					}
				}
			}
		
			return $meta;
		}

		public function get_users_table_row($val, $column_name, $user_id) {
			
			if(!isset($this->list->{$user_id})){
				
				$meta = $this->get_all_user_meta($user_id);
				
				$this->list->{$user_id} = new stdClass();
				
				$this->list->{$user_id}->role 		= get_userdata($user_id);
				$this->list->{$user_id}->last_seen 	= isset($meta[$this->parent->_base . '_last_seen']) ? $meta[$this->parent->_base . '_last_seen'] : '';
				$this->list->{$user_id}->last_uagent= isset($meta[$this->parent->_base . '_last_uagent']) ? $this->get_browser($meta[$this->parent->_base . '_last_uagent']) : '';
				$this->list->{$user_id}->stars 		= $this->parent->stars->get_count($user_id);
				$this->list->{$user_id}->can_spam 	= isset($meta[$this->parent->_base . '_can_spam']) ? $meta[$this->parent->_base . '_can_spam'] : 'false';
				$this->list->{$user_id}->notify 	= isset($meta[$this->parent->_base . 'notify']) ? $meta[$this->parent->_base . 'notify'] : $this->get_user_notification_settings($user_id);
				$this->list->{$user_id}->referredBy	= isset($meta[$this->parent->_base . 'referredBy']) ? $meta[$this->parent->_base . 'referredBy'] : '';

				// user marketing channel
				
				$terms = wp_get_object_terms( $user_id, 'marketing-channel' );		
				$this->list->{$user_id}->channel = ( ( !isset($terms->errors) && isset($terms[0]->name) ) ? $terms[0]->name : '');
			}
			
			$user_role 	= $this->list->{$user_id}->role;
			$user_seen 	= $this->list->{$user_id}->last_seen;
			$user_agent	= $this->list->{$user_id}->last_uagent;
			$user_stars	= $this->list->{$user_id}->stars;
			$can_spam  	= $this->list->{$user_id}->can_spam;
			$notify  	= $this->list->{$user_id}->notify;
			$referredBy	= $this->list->{$user_id}->referredBy;
			$channel   	= $this->list->{$user_id}->channel;
			
			$search_terms = ( !empty($_REQUEST['s']) ? $_REQUEST['s'] : '' );
			
			$row='';

			if ($column_name == "seen") {
				
				if( !empty($user_agent) ){
				
					$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
					
						$row .= $this->get_browser($user_agent);
						
					$row .= '</span>';
				}
				
				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
				
					$row .= $this->time_ago( '@' . $user_seen );
					
				$row .= '</span>';
			}
			elseif ($column_name == "channel") {
				
				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
					
					if(!empty($referredBy)){
						
						$row .= '<a href="'.admin_url( 'user-edit.php' ).'?user_id='.key($referredBy).'">'.reset($referredBy).'</a>';
					}
					else{
						
						$row .= $channel;
					}
				
				$row .= '</span>';
			}
			elseif ($column_name == "stars") {
				
				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
					
					$row .= $user_stars;
					
				$row .= '</span>';
			}
			elseif ($column_name == "notify") {
				
				$notify = array_merge($this->parent->email->get_notification_settings(),$notify);
				
				foreach( $notify as $channel => $can_notify ){
					
					$row .= '<div style="font-size:11px;text-align:left;">';
						
						$channel_name = ucfirst($channel);
						
						if( $can_notify != 'true' ){
							
							$text = "<img loading='lazy' class='lazy' data-original='" . $this->parent->assets_url . "/images/wrong_arrow.png' width=15 height=15>" . $channel_name;
							$row .= "<a title=\"Subscribe to ".$channel_name."\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ltple_notify"), "ltple_notify[".$channel."]" => "true" , "ltple_view" => $this->view, "s" => $search_terms ), get_admin_url() . "users.php") . "\">" . apply_filters("ltple_manual_notify", $text) . "</a>";
						}
						else{
							
							$text = "<img class='lazy' data-original='" . $this->parent->assets_url . "/images/right_arrow.png' width=15 height=15>" . $channel_name;
							$row .= "<a title=\"Unsubscribe from ".$channel_name."\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ltple_notify"), "ltple_notify[".$channel."]" => "false" , "ltple_view" => $this->view, "s" => $search_terms ), get_admin_url() . "users.php") . "\">" . apply_filters("ltple_manual_notify", $text) . "</a>";
						}

					$row .= '</div>';
				}
			}
			
			return $row;
		}
		
		public function get_newsletter_table_row($val, $column_name, $user_id) {
			
			if(!isset($this->list->{$user_id})){
				
				$meta = $this->get_all_user_meta($user_id);
				
				$this->list->{$user_id} = new stdClass();
				
				$this->list->{$user_id}->role 		= get_userdata($user_id);
				$this->list->{$user_id}->last_seen 	= isset($meta[$this->parent->_base . '_last_seen']) ? $meta[$this->parent->_base . '_last_seen'] : '';
				$this->list->{$user_id}->last_uagent= isset($meta[$this->parent->_base . '_last_uagent']) ? $this->get_browser($meta[$this->parent->_base . '_last_uagent']) : '';
				$this->list->{$user_id}->stars 		= $this->parent->stars->get_count($user_id);
				$this->list->{$user_id}->can_spam 	= isset($meta[$this->parent->_base . '_can_spam']) ? $meta[$this->parent->_base . '_can_spam'] : 'false';
				$this->list->{$user_id}->notify 	= isset($meta[$this->parent->_base . 'notify']) ? $meta[$this->parent->_base . 'notify'] : $this->get_user_notification_settings($user_id);
				$this->list->{$user_id}->sent 		= isset($meta[$this->parent->_base . '_email_sent']) ? $meta[$this->parent->_base . '_email_sent'] : '';
				$this->list->{$user_id}->referredBy	= isset($meta[$this->parent->_base . 'referredBy']) ? $meta[$this->parent->_base . 'referredBy'] : '';

				// user marketing channel
				
				$terms = wp_get_object_terms( $user_id, 'marketing-channel' );		
				$this->list->{$user_id}->channel = ( ( !isset($terms->errors) && isset($terms[0]->name) ) ? $terms[0]->name : '');
			}
			
			$user_role 	= $this->list->{$user_id}->role;
			$user_seen 	= $this->list->{$user_id}->last_seen;
			$user_agent	= $this->list->{$user_id}->last_uagent;
			$user_stars	= $this->list->{$user_id}->stars;
			$can_spam  	= $this->list->{$user_id}->can_spam;
			$notify  	= $this->list->{$user_id}->notify;
			$last_sent 	= $this->list->{$user_id}->sent;
			$referredBy	= $this->list->{$user_id}->referredBy;
			$channel   	= $this->list->{$user_id}->channel;
			
			$search_terms = ( !empty($_REQUEST['s']) ? $_REQUEST['s'] : '' );
			
			$row='';

			if ($column_name == "seen") {
				
				if( !empty($user_agent) ){
				
					$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
					
						$row .= $this->get_browser($user_agent);
						
					$row .= '</span>';
				}
				
				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
				
					$row .= $this->time_ago( '@' . $user_seen );
					
				$row .= '</span>';
			}
			elseif ($column_name == "channel") {
				
				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
					
					if(!empty($referredBy)){
						
						$row .= '<a href="'.admin_url( 'user-edit.php' ).'?user_id='.key($referredBy).'">'.reset($referredBy).'</a>';
					}
					else{
						
						$row .= $channel;
					}
				
				$row .= '</span>';
			}
			elseif ($column_name == "stars") {
				
				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
					
					$row .= $user_stars;
					
				$row .= '</span>';
			}
			elseif ($column_name == "notify") {
				
				$notify = array_merge($this->parent->email->get_notification_settings(),$notify);
				
				foreach( $notify as $channel => $can_notify ){
					
					$row .= '<div style="font-size:11px;text-align:left;">';
						
						$channel_name = ucfirst($channel);
						
						if( $can_notify != 'true' ){
							
							$text = "<img loading='lazy' class='lazy' data-original='" . $this->parent->assets_url . "/images/wrong_arrow.png' width=15 height=15>" . $channel_name;
							$row .= "<a title=\"Subscribe to ".$channel_name."\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ltple_notify"), "ltple_notify[".$channel."]" => "true" , "ltple_view" => $this->view, "s" => $search_terms ), get_admin_url() . "users.php") . "\">" . apply_filters("ltple_manual_notify", $text) . "</a>";
						}
						else{
							
							$text = "<img class='lazy' data-original='" . $this->parent->assets_url . "/images/right_arrow.png' width=15 height=15>" . $channel_name;
							$row .= "<a title=\"Unsubscribe from ".$channel_name."\" href=\"" . add_query_arg(array("user_id" => $user_id, "wp_nonce" => wp_create_nonce("ltple_notify"), "ltple_notify[".$channel."]" => "false" , "ltple_view" => $this->view, "s" => $search_terms ), get_admin_url() . "users.php") . "\">" . apply_filters("ltple_manual_notify", $text) . "</a>";
						}

					$row .= '</div>';
				}
			}
			elseif ($column_name == "sent") {
				
				$emails = json_decode($last_sent,true);

				if( !empty( $emails ) ){
					
					$emails = array_slice($emails, 0, 10);					
					
					$row .= '<pre style="margin:0px;padding:0px;font-size: 10px;line-height: 14px;overflow:hidden;background:transparent;border:none;">';

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

		public function get_customers_table_row($val, $column_name, $user_id) {
			
			if(!isset($this->list->{$user_id})){
				
				$meta = $this->get_all_user_meta($user_id);
				
				$this->list->{$user_id} = new stdClass();
				
				$this->list->{$user_id}->role 		= get_userdata($user_id);
				$this->list->{$user_id}->plan 		= $this->parent->plan->get_user_plan_info( $user_id, true );
				$this->list->{$user_id}->period		= $this->parent->plan->get_license_period_end($user_id);
				$this->list->{$user_id}->last_seen 	= isset($meta[$this->parent->_base . '_last_seen']) ? $meta[$this->parent->_base . '_last_seen'] : '';
				$this->list->{$user_id}->last_uagent= isset($meta[$this->parent->_base . '_last_uagent']) ? $this->get_browser($meta[$this->parent->_base . '_last_uagent']) : '';
				$this->list->{$user_id}->stars 		= $this->parent->stars->get_count($user_id);
				$this->list->{$user_id}->referredBy	= isset($meta[$this->parent->_base . 'referredBy']) ? $meta[$this->parent->_base . 'referredBy'] : '';

				// user marketing channel
				
				$terms = wp_get_object_terms( $user_id, 'marketing-channel' );		
				$this->list->{$user_id}->channel = ( ( !isset($terms->errors) && isset($terms[0]->name) ) ? $terms[0]->name : '');
			}
			
			$user_role 	= $this->list->{$user_id}->role;
			$user_plan 	= $this->list->{$user_id}->plan;
			$user_seen 	= $this->list->{$user_id}->last_seen;
			$user_agent	= $this->list->{$user_id}->last_uagent;
			$user_stars	= $this->list->{$user_id}->stars;
			$referredBy	= $this->list->{$user_id}->referredBy;
			$channel   	= $this->list->{$user_id}->channel;
			$period_end = $this->list->{$user_id}->period;
			
			$search_terms = ( !empty($_REQUEST['s']) ? $_REQUEST['s'] : '' );
			
			$row='';
			
			if ($column_name == "subscription") { 

				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';	

					if( $user_plan['info']['total_fee_amount'] > 0 ){
						
						$row .= htmlentities(' ').$user_plan['info']['total_price_currency'].$user_plan['info']['total_fee_amount'].' '.$user_plan['info']['total_fee_period'];
						$row .= '<br>+';
					}
					
					$row .= $user_plan['info']['total_price_currency'].$user_plan['info']['total_price_amount'].'/'.$user_plan['info']['total_price_period'];
					
				$row .= '</span>';

				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';	
					
					if( !empty($period_end) ){
						
						$days = floor($this->parent->plan->get_license_remaining_days($period_end));

						$row .= $days . ' ' . ( ($days == 1 || $days == -1) ? 'day' : 'days' ) ;					
					
						if( $days < -30 ){
							
							$this->parent->plan->flush_user_plan($user_id);
						}					
					}
					else{
		
						$row .= $period_end . ' days';
					}
					
				$row .= '</span>';
				
				$update_period_url  = add_query_arg(array_merge(array('ltple_update_period'=>$user_id),$_REQUEST),$this->parent->urls->current);
				
				$row .= '<a href="'.$update_period_url.'">';
				
					$row .= "<img loading='lazy' src='" . $this->parent->assets_url . "images/send.png' width=25 height=25>";
				
				$row .= '</a>';
			}
			elseif ($column_name == "plan") {
					
				if( $user_plan['id'] > 0 ){
					
					$row .= '<textarea style="margin:0px;padding:5px;font-size:10px;line-height:14px;height:70px;width:100px;">';

					foreach($user_plan['taxonomies'] as $taxonomy => $tax){
						
						foreach($tax['terms'] as $term){
							
							if($term['has_term']){
								
								$row .= $term['name'].PHP_EOL;
							}
						}
					}

					$row .= '</textarea>';
				}
			}
			elseif ($column_name == "seen") {
				
				if( !empty($user_agent) ){
				
					$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
					
						$row .= $this->get_browser($user_agent);
						
					$row .= '</span>';
				}
				
				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
				
					$row .= $this->time_ago( '@' . $user_seen );
					
				$row .= '</span>';
			}
			elseif ($column_name == "channel") {
				
				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
					
					if(!empty($referredBy)){
						
						$row .= '<a href="'.admin_url( 'user-edit.php' ).'?user_id='.key($referredBy).'">'.reset($referredBy).'</a>';
					}
					else{
						
						$row .= $channel;
					}
				
				$row .= '</span>';
			}
			elseif ($column_name == "stars") {
				
				$row .= '<span style="width:100%;display:block;margin: 0px;font-size: 10px;line-height: 14px;">';
					
					$row .= $user_stars;
					
				$row .= '</span>';
			}
			
			return $row;
		}
		
		public function get_user_notification_settings( $user_id ){
			
			if( !$notify = get_user_meta($user_id, $this->parent->_base . 'notify',true) ){
				
				if( !$last_seen = get_user_meta($user_id, $this->parent->_base . '_last_seen',true)){
					
					$can_spam = 'false';
				}
				elseif ( !$can_spam = get_user_meta($user_id, $this->parent->_base . '_can_spam',true) ){
					
					$can_spam = 'false';
				}
				
				$notify = array('series' => $can_spam);
			}
			
			// normalize default notification settings
			
			$notification_settings = $this->parent->email->get_notification_settings();
			
			foreach( $notification_settings as $key => $value ){
				
				if( empty($notify[$key]) ){
					
					$notify[$key] = $value;
				}
			}
			
			return $notify;
		}
		
		public function update_user_manually() {
			
			if( isset($_REQUEST["user_id"]) && isset($_REQUEST["wp_nonce"]) ) {
				
				$notify = array();
				
				if( wp_verify_nonce($_REQUEST["wp_nonce"], "ltple_can_spam") && isset($_REQUEST["ltple_can_spam"]) ){
					
					if($_REQUEST["ltple_can_spam"] === 'true' || $_REQUEST["ltple_can_spam"] === 'false'){
						
						$notify = $this->get_user_notification_settings($_REQUEST["user_id"]);				
						
						$notify['series'] = $_REQUEST["ltple_can_spam"];
					}
				}
				elseif( wp_verify_nonce($_REQUEST["wp_nonce"], "ltple_notify") && isset($_REQUEST["ltple_notify"]) && is_array($_REQUEST["ltple_notify"]) ){
					
					$notify = $this->get_user_notification_settings($_REQUEST["user_id"]);
					
					$notify = array_merge($notify,$_REQUEST["ltple_notify"]);
				}
				
				if( !empty($notify) ){
					
					update_user_meta($_REQUEST["user_id"], $this->parent->_base . '_can_spam', $notify['series']);
					
					update_user_meta($_REQUEST["user_id"], $this->parent->_base . 'notify', $notify);					
				}
			}
		}
		
		public function add_bulk_actions() {
		 
			?>
			
			<script type="text/javascript">
			
				jQuery(document).ready(function() {
					  					
					// append to top dropdown
					jQuery('<option>').val('export-emails').text('<?php _e('Export emails')?>').appendTo("select[name='action']");
					
					// append to bottom dropdown
					jQuery('<option>').val('export-emails').text('<?php _e('Export emails')?>').appendTo("select[name='action2']");
				
					// switch method to get
					jQuery('form').attr('method','get');  
					 
					// select all users
					jQuery('#cb-select-all-1').click( function(){
						
						if( jQuery(this).is(':checked') ){
						   
							if( !jQuery('#cb-select-all-3').length ){
								
								var items = jQuery('.displaying-num').first().text();
								
								jQuery('<caption id="cb-select-all-3" style="text-align:left;margin-top:-47px;padding:15px 5px;">').html('<input type="checkbox" name="selectAll" /> Select <b>' + items + '</b>' ).prependTo(".wp-list-table");
							}
							else{
								
								jQuery('#cb-select-all-3').show();
							}
						}
						else{
							
							if( jQuery('#cb-select-all-3').length ){
								
								jQuery('#cb-select-all-3 input').attr('checked', false);
								
								jQuery('#cb-select-all-3').hide();
							}
						}
					});
				});
			
			</script>
			<?php
		}

		public function load_bulk_action() {
		 
			// get the action
			$wp_list_table = _get_list_table('WP_Posts_List_Table');
			$action = $wp_list_table->current_action();
			$sendback = '';
			
			// security check
			//check_admin_referer('bulk-users');
			
			//echo'<pre>';var_dump($_POST);exit;
			
			switch($action) {
			
				case 'export-emails':
				
					// if we set up user permissions/capabilities, the code might look like:
					//if ( !current_user_can($post_type_object->cap->export_post, $post_id) )
					//  pp_die( __('You are not allowed to export this post.') );
				 
					$exported = 0;
					
					if( !empty($_REQUEST['selectAll']) ){

						$users = new WP_User_Query(array('fields'=>array('user_email','user_nicename')));
					}
					elseif( !empty($_REQUEST['users']) ){
						
						$user_ids = $_REQUEST['users'];
						
						$users = new WP_User_Query(array(
						
							'include' 	=> $user_ids,
							'fields'	=> array('user_email','user_nicename'),
						));
					}
					
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
					//$sendback = add_query_arg( array( 'exported' => $exported, 'ltple_view' => $_REQUEST['ltple_view'] ), $sendback );		
				
				break;
				default:
					
					// custom bulk actions
					
					$this->bulk_schedule_email_model();
					$this->bulk_add_plan();
					//$this->bulk_add_type();
					$this->bulk_add_range();
					$this->bulk_add_option();
					$this->bulk_add_stars();					
					
					if( !empty($_GET['users']) ){

						//redirect url
						
						$redirect_url = remove_query_arg( array(
						
							'users',
							'_wp_http_referer',
							
						),$this->parent->urls->current);
					
						wp_redirect($redirect_url);
						exit;					
					}
					
				return;
			}
		 
			// redirect client
			//wp_redirect($sendback);
		 
			exit();
		}
		
		public function get_filter_value($filter) {
			
			$value=null;
			
			if ( isset( $_REQUEST[$filter.'1'] ) && $_REQUEST[$filter.'1'] != '-1' ) {
				
				$value = $_REQUEST[$filter.'1'];
			}
			elseif ( isset( $_REQUEST[$filter.'2'] ) && $_REQUEST[$filter.'2'] != '-1' ) {
				
				$value = $_REQUEST[$filter.'2'];
			}

			return $value;
		}

		public function filter_users_by_role( $query ) {

			if( !empty($_REQUEST['role']) ){
				
				$query->set( 'role', $_REQUEST['role']);
			}
			
			return $query;
		}
		
		public function filter_users_by_email_subscription( $query ) {
			
			if( $this->view == 'newsletter' ){
				
				$meta_query = array (
					
					array(
					
						'key' 		=> $this->parent->_base . '_last_seen',
						'compare'	=> 'EXISTS', 
					),
					array(
					
						'key'     	=> $this->parent->_base . '_can_spam',
						'compare' 	=> '=',
						'value'		=> 'true'
					),
				);
					
				if( !empty($query->query_vars['meta_query']) ){
					
					$meta_query = array_merge($meta_query,$query->query_vars['meta_query']);
				}
				
				$query->set( 'meta_query', $meta_query);
			}
			
			return $query;
		}
		
		public function filter_users_by_marketing_channel( $query ) {
			
			$taxonomy = 'marketing-channel';

			if( $term_id = $this->get_filter_value($taxonomy) ){
				
				// alter the user query to add my meta_query
				
				$ids =  array( intval($term_id) );
				
				if( $children = get_term_children($term_id,$taxonomy) ){
				
					$ids =  array_merge($ids,$children);
				}
				
				$users = get_objects_in_term($ids, $taxonomy );
				
				if( !empty($users) ){
					
					$query->set( 'include', $users);
				}
				else{
					
					$query->set( 'meta_key', 'something-that-doesnt-exists' ); //to return NULL instead of all
				}
			}
			
			return $query;
		}
		
		public function filter_users_by_plan_value( $query ) {
			
			if( $this->view == 'customers' ){

				$meta_query = array (
					
					array(
							
						'key'     	=> 'has_subscription',
						'compare' 	=> '=',
						'value'		=> 'true',
					),
					/*
					array(
					
						'key'     	=> $this->parent->_base . 'period_end',
						'compare' 	=> '<',
						'value'		=> strtotime('-0 days'), // for debugging
					),
					*/
				);
				
				if( isset($_REQUEST['licenseStatus1']) ){
					
					if( $_REQUEST['licenseStatus1'] == 'active' ){
						
						$meta_query[] = array(
					
							'key'     	=> $this->parent->_base . 'period_end',
							'compare' 	=> '>',
							'value'		=> strtotime('-0 days'), // exclude past due
						);
					}
					elseif( $_REQUEST['licenseStatus1'] == 'due' ){
						
						$meta_query[] = array(
					
							'key'     	=> $this->parent->_base . 'period_end',
							'compare' 	=> '<',
							'value'		=> strtotime('-1 days'), // exclude active
						);						
					}
				}
					
				if( !empty($query->query_vars['meta_query']) ){
					
					$meta_query = array_merge($meta_query,$query->query_vars['meta_query']);
				}

				$query->set( 'meta_query', $meta_query);
				$query->set( 'meta_key', $this->parent->_base . 'period_end');
			}
			
			return $query;
		}
		
		/*
		public function bulk_send_email_model() {
			
			$post_type = 'email-model';
			$model_id=null;
			
			if ( isset( $_REQUEST[$post_type.'1'] ) && is_numeric( $_REQUEST[$post_type.'1'] ) && $_REQUEST[$post_type.'1'] != '-1' ) {
				
				$model_id=intval($_REQUEST[$post_type.'1']);
			}
			elseif ( isset( $_REQUEST[$post_type.'2'] ) && is_numeric( $_REQUEST[$post_type.'2'] ) && $_REQUEST[$post_type.'2'] != '-1' ) {
				
				$model_id=intval($_REQUEST[$post_type.'2']);
			}
			
			if( !is_null( $model_id ) && !empty($_REQUEST['users']) && is_array($_REQUEST['users'])){
				
				$this->email_sent	  = 0;
				$this->email_not_sent = 0;
				
				foreach( $_REQUEST['users'] as $user_id){
					
					$user = get_userdata($user_id);
				
					if($this->parent->email->send_model( $model_id, $user)){
						
						++$this->email_sent;	
					}
					else{
						
						++$this->email_not_sent;
					}
				}

				add_action( 'admin_notices', array( $this, 'output_send_email_admin_notice'));				
			}
		}
		*/
		
		public function get_all_selected_users( $field, $s = '', $meta_query = array() ) {
			
			$selected_users = array();
			
			$args = array( 
				
				'fields' 		=> array($field),
				'meta_query' 	=> $meta_query,
			);
			
			if( !empty($s) ){
				
				$args['search'] = '*'.$s.'*';
				
				$args['search_columns'] = array(
					
					'user_login',
					'user_nicename',
					'user_email',
				);
			}
			
			if( $users = new WP_User_Query($args)){
				
				if( !empty($users->results) ){
					
					foreach( $users->results as $user){
						
						$selected_users[] = $user->{$field};
					}
				}
			}

			return $selected_users;
		}
		
		public function delete_user_manually(){
			
			if( !empty($_REQUEST['action']) && !empty($_REQUEST['users']) && is_array($_REQUEST['users']) ){
				
				if( $_REQUEST['action'] == 'delete' || ( $_REQUEST['action'] == 'dodelete' && $_REQUEST['delete_option'] == 'delete' ) ){
					
					if( current_user_can('delete_users') ){
						
						$ids = implode(',',$_REQUEST['users']);
						
						global $wpdb;
						
						$wpdb->get_results('DELETE FROM ' . $wpdb->prefix . 'users WHERE ID in('. $ids . ')');

						$wpdb->get_results('DELETE FROM ' . $wpdb->prefix . 'usermeta WHERE user_id in('. $ids . ')');
					}
				}
			}
		}
		
		public function delete_user( $user_id ){
			
			if( current_user_can('delete_users') ){
			
				global $wpdb;
						
				$wpdb->get_results('DELETE FROM ' . $wpdb->prefix . 'users WHERE ID in('. $user_id . ')');

				$wpdb->get_results('DELETE FROM ' . $wpdb->prefix . 'usermeta WHERE user_id in('. $user_id . ')');
			}
		}
			
		public function bulk_schedule_email_model() {
			
			$post_type 	= 'email-model';
			$model_id 	= null;
			
			if ( isset( $_REQUEST[$post_type.'1'] ) && is_numeric( $_REQUEST[$post_type.'1'] ) && $_REQUEST[$post_type.'1'] != '-1' ) {
				
				$model_id = intval($_REQUEST[$post_type.'1']);
			}
			elseif ( isset( $_REQUEST[$post_type.'2'] ) && is_numeric( $_REQUEST[$post_type.'2'] ) && $_REQUEST[$post_type.'2'] != '-1' ) {
				
				$model_id = intval($_REQUEST[$post_type.'2']);
			}
			
			if( $model_title = get_post_field( 'post_title', $model_id ) ){

				//get email title
				
				$model_title = $this->parent->email->get_title($model_title);
				
				// get email slug
				
				$model_slug = sanitize_title($model_title);
				
				$users 	= array();

				if( !empty($_REQUEST['selectAll']) ){
					
					$s = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';

					$users = $this->get_all_selected_users('id',$s,array(
					
						array(
						
							'key' 		=> $this->parent->_base . '_email_sent',
							'value' 	=> $model_slug,
							'compare' 	=> 'NOT LIKE',
						),
					));
				}
				elseif( !empty($_REQUEST['users']) && is_array($_REQUEST['users']) ){
					
					$users = $_REQUEST['users'];
				}
				
				if( !empty($users) ){
					
					$max_users = 10;

					// prepare user list

					$users = array_chunk($users,$max_users);
		
					//get time limit
					
					$max_execution_time = ini_get('max_execution_time'); 
					
					//remove time limit

					set_time_limit(0);				
				
					$m = 0;
				
					foreach( $users as $i => $user_ids){

						wp_schedule_single_event( ( time() + ( 60 * $m ) ) , $this->parent->_base . 'bulk_send_email_event' , [$model_id,$user_ids] );
					
						if ($i % 10 == 0) {
							
							++$m;
						}
					}
					
					//reset time limit
					
					set_time_limit($max_execution_time);
				}
				
				add_action( 'admin_notices', array( $this, 'output_schedule_email_admin_notice'));
			}
		}
		
		public function bulk_add_plan() {
			
			$post_type 	= 'subscription-plan';
			
			if ( isset( $_REQUEST[$post_type.'1'] ) && is_numeric( $_REQUEST[$post_type.'1'] ) && $_REQUEST[$post_type.'1'] != '-1' ) {
				
				$plan_id = intval($_REQUEST[$post_type.'1']);
			}
			elseif ( isset( $_REQUEST[$post_type.'2'] ) && is_numeric( $_REQUEST[$post_type.'2'] ) && $_REQUEST[$post_type.'2'] != '-1' ) {
				
				$plan_id = intval($_REQUEST[$post_type.'2']);
			}

			if( !empty($plan_id) ){
				
				$users 	= array();

				if( !empty($_REQUEST['selectAll']) ){
					
					$s = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';
					
					$users = $this->get_all_selected_users('id',$s);
				}
				elseif( !empty($_REQUEST['users']) && is_array($_REQUEST['users']) ){
					
					$users = $_REQUEST['users'];
				}

				if(  !empty($users) ){
		
					//get time limit
					
					$max_execution_time = ini_get('max_execution_time'); 
					
					//remove time limit
					
					set_time_limit(0);				
				
					$m = 0;
				
					$this->parent->plan->bulk_update_user_plan($users,$plan_id);
					
					//reset time limit
					
					set_time_limit($max_execution_time);
					
					add_action( 'admin_notices', array( $this, 'output_add_plan_notice'));
				}
			}
		}

		public function bulk_add_type() {
			
			$taxonomy 	= 'layer-type';
			
			if ( isset( $_REQUEST[$taxonomy.'1'] ) && is_numeric( $_REQUEST[$taxonomy.'1'] ) && $_REQUEST[$taxonomy.'1'] != '-1' ) {
				
				$term_id = intval($_REQUEST[$taxonomy.'1']);
			}
			elseif ( isset( $_REQUEST[$taxonomy.'2'] ) && is_numeric( $_REQUEST[$taxonomy.'2'] ) && $_REQUEST[$taxonomy.'2'] != '-1' ) {
				
				$term_id = intval($_REQUEST[$taxonomy.'2']);
			}

			if( !empty($term_id) ){
				
				$users 	= array();

				if( !empty($_REQUEST['selectAll']) ){
					
					$s = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';
					
					$users = $this->get_all_selected_users('id',$s);
				}
				elseif( !empty($_REQUEST['users']) && is_array($_REQUEST['users']) ){
					
					$users = $_REQUEST['users'];
				}

				if( !empty($users) ){
					
					//get time limit
					
					$max_execution_time = ini_get('max_execution_time'); 
					
					//remove time limit
					
					set_time_limit(0);				
				
					$m = 0;
					
					$this->parent->plan->bulk_update_user_type($users,$term_id);
					
					add_action( 'admin_notices', array( $this, 'output_add_type_notice'));
				
					//reset time limit
					
					set_time_limit($max_execution_time);				
				}
			}
		}
		
		public function bulk_add_range() {
			
			$taxonomy 	= 'layer-range';

			if ( isset( $_REQUEST[$taxonomy.'1'] ) && is_numeric( $_REQUEST[$taxonomy.'1'] ) && $_REQUEST[$taxonomy.'1'] != '-1' ) {
				
				$term_id = intval($_REQUEST[$taxonomy.'1']);
			}
			elseif ( isset( $_REQUEST[$taxonomy.'2'] ) && is_numeric( $_REQUEST[$taxonomy.'2'] ) && $_REQUEST[$taxonomy.'2'] != '-1' ) {
				
				$term_id = intval($_REQUEST[$taxonomy.'2']);
			}

			if( !empty($term_id) ){
				
				$users 	= array();

				if( !empty($_REQUEST['selectAll']) ){
					
					$s = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';
					
					$users = $this->get_all_selected_users('id',$s);;
				}
				elseif( !empty($_REQUEST['users']) && is_array($_REQUEST['users']) ){
					
					$users = $_REQUEST['users'];
				}

				if( !empty($users) ){
		
					//get time limit
					
					$max_execution_time = ini_get('max_execution_time'); 
					
					//remove time limit
					
					set_time_limit(0);				
				
					$m = 0;
					
					$this->parent->plan->bulk_update_user_range($users,$term_id);
					
					add_action( 'admin_notices', array( $this, 'output_add_range_notice'));
				
					//reset time limit
					
					set_time_limit($max_execution_time);
				}
			}			
		}
		
		public function bulk_add_option() {
			
			$taxonomy 	= 'account-option';
			
			if ( isset( $_REQUEST[$taxonomy.'1'] ) && is_numeric( $_REQUEST[$taxonomy.'1'] ) && $_REQUEST[$taxonomy.'1'] != '-1' ) {
				
				$term_id = intval($_REQUEST[$taxonomy.'1']);
			}
			elseif ( isset( $_REQUEST[$taxonomy.'2'] ) && is_numeric( $_REQUEST[$taxonomy.'2'] ) && $_REQUEST[$taxonomy.'2'] != '-1' ) {
				
				$term_id = intval($_REQUEST[$taxonomy.'2']);
			}

			if( !empty($term_id) ){
				
				$users 	= array();

				if( !empty($_REQUEST['selectAll']) ){
					
					$s = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';
					
					$users = $this->get_all_selected_users('id',$s);
				}
				elseif( !empty($_REQUEST['users']) && is_array($_REQUEST['users']) ){
					
					$users = $_REQUEST['users'];
				}

				if( !empty($users) ){
		
					//get time limit
					
					$max_execution_time = ini_get('max_execution_time'); 
					
					//remove time limit
					
					set_time_limit(0);				
				
					$m = 0;
					
					$this->parent->plan->bulk_update_user_option($users,$term_id);
					
					add_action( 'admin_notices', array( $this, 'output_add_option_notice'));
				
					//reset time limit
					
					set_time_limit($max_execution_time);				
				}
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
		
		public function output_schedule_email_admin_notice(){
			
			echo'<div class="notice notice-success">';
			
				echo'<p>';
				
					echo 'Email(s) have been succesfully scheduled';
					
				echo'</p>';
				
			echo'</div>';
		}	

		public function output_add_plan_notice(){
			
			echo'<div class="notice notice-success">';
			
				echo'<p>';
				
					echo 'Plan(s) succesfully added';
					
				echo'</p>';
				
			echo'</div>';
		}

		public function output_add_type_notice(){
			
			echo'<div class="notice notice-success">';
			
				echo'<p>';
				
					echo 'Layer type succesfully added';
					
				echo'</p>';
				
			echo'</div>';
		}
		
		public function output_add_range_notice(){
			
			echo'<div class="notice notice-success">';
			
				echo'<p>';
				
					echo 'Layer range succesfully added';
					
				echo'</p>';
				
			echo'</div>';
		}

		public function output_add_option_notice(){
			
			echo'<div class="notice notice-success">';
			
				echo'<p>';
				
					echo 'Account option succesfully added';
					
				echo'</p>';
				
			echo'</div>';
		}			
		
		public function bulk_add_stars() {
			
			$field = 'addStars';
			$addStars=0;
			
			if ( isset( $_REQUEST[$field.'1'] ) && is_numeric( $_REQUEST[$field.'1'] ) ) {
				
				$addStars = floatval($_REQUEST[$field.'1']);
			}
			elseif ( isset( $_REQUEST[$field.'2'] ) && is_numeric( $_REQUEST[$field.'2'] ) ) {
				
				$addStars = floatval($_REQUEST[$field.'2']);
			}
			
			if( is_numeric( $addStars ) && !empty($_REQUEST['users']) && is_array($_REQUEST['users'])){
				
				$users = array();
				
				if( !empty($_REQUEST['selectAll']) ){
					
					$s = !empty($_REQUEST['s']) ? $_REQUEST['s'] : '';
					
					$users = $this->get_all_selected_users('id',$s);
				}
				elseif( !empty($_REQUEST['users']) && is_array($_REQUEST['users']) ){
					
					$users = $_REQUEST['users'];
				}
				
				$this->stars_added = $addStars;
				
				foreach( $users as $user_id){
					
					$this->parent->stars->add_stars( $user_id, $addStars );
				}

				add_action( 'admin_notices', array( $this, 'output_stars_added_notice'));						
			}
		}
		
		public function output_stars_added_notice(){
			
			if( $this->stars_added > 0 ){
				
				echo'<div class="notice notice-success">';
				
					echo'<p>';
					
						echo $this->stars_added .' stars added';
						
					echo'</p>';
					
				echo'</div>';
			}			
		}
		
		public function ref_users_bulk_register(){
					
			if( !empty($this->parent->email->imported['imported']) ){	
					
				$this->referent = $this->parent->user;
					
				foreach( $this->parent->email->imported['imported'] as $user ){

					if( $referral = $this->parent->users->set_ref_user( $user['id'], $this->referent ) ){
						
						$this->parent->users->referrals[] = $referral;
					}
				}
				
				if( !empty($this->parent->users->referrals) ){
				
					do_action('ltple_ref_users_bulk_added');
				}
			}
		}	
		
		public function ref_user_register( $user_id ){
					
			if( is_numeric( $this->parent->request->ref_id ) ){
				
				// get referent data

				if( $this->referent = get_userdata( $this->parent->request->ref_id ) ){
					
					if( $this->referral = $this->parent->users->set_ref_user( $user_id, $this->referent ) ){
						
						//add referral stars
						
						/** 
							we dont use do_action here
							because all hooks are attached to the current id
							and we want the referral id to be credited
						**/
						
						$this->parent->stars->add_stars( $this->referent->ID, $this->parent->_base . 'ltple_referred_registration_stars' );
						
						do_action('ltple_ref_user_added');					
					}
				}
			}
		}
		
		public function set_ref_user( $user_id, $referent ){

			// get referral info

			if( $referral = get_userdata($user_id) ){
				
				//set marketing channel
				
				$this->parent->channels->update_user_channel($user_id,'Friend Recommendation');
		
				if( !empty($referent->ID) ){
			
					//assign referent to referral
					
					update_user_meta( $referral->ID, $this->parent->_base . 'referredBy', [ $referent->ID => $referent->user_login ] );
					
					//assign referral to referent
					
					$referrals = get_user_meta($referent->ID,$this->parent->_base . 'referrals', true);
					
					if( !is_array($referrals) ) {
						
						$referrals = [];
					}
					else{
						
						foreach( $referrals as $key => $val){
							
							if(!is_string($val)){
								
								unset($referrals[$key]);
							}
						}
					}

					$referrals[$referral->ID] = $referral->user_login;
					
					update_user_meta( $referent->ID, $this->parent->_base . 'referrals', $referrals );

					return $referral;
				}
			}	
			
			return false;
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
	