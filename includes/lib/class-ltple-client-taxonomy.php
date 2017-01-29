<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Taxonomy {
	
	public $parent;
	
	/**
	 * The name for the taxonomy.
	 * @var 	string
	 * @access  public
	 * @since 	1.0.0
	 */
	public $taxonomy;

	/**
	 * The plural name for the taxonomy terms.
	 * @var 	string
	 * @access  public
	 * @since 	1.0.0
	 */
	public $plural;

	/**
	 * The singular name for the taxonomy terms.
	 * @var 	string
	 * @access  public
	 * @since 	1.0.0
	 */
	public $single;

	/**
	 * The array of post types to which this taxonomy applies.
	 * @var 	array
	 * @access  public
	 * @since 	1.0.0
	 */
	public $post_types;

  /**
	 * The array of taxonomy arguments
	 * @var 	array
	 * @access  public
	 * @since 	1.0.0
	 */
	public $taxonomy_args;

	public function __construct ( $parent, $taxonomy = '', $plural = '', $single = '', $post_types = array(), $tax_args = array() ) {
		
		$this->parent = $parent;
		
		if ( ! $taxonomy || ! $plural || ! $single ) return;

		// Post type name and labels
		$this->taxonomy = $taxonomy;
		$this->plural = $plural;
		$this->single = $single;
		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}
		$this->post_types = $post_types;
		$this->taxonomy_args = $tax_args;

		// Register taxonomy
		add_action('init', array( $this, 'register_taxonomy' ) );
	}

	/**
	 * Register new taxonomy
	 * @return void
	 */
	public function register_taxonomy () {

        $labels = array(
            'name' => $this->plural,
            'singular_name' => $this->single,
            'menu_name' => $this->plural,
            'all_items' => sprintf( __( 'All %s' , 'live-template-editor-client' ), $this->plural ),
            'edit_item' => sprintf( __( 'Edit %s' , 'live-template-editor-client' ), $this->single ),
            'view_item' => sprintf( __( 'View %s' , 'live-template-editor-client' ), $this->single ),
            'update_item' => sprintf( __( 'Update %s' , 'live-template-editor-client' ), $this->single ),
            'add_new_item' => sprintf( __( 'Add New %s' , 'live-template-editor-client' ), $this->single ),
            'new_item_name' => sprintf( __( 'New %s Name' , 'live-template-editor-client' ), $this->single ),
            'parent_item' => sprintf( __( 'Parent %s' , 'live-template-editor-client' ), $this->single ),
            'parent_item_colon' => sprintf( __( 'Parent %s:' , 'live-template-editor-client' ), $this->single ),
            'search_items' =>  sprintf( __( 'Search %s' , 'live-template-editor-client' ), $this->plural ),
            'popular_items' =>  sprintf( __( 'Popular %s' , 'live-template-editor-client' ), $this->plural ),
            'separate_items_with_commas' =>  sprintf( __( 'Separate %s with commas' , 'live-template-editor-client' ), $this->plural ),
            'add_or_remove_items' =>  sprintf( __( 'Add or remove %s' , 'live-template-editor-client' ), $this->plural ),
            'choose_from_most_used' =>  sprintf( __( 'Choose from the most used %s' , 'live-template-editor-client' ), $this->plural ),
            'not_found' =>  sprintf( __( 'No %s found' , 'live-template-editor-client' ), $this->plural ),
        );

        $args = array(
        	'label' 				=> $this->plural,
        	'labels' 				=> apply_filters( $this->taxonomy . '_labels', $labels ),
        	'hierarchical' 			=> true,
            'public' 				=> true,
            'show_ui' 				=> true,
            'show_in_nav_menus' 	=> true,
            'show_tagcloud' 		=> true,
            'meta_box_cb' 			=> null,
			'show_in_quick_edit' 	=> false,
            'show_admin_column' 	=> true,
            'update_count_callback' => '',
            'show_in_rest'          => true,
            'rest_base'             => $this->taxonomy,
            'rest_controller_class' => 'WP_REST_Terms_Controller',
            'query_var' 			=> $this->taxonomy,
            'rewrite' 				=> true,
            'sort' 					=> '',
        );

        $args = array_merge($args, $this->taxonomy_args);
		
		register_taxonomy( $this->taxonomy, $this->post_types, apply_filters( $this->taxonomy . '_register_args', $args, $this->taxonomy, $this->post_types ) );		

		$taxonomy_groups = [];
		$taxonomy_groups['layer-type'] 		= 'layer';
		$taxonomy_groups['layer-range'] 	= 'layer';
		$taxonomy_groups['account-option']	= 'layer';
		$taxonomy_groups['image-type']		= 'image';
		$taxonomy_groups['app-type']		= 'app';
		
		if( isset($taxonomy_groups[$this->taxonomy]) ){
			
			add_filter( $this->taxonomy . '_row_actions', array($this, 'remove_app_taxonomy_quick_edition'), 10, 2 );
							
			if( $taxonomy_groups[$this->taxonomy] == 'layer' ){
				
				// add taxonomy custom fields
				
				add_action($this->taxonomy . '_add_form_fields', array( $this, 'get_new_layer_taxonomy_fields' ) );
				add_action($this->taxonomy . '_edit_form_fields', array( $this, 'get_layer_taxonomy_fields' ) );
				
				if($this->taxonomy=='account-option'){
					
					// add custom account taxonomy column
					
					add_filter('manage_edit-' . $this->taxonomy . '_columns', array( $this, 'set_account_taxonomy_columns' ) );
					add_filter('manage_' . $this->taxonomy . '_custom_column', array( $this, 'add_account_taxonomy_column_content' ),10,3);			
				}
				else{
					
					// add custom layer taxonomy column
					
					add_filter('manage_edit-' . $this->taxonomy . '_columns', array( $this, 'set_layer_taxonomy_columns' ) );
					add_filter('manage_' . $this->taxonomy . '_custom_column', array( $this, 'add_layer_taxonomy_column_content' ),10,3);		
				}

				// save taxonomy custom fields
				
				add_action('create_' . $this->taxonomy, array( $this, 'save_layer_taxonomy_fields' ) );
				add_action('edit_' . $this->taxonomy, array( $this, 'save_layer_taxonomy_fields' ) );				
			}
			elseif( $taxonomy_groups[$this->taxonomy] == 'app' ){
				
				// add taxonomy custom fields
				
				add_action($this->taxonomy . '_add_form_fields', array( $this, 'get_new_app_taxonomy_fields' ) );
				add_action($this->taxonomy . '_edit_form_fields', array( $this, 'get_app_taxonomy_fields' ) );

				add_filter('manage_edit-' . $this->taxonomy . '_columns', array( $this, 'set_app_taxonomy_columns' ) );
				add_filter('manage_' . $this->taxonomy . '_custom_column', array( $this, 'add_app_taxonomy_column_content' ),10,3);			

				// save taxonomy custom fields
				
				add_action('create_' . $this->taxonomy, array( $this, 'save_app_taxonomy_fields' ) );
				add_action('edit_' . $this->taxonomy, array( $this, 'save_app_taxonomy_fields' ) );
			}
		}
	}

	public function remove_app_taxonomy_quick_edition( $actions, $term ){

		//unset( $actions['edit'] );
		unset( $actions['view'] );
		unset( $actions['trash'] );
		unset( $actions['inline hide-if-no-js'] );
		
		return $actions;
	}
	
	//--------------------- APP TAXONOMY ----------------------
	
	public function set_app_taxonomy_columns($columns) {

		// Remove description, posts, wpseo columns
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] 			= '<input type="checkbox" />';
		$columns['thumbnail'] 	= 'Thumb';
		$columns['name'] 		= 'Name';
		$columns['slug'] 		= 'Slug';
		$columns['types'] 		= 'Types';
		
		return $columns;
	}
		
	public function add_app_taxonomy_column_content($content, $column_name, $term_id){
	
		$term= get_term($term_id);
	
		if($column_name == 'thumbnail') {

			$thumb_url = get_option('thumbnail_' . $term->slug);
			
			if(!empty($thumb_url)){
				
				$content.='<img style="width: 70px;" src="'.$thumb_url.'" />';
			}
			else{
				
				$content.='<div style="width: 70px;text-align:center;">null</div>';
			}
		}
		elseif($column_name == 'types'){
			
			$types = get_option('types_' . $term->slug);
			
			if(!empty($types)){
				
				$content.='<ul style="margin:0;font-size:11px;">';
				
					foreach($types as $type){
						
						$content.='<li>'.$type.'</li>';
					}
				
				$content.='</ul>';				
			}
		}

		return $content;
	}
	
	public function get_new_app_taxonomy_fields($taxonomy_name){
		
		echo'<div class="form-field">';
			
			echo'<label for="'.$taxonomy_name.'-thumbnail">Thumbnail</label>';

			echo'<div class="input-group">';

				echo'<input type="text" name="'.$taxonomy_name.'-thumbnail" id="'.$taxonomy_name.'-thumbnail" value=""/>';

			echo'</div>';
			
		echo'</div>';
		
		echo'<div class="form-field">';
		
			echo'<label for="'.$taxonomy_name.'-types">Types</label>';
				
			$types = LTPLE_Client()->get_app_types();
			
			foreach($types as $type){
				
				echo'<div class="input-group">';
					echo'<input type="checkbox" name="'.$taxonomy_name.'-types[]" id="'.$taxonomy_name.'-types" value="'.$type.'"/> '.ucfirst($type);
				echo'</div>';				
			}
				
		echo'</div>';
	}	
	
	public function get_app_taxonomy_fields($term){

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Thumbnail</label>';
			
			echo'</th>';
			
			echo'<td>';
				
				echo'<input type="text" name="' . $term->taxonomy . '-thumbnail" id="' . $term->taxonomy . '-thumbnail" value="'.get_option('thumbnail_'.$term->slug).'"/>';
						
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Types</label>';
			
			echo'</th>';
			
			echo'<td>';
				
				$types 		= LTPLE_Client()->get_app_types();
				$app_types 	= get_option('types_'.$term->slug);
				
				foreach($types as $type){
					
					$checked = ( ( !empty($app_types) && in_array($type,$app_types)) ? ' checked="checked"' : '' );
					
					echo'<div class="input-group">';
					
						echo'<input type="checkbox" name="'.$term->taxonomy.'-types[]" id="'.$term->taxonomy.'-types" value="'.$type.'"'.$checked.'/> '.ucfirst($type);
					
					echo'</div>';				
				}
						
			echo'</td>';
			
		echo'</tr>';	

		if($this->parent->user->is_admin){

			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">API Client</label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$clients 					= array();
					$clients ['None'] 			= 'None';
					$clients ['scraper'] 		= 'Scraper';
					$clients ['blogger'] 		= 'Blogger';
					$clients ['google-plus']	= 'Google +';
					$clients ['imgur'] 			= 'Imgur';
					$clients ['tumblr'] 		= 'Tumblr';
					$clients ['twitter'] 		= 'Twitter';
					$clients ['wordpress'] 		= 'Wordpress';
					$clients ['youtube'] 		= 'Youtube';
					
					$field = array(
						'type'				=> 'select',
						'id'				=> 'api_client_'.$term->slug,
						'name'				=> $term->taxonomy . '-api-client',
						'options' 			=> $clients,
						'description'		=> '',
					);
					
					$this->parent->admin->display_field( $field, false );
					
				echo'</td>';
				
			echo'</tr>';
		
			echo'<tr class="form-field">';
			
				echo'<th valign="top" scope="row">';
					
					echo'<label for="category-text">Parameters (admin)</label>';
				
				echo'</th>';
				
				echo'<td>';
					
					$field = array(
						'type'				=> 'key_value',
						'id'				=> 'parameters_'.$term->slug,
						'name'				=> $term->taxonomy . '-parameters',
						'array' 			=> [],
						'description'		=> ''
					);
					
					$this->parent->admin->display_field( $field, false );
					
				echo'</td>';
				
			echo'</tr>';
		}		
	}
	
	public function save_app_taxonomy_fields($term_id){

		//collect all term related data for this new taxonomy
		
		$term = get_term($term_id);

		//save our custom fields as wp-options
		
		if(isset($_POST[$term->taxonomy . '-thumbnail'])){

			update_option('thumbnail_'.$term->slug, sanitize_text_field($_POST[$term->taxonomy . '-thumbnail'],1));			
		}

		if(isset($_POST[$term->taxonomy . '-types'])){

			update_option('types_'.$term->slug, $_POST[$term->taxonomy . '-types']);			
		}
		
		if($this->parent->user->is_admin){
		
			if(isset($_POST[$term->taxonomy . '-parameters'])){

				update_option('parameters_'.$term->slug, $_POST[$term->taxonomy . '-parameters']);			
			}
			
			if(isset($_POST[$term->taxonomy . '-api-client'])){

				update_option('api_client_'.$term->slug, $_POST[$term->taxonomy . '-api-client']);			
			}
		}
	}
	
	//---------------------LAYER TAXONOMY----------------------
	
	public function get_new_layer_taxonomy_fields($taxonomy_name){
		
		echo'<div class="form-field">';
			
			echo'<label for="'.$taxonomy_name.'-price-amount">Price</label>';

			echo LTPLE_Client()-> get_layer_taxonomy_price_fields($taxonomy_name,[]);
			
		echo'</div>';
		
		echo'<div class="form-field">';
			
			echo'<label for="'.$taxonomy_name.'-storage-amount">Storage</label>';

			echo LTPLE_Client()-> get_layer_taxonomy_storage_field($taxonomy_name,0);
			
		echo'</div>';
	}	
	
	public function get_layer_taxonomy_fields($term){

		//collect the term slug
		$term_slug = $term->slug;

		//collect our saved term field information
		
		$args=[];
		$args['price_amount'] = get_option('price_amount_' . $term_slug); 
		$args['price_period'] = get_option('price_period_' . $term_slug); 

		$storage_amount = get_option('storage_amount_' . $term_slug);
		
		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Price </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				echo LTPLE_Client()-> get_layer_taxonomy_price_fields($term->taxonomy,$args);
				
			echo'</td>';
			
		echo'</tr>';

		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Storage </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				echo LTPLE_Client()-> get_layer_taxonomy_storage_field($term->taxonomy,$storage_amount);
						
			echo'</td>';
			
		echo'</tr>';		
	}
	
	public function save_layer_taxonomy_fields($term_id){

		//collect all term related data for this new taxonomy
		$term = get_term($term_id);

		//save our custom fields as wp-options
		
		if(isset($_POST[$term->taxonomy .'-price-amount'])&&is_numeric($_POST[$term->taxonomy .'-price-amount'])){

			update_option('price_amount_' . $term->slug, round(intval(sanitize_text_field($_POST[$term->taxonomy . '-price-amount'])),1));			
		}
		
		if(isset($_POST[$term->taxonomy .'-price-period'])){

			$periods = LTPLE_Client()->get_price_periods();
			$period = sanitize_text_field($_POST[$term->taxonomy . '-price-period']);
			
			if(isset($periods[$period])){
				
				update_option('price_period_' . $term->slug, $period);	
			}
		}
		
		if(isset($_POST[$term->taxonomy .'-storage-amount'])&&is_numeric($_POST[$term->taxonomy .'-storage-amount'])){

			update_option('storage_amount_' . $term->slug, round(intval(sanitize_text_field($_POST[$term->taxonomy . '-storage-amount'])),0));			
		}
		
		if(isset($_POST[$term->taxonomy .'-storage-unit'])){

			$storage_units = LTPLE_Client()->get_storage_units();
			$storage_unit = sanitize_text_field($_POST[$term->taxonomy . '-storage-unit']);
			
			if(isset($periods[$period])){			
			
				update_option('storage_unit_' . $term->slug, $storage_unit);			
			}
		}
	}
	
	public function set_layer_taxonomy_columns($columns) {

		// Remove description, posts, wpseo columns
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] = '<input type="checkbox" />';
		$columns['name'] = 'Name';
		//$columns['slug'] = 'Slug';
		$columns['description'] = 'Description';
		$columns['price'] = 'Price';
		$columns['storage'] = 'Storage';
		//$columns['posts'] = 'Layers';
		//$columns['users'] = 'Users';

		return $columns;
	}
	
	public function set_account_taxonomy_columns($columns) {

		// Remove description, posts, wpseo columns
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] = '<input type="checkbox" />';
		$columns['name'] = 'Name';
		//$columns['slug'] = 'Slug';
		$columns['description'] = 'Description';
		$columns['price'] = 'Price';
		$columns['storage'] = 'Storage';
		//$columns['posts'] = 'Layers';
		//$columns['users'] = 'Users';

		return $columns;
	}
		
	public function add_layer_taxonomy_column_content($content, $column_name, $term_id){
	
		$term= get_term($term_id);

		if($column_name === 'price') {
			
			if(!$price_amount = get_option('price_amount_' . $term->slug)){
				
				$price_amount = 0;
			} 
			
			if(!$price_period = get_option('price_period_' . $term->slug)){
				
				$price_period = 'month';
			} 	
			
			$content.=$price_amount.'$'.' / '.$price_period;
		}
		elseif($column_name === 'storage') {
			
			if(!$storage_amount = get_option('storage_amount_' . $term->slug)){
				
				$storage_amount = 0;
			}
			
			if(!$storage_unit = get_option('storage_unit_' . $term->slug)){
				
				$storage_unit = 'templates';
			} 
			
			if($storage_unit=='templates'&&$storage_amount==1){
				
				$content.='+'.$storage_amount.' template';
			}
			elseif($storage_amount > 0){
				
				$content.='+'.$storage_amount.' '.$storage_unit;
			}
			else{
				
				$content.=$storage_amount.' '.$storage_unit;
			}
			
		}
		elseif($column_name === 'users') {
			
			$users=0;
			
			$content.=$users;
		}

		return $content;
	}
	
	public function add_account_taxonomy_column_content($content, $column_name, $term_id){
		
		return $this->add_layer_taxonomy_column_content($content, $column_name, $term_id);
	}
}