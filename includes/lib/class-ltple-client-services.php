<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Services extends LTPLE_Client_Object {
	
	public $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct( $parent ) {
		
		$this->parent = $parent;

		$this->parent->register_taxonomy( 'addon-service', __( 'Addon Services', 'live-template-editor-client' ), __( 'Addon Service', 'live-template-editor-client' ),  array('subscription-plan'), array(
			'hierarchical' 			=> true,
			'public' 				=> false,
			'show_ui' 				=> true,
			'show_in_nav_menus' 	=> false,
			'show_tagcloud' 		=> false,
			'meta_box_cb' 			=> null,
			'show_admin_column' 	=> true,
			'update_count_callback' => '',
			'show_in_rest'          => false,
			'rewrite' 				=> true,
			'sort' 					=> '',
		));	

		add_action('addon-service_add_form_fields', array( $this, 'get_new_service_fields' ) );
		add_action('addon-service_edit_form_fields', array( $this, 'get_service_fields' ) );
	
		add_filter('manage_edit-addon-service_columns', array( $this, 'set_service_taxonomy_columns' ) );
		add_filter('manage_addon-service_custom_column', array( $this, 'add_service_taxonomy_column_content' ),10,3);		
		
		add_action('create_addon-service', array( $this, 'save_service_fields' ) );
		add_action('edit_addon-service', array( $this, 'save_service_fields' ) );	

		add_filter('init', array( $this, 'init_service' ));
		
		add_action('wp_loaded', array($this,'get_service_types'));

	}
	
	public function get_service_types(){

		$this->types = $this->get_terms( 'addon-service', array(
				
			'hosting' => array(
			
				'name' 		=> 'Hosting',
				'children'	=> array(
				
					'domain-name' => array(
					
						'name' 			=> 'Domain Name',
						'options' 	=> array(
						
							'price_amount'	 => 20,
							'price_period'	 => 'year',
						),
					),
				)
			),
			'seo' => array(
			
				'name' 		=> 'SEO',
				'children'	=> array(
				
					'backlinks' => array(
					
						'name' 		=> 'Backlinks',
					),
				)
			),	
			'marketing' => array(
			
				'name' 		=> 'Marketing',
				'children'	=> array(
				
					'adwords-campaign' => array(
					
						'name' 		=> 'Adwords Campaign',
					),
				)
			),				
			
		));
	}

	public function init_service(){

		if( is_admin() ) {
			

		}
		else{
	
	
		}
	}

	public function get_new_service_fields($taxonomy_name){
		
		echo'<div class="form-field">';
			
			echo'<label for="'.$taxonomy_name.'-price-amount">Price</label>';

			echo $this->parent->plan->get_layer_price_fields($taxonomy_name,[]);
			
		echo'</div>';
	}
	
	public function get_service_fields($term){

		//collect our saved term field information
		
		$price=[];
		$price['price_amount'] = $this->parent->layer->get_plan_amount($term,'price'); 
		$price['price_period'] = $this->parent->layer->get_plan_amount($term,'price');
		
		//output our additional fields
		
		echo'<tr class="form-field">';
		
			echo'<th valign="top" scope="row">';
				
				echo'<label for="category-text">Price </label>';
			
			echo'</th>';
			
			echo'<td>';
				
				echo $this->parent->plan->get_layer_price_fields($term->taxonomy,$price);
				
			echo'</td>';
			
		echo'</tr>';
	}

	public function set_service_taxonomy_columns($columns) {

		// Remove description, posts, wpseo columns
		$columns = [];
		
		// Add artist-website, posts columns

		$columns['cb'] 			= '<input type="checkbox" />';
		$columns['name'] 		= 'Name';
		$columns['description'] = 'Description';
		$columns['price'] 		= 'Price';
		//$columns['storage'] = 'Storage';

		return $columns;
	}
		
	public function add_service_taxonomy_column_content($content, $column_name, $term_id){
	
		$term= get_term($term_id);

		if($column_name === 'price') {
			
			if(!$price_amount = $this->parent->layer->get_plan_amount($term->term_id,'price')){
				
				$price_amount = 0;
			} 
			
			if(!$price_period = $this->parent->layer->get_plan_period($term->term_id,'price')){
				
				$price_period = 'month';
			} 	
			
			$content.= $price_amount . '$' . ' / ' . $price_period;
		}

		return $content;
	}

	public function save_service_fields($term_id){

		if($this->parent->user->is_admin){
			
			//collect all term related data for this new taxonomy
			$term = get_term($term_id);
						
			//save our custom fields as wp-options
			
			if(isset($_POST[$term->taxonomy .'-price-amount'])&&is_numeric($_POST[$term->taxonomy .'-price-amount'])){

				$this->parent->layer->update_plan_amount($term->term_id,'price',round(intval(sanitize_text_field($_POST[$term->taxonomy . '-price-amount'])),1));
			}
			
			if(isset($_POST[$term->taxonomy .'-price-period'])){

				$periods = $this->parent->plan->get_price_periods();
				$period = sanitize_text_field($_POST[$term->taxonomy . '-price-period']);
				
				if(isset($periods[$period])){
					
					$this->parent->layer->update_plan_period($term->term_id,'price',$period);
				}
			}
		}
	}
}
