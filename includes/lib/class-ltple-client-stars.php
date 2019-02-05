<?php

	if ( ! defined( 'ABSPATH' ) ) exit;

	class LTPLE_Client_Stars {
		
		var $parent;
		var $triggers;
		
		/**
		 * Constructor function
		 */
		 
		public function __construct ( $parent ) {

			$this->parent 	= $parent;
			
			$this->get_triggers();
			
			if( !empty($this->triggers) ){
				
				foreach( $this->triggers as $group => $trigger ){
					
					foreach($trigger as $key => $data){
						
						add_action( $key, array( $this,  'add_triggered_stars') );
					}
				}
			}
			
			add_action( 'show_user_profile', array( $this, 'get_user_stars' ),2,10 );
			add_action( 'edit_user_profile', array( $this, 'get_user_stars' ) );
			
			add_action( 'edit_user_profile_update', array( $this, 'save_user_stars' ) );
		
			add_shortcode('ltple-client-ranking', array( $this , 'get_ranking_shortcode' ) );
		}
		
		public function get_ranking_shortcode(){
			
			if( $this->parent->settings->options->enable_ranking == 'on' ){
			
				include($this->parent->views . '/navbar.php');
			
				include($this->parent->views . '/ranking.php');	
			}
		}
		
		public function get_triggers(){
			
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			
			$this->triggers = array();

			// register & login triggers
			
			$this->triggers['register & login']['user_register'] = array(
					
				'description' => 'when you register for the first time'
			);
			
			$this->triggers['register & login']['ltple_referred_click'] = array(
					
				'description' => 'when someone click on your referral url (daily unique IPs)'
			);			
			
			$this->triggers['register & login']['ltple_referred_registration'] = array(
					
				'description' => 'when someone register after visiting your referral url'
			);
			
			$this->triggers['register & login']['ltple_first_log_today'] = array(
					
				'description' => 'when you login for the first time in a day'
			);
			
			$this->triggers['register & login']['ltple_first_ref_log_today'] = array(
					
				'description' => 'when one of your referrals login for the first time in a day'
			);			
			
			// plan subscription
			
			$this->triggers['plan subscription']['ltple_free_plan_subscription'] = array(
					
				'description' => 'when you subscribe to a demo plan'
			);

			$this->triggers['plan subscription']['ltple_paid_plan_subscription'] = array(
				
				'description' => 'when you subscribe to a pro plan'
			);			
			
			// connected apps triggers
			
			$this->triggers['connected apps']['ltple_new_app_connected'] = array(
					
				'description' => 'when you connect any new App'
			);
			
			// forum triggers
			
			if( is_plugin_active('wpforo/wpforo.php') ){
				
				$this->triggers['forum interaction']['wpforo_after_add_topic'] = array(
						
					'description' => 'when you start a new topic on the forum'
				);

				$this->triggers['forum interaction']['wpforo_after_add_post'] = array(
						
					'description' => 'when you post a message on a forum topic'
				);						
			}
			elseif( is_plugin_active('bbpress/bbpress.php') ){
				
				$this->triggers['forum interaction']['bbp_new_topic'] = array(
						
					'description' => 'when you start a new topic on the forum'
				);

				$this->triggers['forum interaction']['bbp_new_reply'] = array(
						
					'description' => 'when you reply to a forum topic'
				);					
			}

			return true;
		}

		public function get_count( $user_id = null ){
			
			if( !is_numeric($user_id) ){
				
				$user_id = $this->parent->user->ID;
			}
			
			$stars = get_user_meta( $user_id, $this->parent->_base . 'stars', true );
			
			if(is_numeric($stars)){
				
				$stars = intval($stars);
			}
			else{
				
				// set first count
				
				$stars = 0;
				
				// set stars
				
				update_user_meta( $user_id, $this->parent->_base . 'stars', $stars );
			}
			
			if( $stars === 0 ){
				
				// TODO update stars
				
			}
			
			return $stars;
		}
		
		public function add_stars( $user_id, $stars ){
			
			if( is_numeric($user_id) ){
			
				if( !is_numeric($stars) ){
					
					$stars = get_option($stars);
				}
				
				if( is_numeric($stars) ){
					
					// get user stars
					
					$user_stars = $this->get_count( $user_id );
					
					$user_stars = $user_stars + $stars;
					
					// update user stars

					update_user_meta( $user_id, $this->parent->_base . 'stars', $user_stars );
					
					if( $user_id == $this->parent->user->ID){
						
						// set current user stars
						
						$this->parent->user->stars = $user_stars;
					}
				}
			}			
		}
		
		public function add_triggered_stars( $user_id = null ){
			
			if( !is_numeric($user_id) ){
				
				$user_id = $this->parent->user->ID;
			}			
			
			$option_name = $this->parent->_base . current_filter().'_stars';

			$this->add_stars( $user_id, $option_name );
		}
			
		public function get_user_stars( $user ) {
			
			if( current_user_can( 'administrator' ) ){
				
				echo '<div class="postbox" style="min-height:45px;">';
					
					echo '<h3 style="margin:10px;width:300px;display: inline-block;">' . __( 'Stars', 'live-template-editor-client' ) . '</h3>';
					
					echo '<div style="display:inline-block;">';
						
						$this->parent->admin->display_field(array(
				
							'id' 			=> $this->parent->_base . 'stars',
							'description' 	=> '',
							'type'			=> 'number',
							'placeholder'	=> 'stars',
						), $user );
							
					echo'</div>';
						
				echo'</div>';
			}	
		}
		
		public function save_user_stars( $user_id ) {
			
			if( current_user_can( 'administrator' ) ){
				
				$field = $this->parent->_base . 'stars';
				
				if( isset($_REQUEST[$field]) && is_numeric($_REQUEST[$field]) ){
					
					$user_stars = floatval($_REQUEST[$field]);
					
					// update user stars
					
					update_user_meta( $user_id, $field, $user_stars );
				}
			}	
		}
		
		/**
		 * Main LTPLE_Client_Stars Instance
		 *
		 * Ensures only one instance of LTPLE_Client_Stars is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see LTPLE_Client()
		 * @return Main LTPLE_Client_Stars instance
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
	