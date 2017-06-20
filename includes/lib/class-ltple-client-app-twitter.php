<?php

if ( ! defined( 'ABSPATH' ) ) exit;

use Abraham\TwitterOAuth\TwitterOAuth;

class LTPLE_Client_App_Twitter {
	
	var $parent;
	var $action;
	var $slug;
	var $connectedAppId;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $app_slug, $parent, $apps ) {

		$this->parent 		= $parent;
		$this->parent->apps = $apps;
		
		add_filter("user-app_custom_fields", array( $this, 'get_fields' ));
		
		$this->slug = $app_slug;
		
		// get app term

		$this->term = get_term_by('slug',$this->slug,'app-type');
		
		// get app parameters
		
		$parameters = get_option('parameters_'.$this->slug);
		
		if( isset($parameters['key']) ){
			
			$twt_consumer_key 		= array_search('twt_consumer_key', $parameters['key']);
			$twt_consumer_secret 	= array_search('twt_consumer_secret', $parameters['key']);
			$twt_oauth_callback 	= $this->parent->urls->editor;

			if( !empty($parameters['value'][$twt_consumer_key]) && !empty($parameters['value'][$twt_consumer_secret]) ){
			
				define('CONSUMER_KEY', 		$parameters['value'][$twt_consumer_key]);
				define('CONSUMER_SECRET', 	$parameters['value'][$twt_consumer_secret]);
				define('OAUTH_CALLBACK', 	$twt_oauth_callback);

				// get current action
				
				if(!empty($_REQUEST['action'])){
					
					$this->action = $_REQUEST['action'];
				}
				elseif(!empty($_SESSION['action'])){
					
					$this->action = $_SESSION['action'];
				}

				$methodName = 'app'.ucfirst($this->action);

				if(method_exists($this,$methodName)){
					
					$this->$methodName();
				}
			}
			else{
				
				$_SESSION['message'] = '<div class="alert alert-danger">';
					
					$_SESSION['message'] .= 'Sorry, twitter is not available on this platform yet, please contact the dev team...';
						
				$_SESSION['message'] .= '</div>';				
			}
		}
		
		// store frontend options
		
		if(isset($_POST['leadTwtDm'])){
			
			update_user_meta($this->parent->user->ID , $this->parent->_base . 'leadTwtDm',implode( PHP_EOL, array_map( 'sanitize_text_field', explode( PHP_EOL, $_POST['leadTwtDm'] ) ) ));
		}
	}
	
	// Add app data custom fields

	public function get_fields( $fields=[] ){
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"appRequests"),
				'id'			=>	"twtNextFollowersList",
				'label'			=>	"Next followers/list",
				'type'			=>	'text',
				'disabled'		=>	true,
				'placeholder'	=>	"",
				'description'	=>	'Next time these credentials can be used to request any followers/list'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"appRequests"),
				'id'			=>	"twtCursorFollowersList",
				'label'			=>	"Cursor followers/list",
				'type'			=>	'text',
				'disabled'		=>	true,
				'placeholder'	=>	"",
				'description'	=>	'Next cursor to proceed with the current app followers/list importation'
		);
		
		$fields[]=array(
		
			"metabox" =>
			
				array('name'=>"appRequests"),
				'id'			=>	"twtLastImportedFollowers",
				'label'			=>	"Last imported followers",
				'type'			=>	'text',
				'disabled'		=>	true,
				'placeholder'	=>	"",
				'description'	=>	'Last followers/list importation request made for the current app'
		);
		
		return $fields;
	}
	
	public function do_shortcodes( $str, $to='', $from='' ){
		
		// do email shortcodes
		
		$str = $this->parent->email->do_shortcodes($str);
		
		// do twitter shortcodes
		
		$shortcodes 	= [];
		$shortcodes[] 	= '*|TWT_NAME|*';
		$shortcodes[] 	= '*|TWT_FROM|*';
		
		$data 			= [];
		$data[]			= $to;
		$data[]			= $from;
		
		$str = str_replace($shortcodes,$data,$str);
		
		return $str;
	}
	
	public function get_direct_message(){
		
		$dm = get_user_meta($this->parent->user->ID , $this->parent->_base . 'leadTwtDm',true);
		
		if(empty($dm)){
		
			// default message
			
			$dm .= 'Hey *|TWT_NAME|*!' . PHP_EOL;
			$dm .= PHP_EOL;
			$dm .= 'Are you in the ' . get_option( $this->parent->_base . 'niche_business' ) . ' business?' . PHP_EOL;
			$dm .= PHP_EOL;
			
			if(!$this->parent->user->is_admin){
				
				$dm .= 'I am a ' . get_option( $this->parent->_base . 'niche_single' ) . '. Any new opportunities on your side?' . PHP_EOL;
				$dm .= PHP_EOL;
			}
			
			$dm .= 'We should exchange info.' . PHP_EOL;
			$dm .= PHP_EOL;
			$dm .= 'Have a nice day,' . PHP_EOL;
			$dm .= '*|TWT_FROM|*' . PHP_EOL;

		}
		
		return $dm;
	}
	
	public function is_valid_token($app){
		
		if( !empty( $app->oauth_token) && !empty( $app->oauth_token_secret) ){
		
			$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $app->oauth_token, $app->oauth_token_secret);
		
			$reponse = $connection->get('account/verify_credentials');
			
			if( !empty($reponse->id) ){
				
				return true;
			}
		}		
		
		return false;
	}

	public function startConnectionFor( $request='' ){
	
		// get a valid connection

		$args = array(
			
			'post_type' 	=> 'user-app',
			'post_status' 	=> 'publish',
			'numberposts' 	=> 1,
			//'meta_key' 		=> 'leadTwtFollowers',
			//'orderby' 		=> 'meta_value_num',
			//'order' 		=> 'DESC',
			'meta_query' 	=> array(
				'relation' 	=> 'OR',
				array(
					'key' 		=> $request,
					'value' 	=> time(),
					'compare' 	=> '<',
				),
				array(
					'key' 		=> $request,
					'compare' 	=> 'NOT EXISTS',
				)
			),
			'tax_query' => array(
				array(
				  'taxonomy' 		=> 'app-type',
				  'field' 			=> 'slug',
				  'terms' 			=> 'twitter',
				  'include_children'=> false
				)
			)
		);
		
		$q = get_posts( $args );

		$connection = false;
		
		if(!empty($q[0]->ID)){
		
			if($app = json_decode(get_post_meta( $q[0]->ID, 'appData', true ),false)){
			
				$this->connectedAppId = $q[0]->ID;
				
				if( !empty( $app->oauth_token) && !empty( $app->oauth_token_secret) ){
				
					$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $app->oauth_token, $app->oauth_token_secret);
				}
			}
		}
		
		return $connection;
	}
	
	public function followNextLeads($appId = null, $count = 1){
		
		if( is_numeric($appId) ){
			
			$user_id = intval(get_post_field( 'post_author', $appId ));
			
			if( $user_id > 0 ){
			
				if( $app = json_decode(get_post_meta( $appId, 'appData', true ),false) ){
				
					// start connection

					$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $app->oauth_token, $app->oauth_token_secret);
					
					// get next leads

					$args = array(
						
						'author' 	 	=> - $user_id,
						'post_type' 	=> 'lead',
						'posts_per_page'=> 100,
						'meta_query' 	=> array(
							'relation' 	=> 'OR',
							/*
							array(
								'key' 		=> 'leadRequestedBy'.$appId,
								'value' 	=> 0,
								'compare' 	=> '>',
							),
							*/
							array(
								'key' 		=> 'leadRequestedBy'.$appId,
								'compare' 	=> 'NOT EXISTS',
							)
						)			
					);
					
					$q = get_posts($args);
					
					if( !empty($q) ){
						
						$names = [];
						
						foreach( $q as $lead){
							
							$meta = get_post_meta($lead->ID);
							
							if( isset($meta['leadTwtName'][0]) ){
								
								//stack screen name

								$names[$lead->ID] = $meta['leadTwtName'][0];								
							}
						}
						
						if( !empty($names) ){
								
							// check friendship 

							$friendships = $connection->get( 'friendships/lookup', array(
							
								'screen_name' => implode(',',$names),
							));
							
							if(!empty($friendships)){
								
								$followed = [];
								
								foreach( $friendships as $friendship ){
								
									if( !empty($friendship->screen_name) && isset( $friendship->connections ) ){

										$lead_id = array_search($friendship->screen_name,$names);
										
										if( in_array('none',$friendship->connections) ){
											
											// follow user
												
											$friend = $connection->post( 'friendships/create', array(
										
												'screen_name' => $friendship->screen_name,
											));
											
											// set request time
											
											if( !empty($friend->screen_name) ){
												
												$followed[] = '@'.$friend->screen_name;
												
												update_post_meta( $lead_id, 'leadRequestedBy'.$appId, time() );
											}
											else{
												
												echo'Error following user...';
												exit;											
											}
										}
										else{
											
											update_post_meta( $lead_id, 'leadRequestedBy'.$appId, -1 );
										}
									}
									else{
										
										echo'Error getting user connections...';
										exit;
									}
									
									if( count($followed) == $count){
										
										break;
									}
								}
								
								if(!empty($followed)){

									$startWith = array(
									
										'Following awesomeness',
										'Hello everyone',
										'What\'s up',
										'Wassup',
										'Good day to',
										'Howdy!',
										'Hi there!',
										'How are you',
										'How are things',
										'How do you do',
										'What\'s happening',
										'Greetings',
										'Warm Greetings',
										'Welcoming',
										'Warm welcome to',
										'Happy to connect with',
										'Connecting with',
										'just discovered',
										'Discovering',
										'In love with',
										'Found of',
										'Crazy about',
										'pleased to meet',
										'D\'like to connect with',
										'D\'like to know',
										'D\'like to chat with ',
										'D\'like to talk',
										'Can we talk',
										'#handshake',
										'#acknowledgment',
										'#wordOfGreeting',
										'Good day to',
									);
									
									shuffle($startWith);
									
									$endWith = array(
										
										'',
										'RT','Thx!','Thanks',
										':D',':)','👌','🤣','😃','🙃','😘','😋','🤗',
										'😎','😺','👐','🙌','👍','✌️','🖐','👋','👥',
										'🕶','😻','🐵','🐱','🐭','🐹','🌟','🌞','☀️',
									);
									
									shuffle($endWith);
									
									// tweet a group status
									
									$status = reset($startWith) . ' ' . implode(' ',$followed) . ' ' . reset($endWith);
									
									$tweet = $connection->post( 'statuses/update', array(
									
										'status' => $status,
									));									
								}
							}
							else{
								
								echo'Error getting user firendship...';
								exit;									
							}								
						}
					}
				}
			}
		}			
	}

	public function unfollowLastLeads($appId = null, $max_unf = 1){
		
		exit;
		
		if( is_numeric($appId) ){
			
			$user_id = intval(get_post_field( 'post_author', $appId ));
			
			if( $user_id > 0 ){
			
				if( $app = json_decode(get_post_meta( $appId, 'appData', true ),false) ){
				
					// start connection

					$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $app->oauth_token, $app->oauth_token_secret);
					
					// get last leads followed

					$args = array(
						
						'author' 	 	=> - $user_id,
						'post_type' 	=> 'lead',
						'posts_per_page'=> 100,
						'meta_key'      => 'leadRequestedBy'.$appId,
						'order_by'      => 'meta_value_num',
						'order'         => 'DESC', // older first				
						'meta_query' 	=> array(
							'relation' 	=> 'AND',
							array(
								'key' 		=> 'leadRequestedBy'.$appId,
								'value' 	=> '-2',
								'compare' 	=> '!=',
							),
							array(
								'key' 		=> 'leadRequestedBy'.$appId,
								'compare' 	=> 'EXISTS',
							)
						)			
					);
					
					$q = get_posts($args);
					
					if( !empty($q) ){

						$names 		= [];
						$max_dms 	= 3;
						
						foreach( $q as $lead){
							
							$lead->meta = get_post_meta($lead->ID);
							
							if( isset($lead->meta['leadTwtName'][0]) ){
								
								//stack screen name

								$names[$lead->ID] = $lead->meta['leadTwtName'][0];								
							}
						}
						
						if( !empty($names) ){
								
							// check friendship 

							$friendships = $connection->get( 'friendships/lookup', array(
							
								'screen_name' => implode(',',$names),
							));
							
							if(!empty($friendships)){							
							
								$dms = $unf = 0;
							
								foreach( $friendships as $friendship ){
								
									if( !empty($friendship->screen_name) && isset( $friendship->connections ) ){

										$lead_id = array_search($friendship->screen_name,$names);
										
										if( !in_array('followed_by',$friendship->connections) ){
											
											// unfollow user
												
											$connection->post( 'friendships/destroy', array(
										
												'screen_name' => $friendship->screen_name,
											));
											
											// set request 

											update_post_meta( $lead_id, 'leadRequestedBy'.$appId, -2 );
										}
										else{
											
											// thanks for followingback DM on behalf of main account
											
											$dm_content = get_option( $this->parent->_base . 'twt_thanks_followback_dm' );
								
											if(!empty($dm_content)){
												
												$reponse = $connection->post('direct_messages/new', array(
												
													'screen_name' 	=> $friendship->screen_name,
													'text' 			=> $this->do_shortcodes($dm_content,$friendship->screen_name)
												));
												
												if(isset($reponse->created_at)){
										
													// set request 

													update_post_meta( $lead_id, 'leadRequestedBy'.$appId, -2 );													
										
													sleep(1);
										
													++$dms;
												} 
												elseif( !empty($reponse->errors[0]->code) ){
													
													var_dump($reponse->errors[0]);
													exit;
												}
											}
										}
									}
									else{
										
										echo'Error getting user connections...';
										exit;
									}
									
									if( $dms == $max_dms || $unf == $max_unf){
										
										break;
									}
									else{
										
										++$unf;
									}	
								}
							}
							else{
								
								echo'Error getting user firendship...';
								exit;									
							}								
						}
					}
					else{
						
						echo'No more firendships to analyse...';
						exit;						
					}
				}
			}
		}
	}
	
	
	public function retweetLastTweet($appId = null, $count){
		
		if( is_numeric($appId) ){
			
			if( $app = json_decode(get_post_meta( $appId, 'appData', true ),false) ){

				// start connection

				$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $app->oauth_token, $app->oauth_token_secret);
				
				// get niche hashtags
					
				$hashtags = $this->parent->apps->get_niche_hashtags();			
				
				// get item
				
				/*
				$q->statuses = $connection->get('statuses/user_timeline', array( 
				
					'screen_name' 		=> $app->screen_name, 
					'count' 			=> 200,
					'trim_user'			=> true,
					'exclude_replies'	=> true,
					'include_entities' 	=> true,
					'include_rts' 		=> false
				));
				*/
				
				$q = $connection->get('search/tweets', array( 
				
					'q' 				=> 'from:'.$app->screen_name . ' '. implode(' OR ',$hashtags), 
					'count' 			=> 100,
					'trim_user'			=> true,
					'include_entities' 	=> true,
					'result_type' 		=> 'mixed',
				));

				if(!empty($q->statuses)){
					
					$items 	= [];
					$skip 	= [];

					// fetch creation times

					foreach( $q->statuses as $status ){
	
						if( !isset($skip[$status->id]) ){
	
							$time = strtotime($status->created_at);
		
							if( empty($status->retweeted_status) ){
								
								$status->retweeted_status = new stdClass();
								
								$status->retweeted_status->id = $status->id;
							}
							else{
								
								$skip[$status->retweeted_status->id] = true;
							}
							
							$items[$time] = $status;
						}
					}
				
					if(!empty($items)){
					
						// sort by time
						
						//ksort($items, SORT_NUMERIC);
						shuffle($items);
						
						// get the oldest status (first in list)
						
						$status = reset($items);

						// unretweet
						
						$result = $connection->post('statuses/unretweet/'.$status->retweeted_status->id);

						// retweet
						
						$result = $connection->post('statuses/retweet/'.$status->retweeted_status->id);
						
						echo'<pre>';
						var_dump($result);
						exit;	
					}					
				}
				elseif(!empty($q->errors)){
					
					echo'<pre>';
					var_dump($q->errors);
					exit;						
				}
			}
		}
	}
	
	public function get_pending_importation(){
		
		$app = null;
		
		// get customers only
		
		$q = get_posts(array(
		
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
		
		if(!empty($q)){
			
			$ids = [];
			
			foreach($q as $post){
				
				$ids[] = $post->post_author;
			}
			
			$args = array(
			
				'post_type' 	=> 'user-app',
				'post_status' 	=> 'publish',
				'numberposts' 	=> -1,
				'author' 		=> implode(',',$ids),
				'orderby' 		=> 'date',
				'order' 		=> 'DESC',
				'meta_query' 	=> array(
					'relation' 	=> 'OR',
					array(
						'key' 		=> 'twtCursorFollowersList',
						'value' 	=> 0,
						'compare' 	=> '>',
					),
					array(
						'key' 		=> 'twtCursorFollowersList',
						'compare' 	=> 'NOT EXISTS',
					)
				),
				'tax_query' => array(
					array(
					  'taxonomy' 		=> 'app-type',
					  'field' 			=> 'slug',
					  'terms' 			=> 'twitter',
					  'include_children'=> false
					)
				)
			);
			
			$q = get_posts($args);
		
			if(!empty($q)){
		
				foreach($q as $i => $post){
					
					$lastImported = intval(get_post_meta($post->ID,'twtLastImportedFollowers',true));

					if(empty($lastImported)){
						
						$lastImported = $i;
					}

					$apps[$lastImported]=$post;
				}
				
				ksort($apps);
				
				$app = reset($apps);
			}
		}

		return $app;
	}
	
	public function importPendingLeads(){
		
		if( $app = $this->get_pending_importation() ){

			$this->insert_leads($app);
		}
	}
	
	public function insert_leads($app){
		
		if(!empty($app->ID)){

			$followers = $this->appGetFollowers($app->ID);
			
			if(!empty($followers)){
				
				foreach($followers as $follower){
				
					$lead_title = $this->slug . ' - ' . $follower->screen_name;

					$q = get_page_by_title( $lead_title, OBJECT, 'lead' );

					if( empty($q) ){

						if( $lead_id = wp_insert_post(array(
					
							'post_author' 	=> $app->post_author,
							'post_title' 	=> $lead_title,
							'post_type' 	=> 'lead',
							'post_status' 	=> 'publish'
						))){

							update_post_meta( $lead_id, 'leadAppId', 		$app->ID);
							update_post_meta( $lead_id, 'leadTwtName', 		$follower->screen_name);
							update_post_meta( $lead_id, 'leadNicename',		$follower->name);
							update_post_meta( $lead_id, 'leadPicture',		$follower->profile_image_url);
							update_post_meta( $lead_id, 'leadEmail', 		LTPLE_Client_App_Scraper::extractEmails($follower->description,true));
							update_post_meta( $lead_id, 'leadTwtFollowers',	$follower->followers_count);
							update_post_meta( $lead_id, 'leadDescription',	$follower->description );
							update_post_meta( $lead_id, 'leadCanSpam',		'true' );
							update_post_meta( $lead_id, 'leadTwtProtected',$follower->protected );
							
							if(!empty($follower->entities->urls->display_url) && !empty($follower->entities->urls->expanded_url)){
								
								update_post_meta( $lead_id, 'leadUrls', 		[ 'key' => [$follower->entities->urls->display_url], 'value' => [$follower->entities->urls->expanded_url] ] );
							}
						}
					}
				}
				
				return true;
			}
		}

		return false;
	}
	
	public function appImportLeads(){
		
		$user_id = $_REQUEST['user_id'];
		
		if(is_numeric($user_id)){
		
			$user_apps = $this->parent->apps->getUserApps($user_id,$this->slug);
			
			if( !empty($user_apps) ){
				
				foreach($user_apps as $app){
					
					if($this->insert_leads($app)){
						
						break;
					}
				}
			}
			else{
			
				echo 'Error getting account id';
				exit;
			}
		}
		else{
			
			echo 'Error getting user id';
			exit;			
		}
	}	
	
	public function appGetFollowers($app_id){
		
		$followers = [];
		
		//$next_request = intval( get_post_meta( $app_id, 'twtNextFollowersList', true ));

		//if( time() > $next_request ){
		
			if( $app = json_decode(get_post_meta( $app_id, 'appData', true ),false) ){
				
				// get app connection
				// another pair of credentials is used to request the data
			
				if($connection = $this->startConnectionFor('twtNextFollowersList')){
				
					// get app settings
					
					if( !$settings = json_decode(get_post_meta( $app_id, 'appSettings', true ),false)){
						
						$settings = new stdClass();
					}
					
					// set last cursor
					
					$cursor = -1;
					
					if(isset($settings->cursor_import_followers)){
						
						$cursor = $settings->cursor_import_followers;
					}
				
					if( $cursor !==0 ){
		
						// set request counts
						
						$r = 0;
						$max_request = 15;
						$time_limit	 = 15;
						
						// get followers
					
						do {
					
							$q = $connection->get('followers/list', array( 
							
								'screen_name' 		=> $app->screen_name, 
								'count' 			=> 200,
								'skip_status'		=> 1,
								'cursor' 			=> $cursor,
							));

							if( !empty($q->users) ){

								// get niche terms
									
								$terms = array_merge( $this->parent->apps->get_niche_terms(), $this->parent->apps->get_niche_hashtags() );					
								
								$count = count($q->users);
								
								// parse followers
								
								foreach($q->users as $i => $follower ){

									if( $cursor == -1 && $i == 0 ){
										
										$settings->last_to_follow = $follower->id;
										
										//update settings
										
										update_post_meta( $app_id, 'appSettings', json_encode($settings,JSON_PRETTY_PRINT));
									}

									//get corpus
									
									$corpus 	= [];
									$corpus[] 	= trim($follower->name);
									$corpus[] 	= trim($follower->screen_name);
									$corpus[] 	= trim($follower->description);
									$corpus[] 	= trim($follower->url);
									
									$corpus = array_filter($corpus);
									$corpus = strtolower(implode(' ',$corpus));

									foreach($terms as $term){
										
										if(strpos($corpus,strtolower($term))!==false){
											
											$followers[] = $follower;
										}
									}
								}

								if( isset($q->next_cursor) ){
								
									//set next_cursor
									
									$cursor = $settings->cursor_import_followers = $q->next_cursor;
									
									//update settings
									
									update_post_meta( $app_id, 'appSettings', json_encode($settings,JSON_PRETTY_PRINT));
									
									//update cursor
									
									update_post_meta( $app_id, 'twtCursorFollowersList', $cursor );
									
									//update imported time
									
									update_post_meta( $app_id, 'twtLastImportedFollowers', time() );
								}
								else{
									
									break;
								}
							}
							else{
								
								break;
							}
							
							$r++;
							
						} while( $r < $max_request );
						
						update_post_meta( $this->connectedAppId, 'twtNextFollowersList', ( time() + $time_limit * 60 ) );
						
					}
					else{
						
						add_action( 'admin_notices', function(){				
						
							echo'<div class="notice notice-success">';
							
								echo'<p>All followers already imported...</p>';
								
							echo'</div>';	
						});						
					}
				}
			}
		/*
		}
		else{
			
			add_action( 'admin_notices', function(){				
			
				echo'<div class="notice notice-warning">';
				
					echo'<p>Application request limit reached, wait few minutes...</p>';
					
				echo'</div>';	
			});
		}
		*/
		
		return $followers;
	}
	
	public function appSendDm($appId, $leadAppId, $screen_name, $message, $skipIt=false){
		
		if(is_numeric($leadAppId)){

			if($skipIt){
				
				// update last dm date
						
				update_post_meta($leadAppId, 'leadTwtLastDm','false');

				return true;				
			}
			elseif( is_numeric($appId) && !empty($screen_name) ){
				
				$message = trim($message);
				
				if( !empty($message) ){
					
					// add signature
					
					$message .= PHP_EOL . PHP_EOL . '_____________________';
					$message .= PHP_EOL . 'via ' . $_SERVER['SERVER_NAME'];
					$message .= PHP_EOL . 'tools for ' . get_option( $this->parent->_base . 'niche_business' ).' business';
				}
				
				if( $app = json_decode(get_post_meta( $appId, 'appData', true ),false) ){

					// start connection

					$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $app->oauth_token, $app->oauth_token_secret);
					
					$reponse = $connection->post('direct_messages/new', array( 
					
						'screen_name' 	=> $screen_name, 
						'text' 			=> $message,
					));

					if(isset($reponse->created_at)){
						
						// store last dm date
						
						update_post_meta($leadAppId, 'leadTwtLastDm',time());
						
						// hook connected app
							
						do_action( 'ltple_twitter_dm_sent');

						return true;
					} 
					else{

						if( !empty($reponse->errors[0]->code) ){
							
							if($reponse->errors[0]->code == 34){
								
								// page does not exist
								
								update_post_meta($leadAppId, 'leadTwtLastDm','false');
								
								return true;								
							}
							elseif($reponse->errors[0]->code == 226){
								
								// request looks like it might be automated
								
								// Do something...
							}
						}

						return json_encode($reponse);
					}
				}	
			}
		}

		return false;		
	}
	
	public function appImportImg(){
		
		if(!empty($_REQUEST['id'])){
			
			if( $this->app = $this->parent->apps->getAppData( $_REQUEST['id'], $this->parent->user->ID ) ){

				$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->app->oauth_token, $this->app->oauth_token_secret);
				
				$items = $this->connection->get('statuses/user_timeline', array( 
				
					'screen_name' 		=> $this->app->screen_name, 
					'count' 			=> 200,
					'trim_user'			=> true,
					'exclude_replies'	=> true,
					'include_entities' 	=> true,
					'include_rts' 		=> false
				));

				if( !empty($items->errors[0]->message) ){
	
					$_SESSION['message'] = '<div class="alert alert-danger">';
						
						$_SESSION['message'] .= $items->errors[0]->message;
							
					$_SESSION['message'] .= '</div>';	
				}
				else{
						
					$urls = [];
					
					if(!empty($items)){
						
						foreach($items as $item){
							
							if(isset($item->entities->media)){
								
								foreach($item->entities->media as $media){
									
									if($media->type == 'photo'){
										
										$img_title	= basename($media->media_url);
										$img_url	= $media->media_url;
										
										if(!get_page_by_title( $img_title, OBJECT, 'user-image' )){
											
											if($image_id = wp_insert_post(array(
										
												'post_author' 	=> $this->parent->user->ID,
												'post_title' 	=> $img_title,
												'post_content' 	=> $img_url,
												'post_type' 	=> 'user-image',
												'post_status' 	=> 'publish'
											))){
												
												wp_set_object_terms( $image_id, $this->term->term_id, 'app-type' );
											}
										}
									}						
								}
							}
						}
					}
				}
			}
		}
	}
	
	public function appUnlockFree(){

		if(!empty($_REQUEST['id'])){

			if( $this->app = $this->parent->apps->getAppData( $_REQUEST['id'], $this->parent->user->ID ) ){

				$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->app->oauth_token, $this->app->oauth_token_secret);
				
				// make unlock free tweet

				$tweet_content = get_option( $this->parent->_base . 'twt_unlock_tweet' );
				
				if(!empty($tweet_content )){
					
					$response = $this->connection->post('statuses/update', array(
					
						'status' => $this->do_shortcodes($tweet_content,$this->app->screen_name)
					));
					
					if( isset($response->id) ){
						
						// send unlock request to server
						
						$this->parent->plan->unlock_output_request();
					}
				}
			}
		}
	}
	
	public function appConnect(){
		
		if( isset($_REQUEST['action']) ){
			
			$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
			
			if(!isset($_SESSION['oauth_token'])){
				
				$this->request_token = $this->connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));	

				$_SESSION['app'] 				= $this->slug;
				$_SESSION['action'] 			= $_REQUEST['action'];
				$_SESSION['ref'] 				= ( !empty($_REQUEST['ref']) ? 'http://'.urldecode($_REQUEST['ref']) : '');
				$_SESSION['oauth_token'] 		= $this->request_token['oauth_token'];
				$_SESSION['oauth_token_secret'] = $this->request_token['oauth_token_secret'];			
			}
			
			if(isset($_SESSION['oauth_token'])){
			
				$this->oauth_url = $this->connection->url('oauth/authenticate', array('oauth_token' => $_SESSION['oauth_token']));
			
				wp_redirect($this->oauth_url);
				echo 'Redirecting twitter oauth...';
				exit;		
			}
		}
		elseif( isset($_SESSION['action']) ){

			if(!isset($_SESSION['access_token'])){
				
				// handle connect callback
				
				$this->request_token = [];
				$this->request_token['oauth_token'] 		= $_SESSION['oauth_token'];
				$this->request_token['oauth_token_secret'] 	= $_SESSION['oauth_token_secret'];

				if(isset($_REQUEST['oauth_token']) && $this->request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
					
					//flush session
					session_destroy();
					
					// store failure message

					$_SESSION['message'] = '<div class="alert alert-danger">';
						
						$_SESSION['message'] .= 'Twitter connection failed...';
							
					$_SESSION['message'] .= '</div>';
				}
				elseif(isset($_REQUEST['oauth_verifier'])){
					
					$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->request_token['oauth_token'], $this->request_token['oauth_token_secret']);
					
					//get the long lived access_token that authorized to act as the user
					
					$this->access_token = $this->connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);
					
					//flush session
					session_destroy();

					//store access_token in session					
					$_SESSION['app'] 			= $this->slug;
					$_SESSION['access_token'] 	= $this->access_token;
					
					// store access_token in database		
					
					$app_title = wp_strip_all_tags( 'twitter - ' . $_SESSION['access_token']['screen_name'] );
					
					$app_item = get_page_by_title( $app_title, OBJECT, 'user-app' );
					
					if( empty($app_item) ){
						
						// create app item
						
						$app_id = wp_insert_post(array(
						
							'post_title'   	 	=> $app_title,
							'post_status'   	=> 'publish',
							'post_type'  	 	=> 'user-app',
							'post_author'   	=> $this->parent->user->ID
						));
						
						wp_set_object_terms( $app_id, $this->term->term_id, 'app-type' );
						
						// set app item
											
						update_post_meta( $app_id, 'appData', json_encode($this->access_token,JSON_PRETTY_PRINT));

						// do welcome actions
						
						$this->do_welcome_actions($app_id);
						
						// hook connected app
						
						do_action( 'ltple_twitter_account_connected');
						
						$this->parent->apps->newAppConnected();
					}
					else{
	
						// update app item
											
						update_post_meta( $app_item->ID, 'appData', json_encode($this->access_token,JSON_PRETTY_PRINT));
					}
						

					if(!empty($_SESSION['ref'])){
						
						// handle redirection
						
						$redirect_url = $_SESSION['ref'];
						
						$_SESSION['ref'] = '';
						
						wp_redirect($redirect_url);
						echo 'Redirecting twitter callback...';
						exit;	
					}
					else{
						
						// store success message

						$_SESSION['message'] = '<div class="alert alert-success">';
							
							$_SESSION['message'] .= 'Congratulations, you have successfully connected a Twitter account!';
								
						$_SESSION['message'] .= '</div>';						
					}

				}
				else{
					
					//flush session
					session_destroy();					
				}
			}

			//var_dump($this->parent->user->ID);exit;
		}
	}
	
	public function appLogin(){
		
		if( isset($_REQUEST['action']) ){
			
			$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
			
			if(!isset($_SESSION['oauth_token'])){
				
				$this->request_token = $this->connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));	

				$_SESSION['app'] 				= $this->slug;
				$_SESSION['action'] 			= $_REQUEST['action'];
				$_SESSION['ref'] 				= ( !empty($_REQUEST['ref']) ? 'http://'.urldecode($_REQUEST['ref']) : '');
				$_SESSION['oauth_token'] 		= $this->request_token['oauth_token'];
				$_SESSION['oauth_token_secret'] = $this->request_token['oauth_token_secret'];			
			}
			
			if(isset($_SESSION['oauth_token'])){
			
				$this->oauth_url = $this->connection->url('oauth/authenticate', array(
				
					'oauth_token' => $_SESSION['oauth_token'],
					'force_login' => 'false'
				));
			
				wp_redirect($this->oauth_url);
				echo 'Redirecting twitter oauth...';
				exit;		
			}
		}
		elseif( isset($_SESSION['action']) ){

			if(!isset($_SESSION['access_token'])){
				
				// handle connect callback
				
				$this->request_token = [];
				$this->request_token['oauth_token'] 		= $_SESSION['oauth_token'];
				$this->request_token['oauth_token_secret'] 	= $_SESSION['oauth_token_secret'];

				if(isset($_REQUEST['oauth_token']) && $this->request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
					
					//flush session
					session_destroy();
					
					// store failure message

					echo 'Twitter login failed...';
					exit;
				}
				elseif(isset($_REQUEST['oauth_verifier'])){
					
					$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->request_token['oauth_token'], $this->request_token['oauth_token_secret']);
					
					//get the long lived access_token that authorized to act as the user
					
					$this->access_token = $this->connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);
					
					//flush session
					session_destroy();

					//store access_token in session	
					
					$_SESSION['app'] 			= $this->slug;
					$_SESSION['access_token'] 	= $this->access_token;
					
					// get associated user id
					
					$app_title = wp_strip_all_tags( 'twitter - ' . $_SESSION['access_token']['screen_name'] );
					
					$app_item = get_page_by_title( $app_title, OBJECT, 'user-app' );

					$userIsNew = false;	
					
					if(empty($app_item->post_author)){
						
						// start user connection
						
						$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->access_token['oauth_token'], $this->access_token['oauth_token_secret']);
						
						// get account info 

						$account =	$this->connection->get('account/verify_credentials', array(

							'include_entities' 	=> 'false',
							'skip_status' 		=> 'true',
							'include_email' 	=> 'true'
						));

						if(!empty($account->email)){
							
							$this->userId = email_exists($account->email);

							if(!is_numeric($this->userId)){
								
								// get unique user name
								
								$user_name = $account->screen_name;
								
								if( username_exists( $user_name ) ){
									
									$i=2;
									
									while(username_exists( $user_name.$i )){
										
										++$i;
									}
									
									$user_name = $user_name.$i;
								}
								
								$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
								
								$this->userId = wp_create_user( $user_name, $random_password, $account->email );						
							}
							
							if( is_numeric($this->userId) ){
								
								$this->userId = intval($this->userId);
								
								// create app item
								
								$app_id = wp_insert_post(array(
								
									'post_title'   	 	=> $app_title,
									'post_status'   	=> 'publish',
									'post_type'  	 	=> 'user-app',
									'post_author'   	=> $this->userId
								));
								
								wp_set_object_terms( $app_id, $this->term->term_id, 'app-type' );

								// update app item
									
								update_post_meta( $app_id, 'appData', json_encode($this->access_token,JSON_PRETTY_PRINT));

								// do welcome actions
								
								$this->do_welcome_actions();

								$userIsNew = true;							
							}
							else{
								
								echo'<pre>';
								var_dump($this->userId);
								
								//echo'Error creating a new Twitter user...';
								exit;
							}						
						}
						else{
							
							echo 'Error getting the email associated to this Twitter account...';
							exit;
						}
					}
					else{
						
						$this->userId = intval( $app_item->post_author );
						
						// refresh app token
									
						update_post_meta( $app_item->ID, 'appData', json_encode($this->access_token,JSON_PRETTY_PRINT));						
					}
					
					if(is_numeric($this->userId)){
					
						// set current user
						
						$this->parent->user = wp_set_current_user( $this->userId );
						$this->parent->user->loggedin = true;
						
						// set auth cookie
						
						wp_set_auth_cookie($this->userId, true);
						
						if($userIsNew === true){
							
							// hook connected app
								
							do_action( 'ltple_twitter_account_connected');
								
							$this->parent->apps->newAppConnected();									
						}
					}
					else{
						
						echo'Error logging current Twitter user...';
						exit;
					}
					
					// handle redirection

					if(!empty($_SESSION['ref'])){
						
						$redirect_url = $_SESSION['ref'];
						
						$_SESSION['ref'] = '';
						
						wp_redirect($redirect_url);
						echo 'Redirecting twitter callback...';
						exit;	
					}
				}
				else{
					
					//flush session
					session_destroy();					
				}
			}
		}
	}
	
	public function do_welcome_actions(){
							
		// get main account
		
		$appId = get_option( $this->parent->_base . 'twt_main_account' );

		if( $app = $this->parent->apps->getAppData($appId)){
			
			// new account follow main account
			
			$this->connection->post('friendships/create', array(
			
				'screen_name' => $app->screen_name
			));
			
			// start main account connection
			
			$this->main_connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $app->oauth_token, $app->oauth_token_secret);
			
			// main account follow new account
			
			$this->main_connection->post('friendships/create', array(
			
				'screen_name' => $this->access_token['screen_name']
			));						
			
			// welcome tweet on behalf of main account

			$tweet_content = get_option( $this->parent->_base . 'twt_welcome_tweet' );
			
			if(!empty($tweet_content )){
				
				$this->main_connection->post('statuses/update', array(
				
					'status' => $this->do_shortcodes($tweet_content,$this->access_token['screen_name'])
				));
			}
			
			// welcome DM on behalf of main account
			
			$dm_content = get_option( $this->parent->_base . 'twt_welcome_dm' );
			
			if(!empty($dm_content )){
				
				$this->main_connection->post('direct_messages/new', array(
				
					'screen_name' 	=> $this->access_token['screen_name'],
					'text' 			=> $this->do_shortcodes($dm_content,$this->access_token['screen_name'])
				));
				
				// TODO get leadAppId and update leadTwtLastDm
				
				//update_post_meta($leadAppId, 'leadTwtLastDm', time());
			}
		}
	}
} 