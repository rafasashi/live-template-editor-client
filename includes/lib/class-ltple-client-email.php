<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Email {
	
	var $parent;
	
	/**
	 * Constructor function
	 */
	 
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		// add cron events
			
		add_action( $this->parent->_base . 'send_email_event', 	array( $this, 'send_model'),1,2);
		
		// setup phpmailer

		add_action( 'phpmailer_init', 	function( PHPMailer $phpmailer ) {
			
			$key_name = "key1";
			$urlparts = parse_url(site_url());		
			
			$phpmailer->SMTPOptions = array(
				'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			));

			$phpmailer->DKIM_domain 	= $urlparts ['host'];
			$phpmailer->DKIM_private 	= WP_CONTENT_DIR . "/keys/dkim_" . $key_name . ".ppk";
			$phpmailer->DKIM_selector 	= $key_name;
			$phpmailer->DKIM_passphrase = "";
			$phpmailer->DKIM_identifier = $phpmailer->From;

			$phpmailer->IsSMTP();
		});		
		
		// Custom default email address
		
		add_filter('wp_mail_from', function($old){
			
			$urlparts = parse_url(site_url());
			$domain = $urlparts ['host'];
			
			return 'please-reply@'.$domain;
		});
		
		add_filter('wp_mail_from_name', function($old) {
			
			return 'Live Editor';
		});
	}
	
	public function do_shortcodes( $str, $user=null){
		
		$shortcodes 	= [];
		$shortcodes[] 	= '*|DAY|*'; 		// today
		$shortcodes[] 	= '*|DATE:d/m/y|*'; // date
		$shortcodes[] 	= '*|DATE:y|*'; 	// year
		
		if( !is_null($user) ){
			
			$shortcodes[] 	= '*|FNAME|*';
			$shortcodes[] 	= '*|LNAME|*';
			$shortcodes[] 	= '*|EMAIL|*';			
		}
		
		$data 			= [];
		$data[]			= date( 'l', time());
		$data[]			= date( 'd/m/y', time());
		$data[]			= date( 'y'	 , time());
		
		if( !is_null($user) ){
			
			$data[] 		= ( $user->first_name !='' ? ucfirst($user->first_name) : ucfirst($user->user_nicename) );
			$data[]			= ( $user->last_name  !='' ? ucfirst($user->last_name ) : '' );
			$data[]			= 	$user->user_email;
		}
		
		$str = str_replace($shortcodes,$data,$str);
		
		return $str;
	}
	
	public function send_model( $model_id, $user){
		
		if(is_numeric( $user )){
			
			$user = get_user_by( 'id', $user);
		}
		elseif(is_string($user)){
			
			$user = get_user_by( 'email', $user);
		}
		
		$can_spam = get_user_meta( $user->ID, $this->parent->_base . '_can_spam',true);

		if($can_spam !== 'false'){
		
			$model = get_post($model_id);
			
			if(isset($model->ID)){
				
				$urlparts = parse_url(site_url());
				$domain = $urlparts ['host'];				
				
				
				$title= str_replace(array('–'),'-',$model->post_title);
				$title= explode('-',$title,2);

				if(isset($title[1])){
					
					$Email_title = $title[1];
				}
				else{
					
					$Email_title = $title[0];
				}
				
				$Email_title = $this->do_shortcodes($Email_title, $user);

				// get email slug
				
				$email_slug = sanitize_title($Email_title);
				
				// get email sent
				
				$emails_sent = get_user_meta($user->ID, $this->parent->_base . '_email_sent', true);
				
				if( empty($emails_sent) ){
					
					$emails_sent=[];
				}
				else{
					
					$emails_sent=json_decode($emails_sent,true);
				}
				
				if( !isset($emails_sent[$email_slug]) ){
					
					$sender_email 	= 'please-reply@'.$domain;
					
					$message 		= $model->post_content;
					$message	 	= $this->do_shortcodes($message, $user);
					
					$headers   = [];
					$headers[] = 'From: ' . get_bloginfo('name') . ' <'.$sender_email.'>';
					$headers[] = 'MIME-Version: 1.0';
					$headers[] = 'Content-type: text/html';
					
					$unsubscribeMessage = '<div style="text-align:center;"><a style="font-size: 11px;" href="' . $this->parent->urls->editor . '?unsubscribe=' . $this->parent->ltple_encrypt_uri($user->ID) . '">Unsubscribe from this Newsletter</a></div>';
					
					$preMessage = "<html><body><div style='width:700px;padding:5px;margin:auto;font-size:14px;line-height:18px'>" . apply_filters('the_content', $message) . "<div style='clear:both'></div>".$unsubscribeMessage."<div style='clear:both'></div></div></body></html>";
					
					if(!wp_mail($user->user_email, $Email_title, $preMessage, $headers)){
						
						global $phpmailer;
						
						var_dump($phpmailer->ErrorInfo);exit;				
					}
					else{
						
						// update email sent
						
						$emails_sent[$email_slug]=time();
						
						if( is_array($emails_sent) && !empty($emails_sent) ){
							
							arsort($emails_sent);
							$emails_sent = json_encode($emails_sent);

							update_user_meta($user->ID, $this->parent->_base . '_email_sent', $emails_sent);
						}
						else{
							
							echo 'Error storing email sent info...';
							exit;
						}
						
						return true;
					}				
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Main LTPLE_Client_Email Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Email is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Email instance
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