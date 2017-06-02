<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Admin_API {
	
	var $parent;
	
	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;
		
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 1 );
		
		do_action( 'updated_option', array( $this, 'settings_updated' ), 10, 3 );
	}

	/**
	 * Generate HTML for displaying fields
	 * @param  array   $field Field data
	 * @param  boolean $echo  Whether to echo the field HTML or return it
	 * @return void
	 */
	public function display_field ( $data = array(), $item = false, $echo = true ) {

		// Get field info
		if ( isset( $data['field'] ) ) {
			
			$field = $data['field'];
			
		} else {
			
			$field = $data;
		}

		// Check for prefix on option name
		
		$option_name = '';
		
		if ( isset( $data['prefix'] ) ) {
			
			$option_name = $data['prefix'];
		}

		// Get saved data
		$data = '';
		
		if ( !empty( $item->caps ) ) {
			
			// Get saved field data
			
			$option_name .= $field['id'];
			
			if( isset($item->{$field['id']}) ){
				
				$option = $item->{$field['id']};
			}
			else{
				
				$option = get_user_meta( $item->ID, $field['id'], true );
			}

			// Get data to display in field
			if ( isset( $option ) ) {
				
				$data = $option;
			}

		} 
		elseif ( !empty($item->ID) ) {

			// Get saved field data
			
			$option_name .= $field['id'];
			
			$option = get_post_meta( $item->ID, $field['id'], true );

			// Get data to display in field
			if ( isset( $option ) ) {
				$data = $option;
			}

		} 
		else{

			// Get saved option
			
			$option_name .= $field['id'];
			
			$option = get_option( $option_name );

			// Get data to display in field
			
			if ( isset( $option ) ) {
				
				$data = $option;
			}
		}
		
		// get field id
		
		$id = esc_attr( str_replace(array('[',']'),array('_',''),$field['id']) );
		
		// get field style
		
		$style = '';
		
		if( !empty($field['style']) ){
			
			$style = ' style="'.$field['style'].'"';
		}

		// Show default data if no option saved and default is supplied

		if ( empty($data) && isset( $field['default'] ) ) {
			
			$data = $field['default'];
			
		} 
		elseif ( $data === false ) {
			
			$data = '';
		}
		
		$disabled = ( ( isset($field['disabled']) && $field['disabled'] === true ) ? ' disabled="disabled"' : '' );

		$required = ( ( isset($field['required']) && $field['required'] === true ) ? ' required="true"' : '' );
		
		$html = '';

		switch( $field['type'] ) {

			case 'text':
			case 'url':
			case 'email':
				$html .= '<input' . $style . ' class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" '.$required.$disabled.'/>' . "\n";
			break;
			
			case 'slug':
				$html .= '<div class="input-group">' . "\n";
					$html .= '<span class="input-group-addon">'.home_url() . '/</span>' . "\n";
					$html .= '<input class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" '.$required.$disabled.'/>' . "\n";
					$html .= '<span class="input-group-addon">/</span>' . "\n";
				$html .= '</div>' . "\n";
			break;
			
			case 'margin':
				
				$value = esc_attr( $data );
				
				if($value == ''){
					
					$value = esc_attr( $field['default'] );
				}
				
				$html .= '<input class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $value . '" '.$required.$disabled.'/>' . "\n";
			break;

			case 'password':
			case 'number':
				$min = '';
				if ( isset( $field['min'] ) ) {
					$min = ' min="' . esc_attr( $field['min'] ) . '"';
				}

				$max = '';
				if ( isset( $field['max'] ) ) {
					$max = ' max="' . esc_attr( $field['max'] ) . '"';
				}
				$html .= '<input class="form-control" id="' . $id . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '"' . $min . '' . $max . ''.$required.$disabled.'/>' . "\n";
			break;
			case 'hidden':
				$html .= '<input class="form-control" id="' . $id . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $field['value'] ) . '"'.$required.'/>' . "\n";
			break;
			case 'text_secret':
				$html .= '<input class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" '.$required.$disabled.'/>' . "\n";
			break;

			case 'textarea':
				$html .= '<textarea'.$style.' class="form-control" id="' . $id . '" style="width:100%;height:300px;" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '"'.$required.$disabled.'>' . $data . '</textarea><br/>'. "\n";
			break;
			
			case 'switch':
				
				$checked = '';
				
				if ( $data && 'on' == $data ) {
					
					$checked = 'checked="checked"';
				
				}
				
				$html .= '<label class="switch">';
				
					$html .= '<input'.$style.' class="form-control" id="' . $id . '" type="checkbox" name="' . esc_attr( $option_name ) . '" ' . $checked . ''.$required.$disabled.'/>' . "\n";
					$html .= '<div class="slider round"></div>';
				
				$html .= '</label>';
				
			break;

			case 'checkbox':
				$checked = '';
				if ( $data && 'on' == $data ) {
					$checked = 'checked="checked"';
				}
				$html .= '<input'.$style.' class="form-control" id="' . $id . '" type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $option_name ) . '" ' . $checked . ''.$required.$disabled.'/>' . "\n";
			break;

			case 'checkbox_multi':
			
				foreach ( $field['options'] as $k => $v ) {
					
					$checked = false;
					if ( in_array( $k, (array) $data ) ) {
						$checked = true;
					}
					
					$html .= '<div for="' . esc_attr( $field['id'] . '_' . $k ) . '" class="checkbox_multi"><input class="form-control" type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" '.$required.$disabled.'/> ' . $v . '</div> ';
					$html .= '<br>';
				}
			break;
			
			case 'plan_value':
				
				$total_price_amount 	= $field['plan']['info']['total_price_amount'];
				$total_fee_amount 		= $field['plan']['info']['total_fee_amount'];
				$total_price_period		= $field['plan']['info']['total_price_period'];
				$total_fee_period		= $field['plan']['info']['total_fee_period'];
				$total_price_currency	= $field['plan']['info']['total_price_currency'];
				
				$html .= '<span style="color:red;font-weight:bold;font-size:20px;">';
				
					if( $total_fee_amount > 0 ){
						
						$html .= htmlentities(' ').round($total_fee_amount, 2).$total_price_currency.' '.$total_fee_period;
						$html .= '<br>+';
					}				
			
					$html .= round($total_price_amount, 2).$total_price_currency.' / '.$total_price_period;		
				
				$html .= '</span>';
				
			break;
			
			case 'edit_layer':
				
				$html .= '<div class="row">';
					
					$html .= '<div class="col-xs-6">';
					
						$html .= '<input' . $style . ' class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . esc_attr( $data ) . '" '.$required.$disabled.'/>' . "\n";
					
					$html .= '</div>';
					
					$html .= '<div class="col-xs-6 text-center">';
					
						if( !empty($data) && is_numeric($data) ){
							
							$html .= '<a href="' . $this->parent->urls->editor . '?uri=' . $_GET['post'] . '" target="_blank" class="button button-primary button-hero">';
								
								$html .= 'Edit with LTPLE';
								
							$html .= '</a>';
						}
					
					$html .= '</div>';
					
				$html .= '</div>';
				
				//$html .= '<hr/>';
				
				if( empty($data) || !is_numeric($data) ){

					$layers = get_posts(array( 
				
						'post_type' 	=> 'cb-default-layer', 
						'posts_per_page'=> -1				
					));
					
					if( !empty( $layers ) ){
						
						$items = [];
						
						foreach( $layers as $layer ){
							
							$terms = wp_get_object_terms( $layer->ID, 'layer-type' );
							
							if(!empty($terms[0]->slug)){
								
								$layer_type=$terms[0]->slug;
							}
							else{
								
								$layer_type = 'Layer';
							}
							
							$item = '';
							
							$item.='<div class="' . implode( ' ', get_post_class("col-xs-12 col-sm-6 col-md-4",$layer->ID) ) . '" id="post-' . $layer->ID . '">';
								
								$item.='<div class="panel panel-default" style="border-left:1px solid #DDD;">';
									
									$item.='<div class="panel-heading">';
										
										$item.='<b>' . $layer->post_title . '</b>';
										
									$item.='</div>';

									$item.='<div class="panel-body">';
										
										$item.='<div class="thumb_wrapper" style="background:#ffffff;height:125px;overflow:hidden;">';
										
											//$item.= '<a class="entry-thumbnail" href="'. $permalink .'" target="_blank" title="'. $layer_title .'">';

											if ( $image_id = get_post_thumbnail_id( $layer->ID ) ){
												
												if ($src = wp_get_attachment_image_src( $image_id, 'full' )){

													$item.= '<img style="width:100%;" class="lazy" data-original="' . $src[0] . '"/>';
												}
											
											}
											//$item.= '</a>';
										
										$item.='</div>'; //thumb_wrapper
										
									$item.='</div>';
									
									$item.='<div class="panel-footer text-right">';

										if( intval($data) == $layer->ID ){

											$item.='<button type="button" class="btn btn-xs btn-success layer-selected" data-toggle="layer" data-target="'.$layer->ID.'">'.PHP_EOL;
												
												$item.='Selected'.PHP_EOL;
											
											$item.='</button>'.PHP_EOL;																			
										}
										else{
											
											$item.='<button type="button" class="btn btn-xs btn-warning" data-toggle="layer" data-target="'.$layer->ID.'">'.PHP_EOL;
												
												$item.='Select'.PHP_EOL;
											
											$item.='</button>'.PHP_EOL;										
										}

									$item.='</div>';
								
								$item.='</div>';
								
							$item.='</div>';

							$items[$layer_type][]=$item;
						}
						
						if( !empty($items) ){
							
							$html .= '<ul class="nav nav-tabs" role="tablist" style="margin-top:10px;">';

								$active=' class="active"';
								
								foreach($items as $type => $type_items){
									
									$html .= '<li role="presentation"'.$active.'><a href="#' . $type . '" aria-controls="' . $type . '" role="tab" data-toggle="tab">'.strtoupper(str_replace(array('-','_'),' ',$type)).'</a></li>';
									
									$active='';
								}

							$html .= '</ul>';	

							$html .= '<div class="tab-content row" style="margin-top:10px;">';

								$active=' active';
							
								foreach($items as $type => $type_items){
									
									$html .= '<div role="tabpanel" class="tab-pane'.$active.'" id="' . $type . '">';
									
									foreach($type_items as $item){

										$html .= $item;
									}
									
									$html .= '</div>';
									
									$active='';
								}
								
							$html .= '</div>';
							
							$html .= '<script>';
							
								$html .= ';(function($){';
									
									$html .= '$(document).ready(function(){';

										$html .= '$(\'[data-toggle="layer"]\').on(\'click\', function (e) {';
											
											$html .= '$(".layer-selected").html("Select").removeClass("btn-success layer-selected").addClass("btn-warning");';
											
											$html .= '$(this).html("Selected").removeClass("btn-warning").addClass("btn-success layer-selected");';
											
											$html .= '$("#defaultLayerId").val($(this).data(\'target\'));';
											
										$html .= '});';							
									
									$html .= '});';
									
								$html .= '})(jQuery);';								
							
							$html .= '</script>';
						}
					}
				}

			break;
			
			case 'checkbox_multi_plan_options':
				
				$total_price_amount 	= 0;
				$total_fee_amount 		= 0;
				$total_price_period		='month';
				$total_fee_period		='once';
				$total_price_currency	='$';
				
				$html .= '<table class="widefat fixed striped" style="border:none;">';
				
				foreach ( $field['options'] as $taxonomy => $terms ) {
					
					$html .= '<tr>';
						
						$html .= '<th style="width:200px;">';
							
							$html .= '<div for="' . $taxonomy . '">'.$taxonomy.'</div> ';
								
						$html .= '</th>';
						
						$html .= '<td style="width:250px;">';
						
						foreach($terms as $term){

							$checked = false;
							
							if ( in_array( $term->slug, (array) $data ) ) {
								
								$checked = true;
							}
							
							$html .= '<span style="display:block;padding:1px 0;margin:0;">';
								
								$html .= '<div for="' . esc_attr( $field['id'] . '_' . $term->slug ) . '" class="checkbox_multi"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '[]" value="' . esc_attr( $term->slug ) . '" id="' . esc_attr( $field['id'] . '_' . $term->slug ) . '" /> ' . $term->name . '</div> ';
							
							$html .= '</span>';
						}
						
						$html .= '</td>';

						$html .= '<td>';
						
							$taxonomy_options = [];
							
							foreach($terms as $i => $term){
							
								$taxonomy_options[$i] = $this->parent->layer->get_options( $taxonomy, $term );
								
								if ( in_array( $term->slug, (array) $data ) ) {
									
									$total_fee_amount 	= $this->parent->plan->sum_custom_taxonomy_total_price_amount( $total_fee_amount, $taxonomy_options[$i], $total_fee_period);
									$total_price_amount = $this->parent->plan->sum_custom_taxonomy_total_price_amount( $total_price_amount, $taxonomy_options[$i], $total_price_period);
									$total_storage 		= $this->parent->plan->sum_custom_taxonomy_total_storage( $total_storage, $taxonomy_options[$i]);
								}

								$html .= '<span style="display:block;padding:1px 0 3px 0;margin:0;">';
								
									if($taxonomy_options[$i]['storage_unit']=='templates'&&$taxonomy_options[$i]['storage_amount']==1){
										
										$html .= '+'.$taxonomy_options[$i]['storage_amount'].' template';
									}
									elseif($taxonomy_options[$i]['storage_amount']>0){
										
										$html .= '+'.$taxonomy_options[$i]['storage_amount'].' '.$taxonomy_options[$i]['storage_unit'];
									}	
									else{
										
										$html .= $taxonomy_options[$i]['storage_amount'].' '.$taxonomy_options[$i]['storage_unit'];
									}														
						
								$html .= '</span>';
							}
						
						$html .= '</td>';
						
						$html .= '<td>';
						
						foreach($terms as $i => $term){
							
							$html .= '<span style="display:block;padding:1px 0 3px 0;margin:0;">';
							
								$html .= $taxonomy_options[$i]['price_amount'].$taxonomy_options[$i]['price_currency'].' / '.$taxonomy_options[$i]['price_period'];							
						
							$html .= '</span>';
						}
						
						$html .= '</td>';
						
					$html .= '</tr>';
						
				}

				$html .= '<tr style="font-weight:bold;">';
					
					$html .= '<th style="width:200px;">';
						
						$html .= '<div style="font-weight:bold;" for="totals">TOTALS</div> ';
							
					$html .= '</th>';
					
					$html .= '<td style="width:250px;"></td>';
					
					$html .= '<td>';
						
						if(!empty($total_storage)){
							
							foreach($total_storage as $storage_unit => $total_storage_amount){
								
								$html .= '<span style="display:block;">';
								
									if($storage_unit=='templates'&&$total_storage_amount==1){
										
										$html .= '+'.$total_storage_amount.' template';
									}
									elseif($total_storage_amount>0){
										
										$html .= '+'.$total_storage_amount.' '.$storage_unit;
									}									
									else{
										
										$html .= $total_storage_amount.' '.$storage_unit;
									}
									
								$html .= '</span>';
							}							
						}
						
					$html .= '</td>'; 
					
					$html .= '<td>';

						if( $total_fee_amount > 0 ){
							
							$html .= htmlentities(' ').round($total_fee_amount, 2).$total_price_currency.' '.$total_fee_period;
							$html .= '<br>+';
						}
		
						$html .= round($total_price_amount, 2).$total_price_currency.' / '.$total_price_period;

					$html .= '</td>';				
				
				$html .= '</table>';
				
			break;
			
			case 'addon_plugins':
				
				$html .= '<div id="the-list">';
				
					foreach( $this->parent->settings->addons as $addon ){
				
						$html .= '<div class="panel panel-default plugin-card plugin-card-akismet">';
						
							$html .= '<div class="panel-body plugin-card-top">';
							
								$html .= '<h3>';
								
									$html .= '<a href="'.$addon['addon_link'].'" class="thickbox open-plugin-details-modal">';
										
										$html .= $addon['title'];	
										
									$html .= '</a>';
									
								$html .= '</h3>';
								
								$html .= '<p>'.$addon['description'].'</p>';
								$html .= '<p class="authors"> <cite>By <a target="_blank" href="'.$addon['author_link'].'">'.$addon['author'].'</a></cite></p>';
								
							$html .= '</div>';
							
							$html .= '<div class="panel-footer plugin-card-bottom text-right">';
								
								$plugin_file = $addon['addon_name'] . '/' . $addon['addon_name'] . '.php';
								
								if( !file_exists( WP_PLUGIN_DIR . '/' . $addon['addon_name'] . '/' . $addon['addon_name'] . '.php' ) ){
									
									$url = $addon['source_url'];
									
									$html .= '<a href="' . $url . '" class="button install-now" aria-label="Install">Install Now</a>';
								}
								else{
									
									if( !empty($_GET['action']) && !empty($_GET['plugin']) && file_exists( WP_PLUGIN_DIR . '/' . $_GET['plugin'] ) ){
										
										// do activation deactivation

										$is_activate = is_plugin_active( $_GET['plugin'] );
										
										if( $_GET['action'] == 'activate' && !$is_activate ){
											
											activate_plugin($_GET['plugin']);
										}
										elseif( $_GET['action'] == 'deactivate' && $is_activate ){
											
											deactivate_plugins($_GET['plugin']);
										}
									}
									
									// output button
									
									if( is_plugin_active( $addon['addon_name'] . '/' . $addon['addon_name'] . '.php' ) ){

										//$url = wp_nonce_url( 'http://ltple.recuweb.com/wp-admin/plugins.php?action=deactivate&plugin='.urlencode( $plugin_file ), 'deactivate-plugin_' . $plugin_file );
									
										$url = add_query_arg( array(
											'action' => 'deactivate',
											'plugin' => urlencode( $plugin_file ),
										), $this->parent->urls->current );
											
										$html .= '<a href="'.$url.'" class="button deactivate-now" aria-label="Deactivate">Deactivate</a>';
									}
									else{
										
										//$url = wp_nonce_url( 'http://ltple.recuweb.com/wp-admin/plugins.php?action=activate&plugin='.urlencode( $plugin_file ), 'activate-plugin_' . $plugin_file );
										
										$url = add_query_arg( array(
											'action' => 'activate',
											'plugin' => urlencode( $plugin_file ),
										), $this->parent->urls->current );									
										
										$html .= '<a href="'.$url.'" class="button activate-now" aria-label="Activate">Activate</a>';
									}
								}
							
							$html .= '</div>';
						
						$html .= '</div>';
					}
				
				$html .= '</div>';
			
			break;
			
			case 'email_series':
			
				if( isset($data['model']) && isset($data['days']) ){
					
					$email_series = $data;
				}
				else{
					
					$email_series = ['model' => [ 0 => '' ], 'days' => [ 0 => 0 ]];
				}
				
				$html .= '<div id="email_series" class="sortable">';
					
					$html .= ' <a href="#" class="add-input-group" style="line-height:40px;">Add email</a>';
				
					$html .= '<ul class="input-group ui-sortable">';
						
						foreach( $email_series['model'] as $e => $model) {
									
							if($e > 0){
								
								$class='input-group-row ui-state-default ui-sortable-handle';
							}
							else{
								
								$class='input-group-row ui-state-default ui-state-disabled';
							}

							$html .= '<li class="'.$class.'">';
						
								$html .= 'Send  ';
								
								$html .= '<select style="width:350px;" name="email_series[model][]" id="plan_email_model">';

								foreach ( $field['email-models'] as $k => $v ) {
									
									$selected = false;
									
									if ( $k == $model ) {
										
										$selected = true;
									}
									elseif( isset($field['model-selected']) && $field['model-selected'] == $k ){
										
										$selected = true;
									}
									
									$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
								}
								
								$html .= '</select> ';

								$html .= ' + ';
								
								$html .= '<input type="number" step="1" min="0" max="1000" placeholder="0" name="email_series[days][]" id="plan_email_days" style="width: 50px;" value="'.$email_series['days'][$e].'">';
								
								$html .= ' day(s) after triggered ';
								
								if( $e > 0 ){
									
									$html .= '<a class="remove-input-group" href="#">[ x ]</a> ';
								}
								

							$html .= '</li>';						
						}
						
					$html .= '</ul>';
					
				$html .= '</div>';

			break;
			
			case 'key_value':

				if( !isset($data['key']) || !isset($data['value']) ){

					$data = ['key' => [ 0 => '' ], 'value' => [ 0 => '' ]];
				}

				if( !empty($field['inputs']) && is_string($field['inputs']) ){
					
					$inputs = [$field['inputs']];
				}
				elseif(empty($field['inputs'])||!is_array($field['inputs'])) {
					
					$inputs = ['string','text','number','password','url','parameter','xpath','attribute','folder','filename'];
				}				
				else{
				
					$inputs = $field['inputs'];
				}

				$html .= '<div id="'.$field['id'].'" class="sortable">';
					
					$html .= ' <a href="#" class="add-input-group" style="line-height:40px;">Add field</a>';
				
					$html .= '<ul class="input-group ui-sortable">';
						
						foreach( $data['key'] as $e => $key) {

							if($e > 0){
								
								$class='input-group-row ui-state-default ui-sortable-handle';
							}
							else{
								
								$class='input-group-row ui-state-default ui-state-disabled';
							}
						
							$value = str_replace('\\\'','\'',$data['value'][$e]);
									
							$html .= '<li class="'.$class.'" style="display:inline-block;width:100%;">';
						
								$html .= '<select name="'.$option_name.'[input][]" style="float:left;">';

								foreach ( $inputs as $input ) {
									
									$selected = false;
									if ( isset($data['input'][$e]) && $data['input'][$e] == $input ) {
										
										$selected = true;
									}
									
									$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $input ) . '">' . $input . '</option>';
								}
								
								$html .= '</select> ';
						
								$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['key']) ? $field['placeholder']['key'] : 'key' ).'" name="'.$option_name.'[key][]" style="width:30%;float:left;" value="'.$data['key'][$e].'">';
								
								$html .= '<span style="float:left;"> => </span>';
								
								if(isset($data['input'][$e])){
									
									if($data['input'][$e] == 'number'){
										
										$html .= '<input type="number" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'number' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
									}
									elseif($data['input'][$e] == 'password'){
										
										$html .= '<input type="password" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'password' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
									}
									elseif($data['input'][$e] == 'text'){
										
										$html .= '<textarea placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'text' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;height:200px;">' . $value . '</textarea>';
									}										
									else{
										
										$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'value' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
									}
								}
								else{
									
									$html .= '<input type="text" placeholder="'.( !empty($field['placeholder']['value']) ? $field['placeholder']['value'] : 'value' ).'" name="'.$option_name.'[value][]" style="width:30%;float:left;" value="'.$value.'">';
								}

								if( $e > 0 ){
									
									$html .= '<a class="remove-input-group" href="#">[ x ]</a> ';
								}

							$html .= '</li>';						
						}
					
					$html .= '</ul>';					
					
				$html .= '</div>';

			break;
			
			case 'domain':
				
				$exts = array('.com','.net','.org');
				
				$html .= '<div class="input-group">';
					
					$html .= '<input class="form-control" id="' . $id . '" type="text" name="' . esc_attr( $option_name ) . '[domain_name][name][]" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="" '.$required.$disabled.'/>' . "\n";

					$html .= '<span	class="input-group-addon" style="background:#fff;">';
					
						$html .= '<select name="'.esc_attr( $option_name ).'[domain_name][ext][]" style="border:none;">';

							foreach ( $exts as $ext ) {
								
								$selected = false;
								if ( isset($data['ext']) && $data['ext'] == $ext ) {
									
									$selected = true;
								}
								
								$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $ext ) . '">' . $ext . '</option>';
							}
						
						$html .= '</select> ';
					
					$html .= '</span>';
					
					$html .= '<input type="hidden" name="valid_domain[]" value="'.$field['id'].'" />';

				$html .= '</div>';
			
			break;

			case 'form':

				if( !isset($data['name']) || !isset($data['value']) ){

					$data = array(
					
						'name' 		=> [ 0 => '' ],
						'required' 	=> [ 0 => '' ],
						'value' 	=> [ 0 => '' ],
					);
				}

				$inputs 	= ['title','label','text','textarea','number','password','domain','submit'];
				$required 	= ['required','optional'];
				$id 		= ( !empty($field['id']) ? $field['id'] : 'form' );
				
				$html .= '<div id="'.$id.'" class="sortable">';
					
					if( !isset($field['action']) ){
					
						$html .= ' <a href="#" class="add-input-group" style="line-height:40px;">Add field</a>';
					
						$html .= '<ul class="input-group ui-sortable">';
							
							foreach( $data['name'] as $e => $name) {
								
								if($e > 0){
									
									$class='input-group-row ui-state-default ui-sortable-handle';
								}
								else{
									
									$class='input-group-row ui-state-default ui-state-disabled';
								}								
								
								$req_val 	= ( isset($data['required'][$e]) ? str_replace('\\\'','\'',$data['required'][$e]): 'optional');
								$value 		= ( isset($data['value'][$e]) 	 ? str_replace('\\\'','\'',$data['value'][$e]) 	 : '');
										
								$html .= '<li class="'.$class.'" style="display:inline-block;width:100%;">';
							
									// inputs
							
									$html .= '<select name="'.$field['name'].'[input][]" style="float:left;">';

										foreach ( $inputs as $input ) {
											
											$selected = false;
											if ( isset($data['input'][$e]) && $data['input'][$e] == $input ) {
												
												$selected = true;
											}
											
											$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $input ) . '">' . $input . '</option>';
										}
									
									$html .= '</select> ';
									
									// required
							
									if ( isset($data['input'][$e]) && in_array($data['input'][$e],['title','label','submit']) ) {

										$disabled = ' disabled="disabled"';
									}
									else{
										
										$disabled = '';
									}
									
									$html .= '<select name="'.$field['name'].'[required][]" style="float:left;"'.$disabled.'>';

										foreach ( $required as $r ) {
											
											$selected = false;
											if ( empty($disabled) && isset($data['required'][$e]) && $data['required'][$e] == $r ) {
												
												$selected = true;
											}
											
											$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $r ) . '">' . $r . '</option>';
										}
									
									$html .= '</select> ';
							
									if( isset($data['input'][$e]) && $data['input'][$e] == 'domain'){
										
										$html .= '<input type="text" style="width:30%;float:left;" value="domain_name" disabled="true">';
										$html .= '<input type="hidden" name="'.$field['name'].'[name][]" value="domain_name">'; 
									}	
									else{
										
										$html .= '<input type="text" placeholder="name" name="'.$field['name'].'[name][]" style="width:30%;float:left;" value="'.$data['name'][$e].'">';
									}
									
									$html .= '<span style="float:left;"> => </span>';
									
									if(isset($data['input'][$e])){
										
										if($data['input'][$e] == 'number'){
											
											$html .= '<input type="number" placeholder="number" name="'.$field['name'].'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
										elseif($data['input'][$e] == 'password'){
											
											$html .= '<input type="password" placeholder="password" name="'.$field['name'].'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
										elseif($data['input'][$e] == 'textarea'){
											
											$html .= '<textarea placeholder="text" name="'.$field['name'].'[value][]" style="width:30%;float:left;height:200px;">' . $value . '</textarea>';
										}									
										else{
											
											$html .= '<input type="text" placeholder="value" name="'.$field['name'].'[value][]" style="width:30%;float:left;" value="'.$value.'">';
										}
									}
									else{
										
										$html .= '<input type="text" placeholder="value" name="'.$field['name'].'[value][]" style="width:30%;float:left;" value="'.$value.'">';
									}

									if( $e > 0 ){
										
										$html .= '<a class="remove-input-group" href="#">[ x ]</a> ';
									}

								$html .= '</li>';						
							}
							
						$html .= '</ul>';
					
					}
					else{
						
						$method = ( ( isset($field['method']) && $field['method'] == 'post' ) ? 'post' : 'get' );
						
						$html .= '<form action="'.$field['action'].'" method="'.$method.'">';

						foreach( $data['name'] as $e => $name) {
							
							if(isset($data['input'][$e])){
								
								$required = ( ( empty($data['required'][$e]) || $data['required'][$e] == 'required' ) ? true : false );
								
								if($data['input'][$e] == 'title'){
									
									$html .= '<h4 id="'.ucfirst($name).'">'.ucfirst(ucfirst($data['value'][$e])).'</h4>';
								}
								elseif($data['input'][$e] == 'label'){
									
									$html .= '<div id="'.ucfirst($name).'">'.ucfirst(ucfirst($data['value'][$e])).'</div>';
								}
								elseif($data['input'][$e] == 'submit'){
									
									$html .= '<div class="form-group">';
									
										$html .= '<button type="'.$data['input'][$e].'" id="'.ucfirst($data['name'][$e]).'" class="control-input pull-right btn btn-primary">'.ucfirst(ucfirst($data['value'][$e])).'</button>';
									
									$html .= '</div>';
								}
								elseif( $data['input'][$e] == 'domain' ){

									$html .= $this->display_field( array(
							
										'type'				=> $data['input'][$e],
										'id'				=> $field['id'],
										'value' 			=> $data['value'][$e],
										'required' 			=> $required,
										'placeholder' 		=> '',
										'description'		=> ''
										
									), false, false ); 									
								}	
								else{
									
									$html .= $this->display_field( array(
							
										'type'				=> $data['input'][$e],
										'id'				=> $id.'['.$name.']',
										'value' 			=> $data['value'][$e],
										'required' 			=> $required,
										'placeholder' 		=> '',
										'description'		=> ''
										
									), false, false ); 
								}
							}							
						}
						
						$html .= '</form>';
					}
					
				$html .= '</div>';

			break;			
			
			case 'radio':
				
				$i = 0;
				
				foreach ( $field['options'] as $k => $v ) {
					
					$checked = false;
					
					if( $k == $data || ( empty($data) && $i == 0 ) ) {
						
						$checked = true;
					}
					
					$html .= '<div for="' . esc_attr( $field['id'] . '_' . $k ) . '">';
					
					$html .= '<input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ';
						
						$html .= $v; 
						
					$html .= '</div> ';
					
					if( isset($field['inline']) && $field['inline'] === false ){
						
						$html .= '<br>'; 
					}
					
					$i++;
				}
				
			break;
			
			case 'avatar':
				
				$checked = array();
				
				foreach ( $field['options'] as $k => $v ) {

					if( $k === 0){
					
						$checked[$k] = true;
					}
					else{
						
						$checked[$k] = false;
						
						if ( $v == $data ) {
							
							$checked[$k] 	= true;
							$checked[0] 	= false;
						}						
					}		
				}
				
				foreach ( $field['options'] as $k => $v ) {

					$html .= '<div for="' . esc_attr( $field['id'] . '_' . $k ) . '" style="width:50px;text-align:center;">';
					
						$html .= '<img src="'.$v.'" height="50" width="50" title="My picture '.( $k + 1 ).'" />'; 
					
						$html .= '<input type="radio" ' . checked( $checked[$k], true, false ) . ' name="' . esc_attr( $option_name ) . '" value="' . esc_attr( $v ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" />';

					$html .= '</div> ';
				}
				
			break;
			
			case 'select':
				
				$html .= '<div class="input-group">';
				
					if(isset($field['name'])){
						
						$html .= '<select class="form-control" name="' . $field['name'] . '" id="' . $id . '"'.$required.$disabled.'>';
					}
					else{
						
						$html .= '<select class="form-control" name="' . esc_attr( $option_name ) . '" id="' . $id . '"'.$required.$disabled.'>';
					}

					foreach ( $field['options'] as $k => $v ) {
						$selected = false;
						if ( $k == $data ) {
							
							$selected = true;
						}
						elseif(isset($field['selected']) && $field['selected'] == $k ){
							
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
					}
					$html .= '</select> ';
					
				$html .= '</div>';
				
			break;

			case 'select_multi':
				$html .= '<select name="' . esc_attr( $option_name ) . '[]" id="' . $id . '" multiple="multiple">';
				foreach ( $field['options'] as $k => $v ) {
					$selected = false;
					if ( in_array( $k, (array) $data ) ) {
						$selected = true;
					}
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';
			break;
			
			case 'dropdown_categories':

				$html .=wp_dropdown_categories(array(
				
					'show_option_none' => 'None',
					'taxonomy'     => $field['taxonomy'],
					'name'    	   => $field['name'],
					'show_count'   => false,
					'hierarchical' => true,
					'selected'     => $field['selected'],
					'echo'		   => false,
					'class'		   => 'form-control',
					'hide_empty'   => false
				));			
			
			break;			
			
			case 'dropdown_main_apps':
			
				//get admin IDs
				
				$users = get_users(array('role' => 'administrator'));
				
				$ids=[];
				
				foreach($users as $user){
					
					$ids[]=$user->ID;
				}

				//get app accounts
				
				$apps = get_posts(array(
				
					'author__in'  => $ids,
					'post_type'   => 'user-app',
					'post_status' => 'publish',
					'numberposts' => -1
				));
				
				$selected_id 	= get_option( $this->parent->_base . $field['id'] );
				$options 		= array( -1 => 'none');
				
				foreach($apps as $app){

					if(strpos($app->post_name, $field['app'] . '-')===0){
						
						$options[$app->ID] = str_replace($field['app'].' - ','',$app->post_title);
					}
				}
				
				if(isset($field['name'])){
					
					$html .= '<select name="' . $field['name'] . '" id="' . $id . '">';
				}
				else{
					
					$html .= '<select name="' . esc_attr( $option_name ) . '" id="' . $id . '">';
				}

				foreach ( $options as $k => $v ) {
					
					$selected = false;
					
					if ( $k == $data ) {
						
						$selected = true;
					}
					elseif($selected_id == $k ){
						
						$selected = true;
					}
					
					$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
				}
				$html .= '</select> ';

			break;

			case 'action_schedule':
				
				$html .= '<div id="'.$option_name.'">';

					$html .= ucfirst($field['action']).' ';				
					
					if( !empty($field['appId']) ){
						
						$html .= '<input type="hidden" name="'.$option_name.'[args][]" id="'.$option_name.'_app_id" value="'.$field['appId'].'"> ';
					}							
					
					if( $field['last'] === true ){
					
						$html .= 'last ';
					
						$html .= '<input type="number" step="1" min="0" max="100" placeholder="0" name="'.$option_name.'[args][]" id="'.$option_name.'_last" style="width: 50px;" value="'.( !empty($data['args'][1]) ? $data['args'][1] : 10 ).'"> ';
						
						$html .= ucfirst($field['unit']).' ';
					}
					
					$html .= 'every ';
					
					$html .= '<input type="number" step="5" min="15" max="60" placeholder="0" name="'.$option_name.'[every]" id="'.$option_name.'_every" style="width: 50px;" value="'.( isset($data['every']) ? $data['every'] : 15 ).'"> ';
					
					$html .= 'minutes ';

				$html .= '</div>';

			break;

			case 'image':
				$image_thumb = '';
				if ( $data ) {
					$image_thumb = wp_get_attachment_thumb_url( $data );
				}
				$html .= '<img id="' . $option_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
				$html .= '<input id="' . $option_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'live-template-editor-client' ) . '" data-uploader_button_text="' . __( 'Use image' , 'live-template-editor-client' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'live-template-editor-client' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'live-template-editor-client' ) . '" />' . "\n";
				$html .= '<input id="' . $option_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
			break;
			
			case 'color':
				?><div class="color-picker" style="position:relative;">
			        <input type="text" name="<?php esc_attr_e( $option_name ); ?>" class="color form-control" value="<?php esc_attr_e( $data ); ?>" />
			        <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
			    </div>
			    <?php
			break;

		}

		//output description
		
		switch( $field['type'] ) {

			case 'checkbox_multi':
			case 'radio':
			case 'select_multi':
				$html .= '<br/><span class="description">' . $field['description'] . '</span>';
			break;

			default:
				
				if(!empty($field['description'])){
				
					if ( ! $item ) {
						
						$html .= '<div for="' . $id . '">' . "\n";
					}

					$html .= '<div><i style="color:#aaa;">' . $field['description'] . '</i></div>' . "\n";

					if ( ! $item ) {
						
						$html .= '</div>' . "\n";
					}
				}
				
			break;
		}

		if ( ! $echo ) {
			return $html;
		}

		echo $html;

	}

	/**
	 * Validate form field
	 * @param  string $data Submitted value
	 * @param  string $type Type of field to validate
	 * @return string       Validated value
	 */
	public function validate_field ( $data = '', $type = 'text' ) {

		switch( $type ) {
			case 'text'	: $data = esc_attr( $data ); break;
			case 'url'	: $data = esc_url( $data ); break;
			case 'email': $data = is_email( $data ); break;
		}

		return $data;
	}

	/**
	 * Add meta box to the dashboard
	 * @param string $id            Unique ID for metabox
	 * @param string $title         Display title of metabox
	 * @param array  $post_types    Post types to which this metabox applies
	 * @param string $context       Context in which to display this metabox ('advanced' or 'side')
	 * @param string $priority      Priority of this metabox ('default', 'low' or 'high')
	 * @param array  $callback_args Any axtra arguments that will be passed to the display function for this metabox
	 * @return void
	 */
	public function add_meta_box ( $id = '', $title = '', $post_types = array(), $context = 'advanced', $priority = 'default', $callback_args = null ) {

		// Get post type(s)
		if ( ! is_array( $post_types ) ) {
			
			$post_types = array( $post_types );
		}

		// Generate each metabox
		foreach ( $post_types as $post_type ) {
			
			add_meta_box( $id, $title, array( $this, 'meta_box_content' ), $post_type, $context, $priority, $callback_args );
		}
	}

	/**
	 * Display metabox content
	 * @param  object $post Post object
	 * @param  array  $args Arguments unique to this metabox
	 * @return void
	 */
	public function meta_box_content ( $post, $args ) {

		$fields = apply_filters( $post->post_type . '_custom_fields', array(), $post->post_type );

		if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

		echo '<div class="custom-field-panel">' . "\n";

		foreach ( $fields as $field ) {

			if ( ! isset( $field['metabox'] ) ) continue;

			if ( ! is_array( $field['metabox'] ) ) {
				
				$field['metabox'] = array( $field['metabox'] );
			}

			if ( in_array( $args['id'], $field['metabox'] ) ) {

				$this->display_meta_box_field( $field, $post );
			}
		}

		echo '</div>' . "\n";
	}

	/**
	 * Dispay field in metabox
	 * @param  array  $field Field data
	 * @param  object $post  Post object
	 * @return void
	 */
	public function display_meta_box_field ( $field = array(), $post ) {

		if ( ! is_array( $field ) || 0 == count( $field ) ) return;

		$meta_box  = '<p class="form-field form-group">' . PHP_EOL;
		
			$meta_box .= '<div for="' . $field['id'] . '">' . $field['label'] . '</div> ' . PHP_EOL;
			
			$meta_box .= $this->display_field( $field, $post, false ) . PHP_EOL;
			
		$meta_box .= '</p>' . PHP_EOL;

		echo $meta_box;
	}

	/**
	 * Save metabox fields
	 * @param  integer $post_id Post ID
	 * @return void
	 */
	public function save_meta_boxes ( $post_id = 0 ) {

		if ( ! $post_id ) return;

		$post_type = get_post_type( $post_id );

		$fields = apply_filters( $post_type . '_custom_fields', array(), $post_type );

		if ( ! is_array( $fields ) || 0 == count( $fields ) ) return;

		foreach ( $fields as $field ) {
			
			if ( isset( $_REQUEST[ $field['id'] ] ) ) {
				
				update_post_meta( $post_id, $field['id'], $this->validate_field( $_REQUEST[ $field['id'] ], $field['type'] ) );
			} 
			else {
				
				update_post_meta( $post_id, $field['id'], '' );
			}
		}
	}
}
