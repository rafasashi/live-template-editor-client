<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Client_Json_API {
	
	var $parent;

	/**
	 * Constructor function
	 */
	public function __construct ( $parent ) {
		
		$this->parent 	= $parent;

		
	}

	public function get_url( $action='leads/list', $user_id = 0, $args=[] ){
		
		$url = $this->parent->urls->editor . '?api='.$action;
		
		if( is_numeric($user_id) && $user_id !== 0 ){
			
			$url .= '/' . $user_id;
		}
		
		if(!empty($args)){
			
			foreach($args as $key => $value){
				
				$url .= '&' . $key . '=' . $value;
			}
		}
		
		return $url;
	}
	
	public function get_table( $api_url, $fields, $trash=false, $export=true, $search=true, $toggle=true, $columns=true, $header=true, $pagination=true, $form=true, $toolbar = 'toolbar' ){
						
		$show_toolbar = ( ( $search || $export || $toggle || $columns ) ? true : false );
								
		if(!$show_toolbar){
			
			echo'<style>#'.$toolbar.'{display:none;}</style>';
		}
		
		if($form){
		
			echo '<form id="tableForm" action="' . $this->parent->urls->current . '" method="post">';
		}
	
		echo '<div id="'.$toolbar.'" class="btn-group">';
			
			/*
			echo '<button id="add" type="button" class="btn btn-default">';
				echo '<i class="glyphicon glyphicon-plus"></i>';
			echo '</button>';
			
			echo '<button id="like" type="button" class="btn btn-default">';
				echo '<i class="glyphicon glyphicon-heart"></i>';
			echo '</button>';
			*/
			
			if($trash){
			
				echo '<button id="trash" type="button" class="btn btn-default">';
					echo '<i class="glyphicon glyphicon-trash"></i>';
				echo '</button>';
			}
			
			if($export){
			
				echo '<button id="export" class="btn btn-default">';
				
					echo '<i class="glyphicon glyphicon-export"></i>';
					
				echo '</button>';
			}
			
		echo '</div>';
		
		echo '<table id="table" class="table table-striped" ';
			echo 'data-toggle="table" ';
			//echo 'data-height="400" ';
			echo 'data-url="' . $api_url . '" ';
			echo 'data-pagination="'.( $pagination ? 'true' : 'false' ).'" ';
			//echo 'data-side-pagination="server" ';
			echo 'data-page-size="20" ';
			echo 'data-page-list="[20, 50, 100, 200, 500]" ';					
			echo 'data-search="'.( $search ? 'true' : 'false' ).'" ';
			echo 'data-show-header="'.( $header ? 'true' : 'false' ).'" ';
			echo 'data-show-toggle="'.( $toggle ? 'true' : 'false' ).'" ';
			echo 'data-show-columns="'.( $columns ? 'true' : 'false' ).'" ';
			echo 'data-show-export="'.( $export ? 'true' : 'false' ).'" ';
			echo 'data-show-refresh="false" ';
			//echo 'data-sort-order="desc" ';   
			//echo 'data-sort-name="description" ';
			echo ( $show_toolbar ? 'data-toolbar="#'.$toolbar.'" ' : '' );
		echo '>';
			echo '<thead>';
			echo '<tr>';
			
				foreach($fields as $field){
					
					echo '<th ';
					
						foreach($field as $key => $value){
							
							if($key!='content'){
								
								echo 'data-'.$key.'="'.$value.'" ';
							}
						}
					echo '>'.(!empty($field['content']) ? $field['content'] : '').'</th>';				
				}

			echo '</tr>';
			echo '</thead>';
			
		echo '</table>';

		if($form){	
		
			echo '</form>';
		}
	}
	
	/**
	 * Main LTPLE_Client_Json_API Instance
	 *
	 * Ensures only one instance of LTPLE_Client_Json_API is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Client()
	 * @return Main LTPLE_Client_Json_API instance
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