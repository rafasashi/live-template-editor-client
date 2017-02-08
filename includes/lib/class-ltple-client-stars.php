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
			
			$this->triggers = $this->get_triggers();
			
			if( !empty($this->triggers) ){
				
				foreach( $this->triggers as $group => $trigger ){
					
					foreach($trigger as $key => $data){
						
						add_action( $key, array( $this,  'add_triggered_stars') );
					}
				}
			}
			
			add_action( 'user_register', 	array( $this, 'ref_user_register' ) );
			
			add_action( 'show_user_profile', array( $this, 'get_user_stars' ) );
			add_action( 'edit_user_profile', array( $this, 'get_user_stars' ) );
			
			add_action( 'edit_user_profile_update', array( $this, 'save_user_stars' ) );
		}
		
		public function get_triggers(){
			
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			
			$triggers = array();

			// register & login triggers
			
			$triggers['register & login']['user_register'] = array(
					
				'description' => 'when you register for the first time'
			);
			
			$triggers['register & login']['ltple_referred_registration'] = array(
					
				'description' => 'when someone register after visiting your referral url'
			);
			
			$triggers['register & login']['ltple_first_log_today'] = array(
					
				'description' => 'when you login for the first time in a day'
			);
			
			// plan subscription
			
			$triggers['plan subscription']['ltple_free_plan_subscription'] = array(
					
				'description' => 'when you subscribe to a demo plan'
			);

			$triggers['plan subscription']['ltple_paid_plan_subscription'] = array(
				
				'description' => 'when you subscribe to a pro plan'
			);			
			
			// connected apps triggers
			
			$triggers['connected apps']['ltple_new_app_connected'] = array(
					
				'description' => 'when you connect any new App'
			);
			
			$triggers['connected apps']['ltple_twitter_account_connected'] = array(
					
				'description' => 'when you connect a new Twitter account'
			);
			
			// twitter triggers
			
			$triggers['twitter interaction']['ltple_twitter_dm_sent'] = array(
					
				'description' => 'when you send a DM via the community panel'
			);
			
			// wpforo triggers
			
			if( is_plugin_active('wpforo/wpforo.php') ){
				
				$triggers['forum interaction']['wpforo_after_add_topic'] = array(
						
					'description' => 'when you start a new topic on the forum'
				);

				$triggers['forum interaction']['wpforo_after_add_post'] = array(
						
					'description' => 'when you post a message on a forum topic'
				);						
			}
			
			return $triggers;
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
		
		public function ref_user_register(){
					
			// we dont use do_action here
			// because all hooks are attached to the current id
			// and we want the referral id to be credited

			if( is_numeric( $this->parent->request->ref_id ) && get_userdata( $this->parent->request->ref_id ) ){
				
				//add referral stars
				
				$this->add_stars( $this->parent->request->ref_id, $this->parent->_base . 'ltple_referred_registration_stars' );
			}
		}
			
		public function get_user_stars( $user ) {
			
			if( current_user_can( 'administrator' ) ){
				
				echo '<div class="postbox" style="min-height:45px;">';
					
					echo '<h3 style="margin:10px;width:300px;display: inline-block;">' . __( 'Stars', 'live-template-editor-client' ) . '</h3>';

					$field =  array(
			
						'id' 			=> $this->parent->_base . 'stars',
						'description' 	=> '',
						'type'			=> 'number',
						'placeholder'	=> 'stars',
					);
					
					$this->parent->admin->display_field( $field, $user );
						
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