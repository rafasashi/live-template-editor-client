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

	public function get_url( $action='leads/list', $user_id = 0 ){
		
		$url = $this->parent->urls->editor . '?api='.$action;
		
		if( is_numeric($user_id) ){
			
			$url .= '/' . $user_id;
		}
		
		return $url;
	}
	
	public function get_table( $api_url, $trash=false, $export=true, $search=true, $toggle=true, $columns=true ){
								
		echo '<form id="tableForm" action="" method="post">';
			
			echo '<div id="toolbar" class="btn-group">';
				
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
			
			echo '<table id="table"';
				echo 'data-toggle="table" ';
				//echo 'data-height="400" ';
				echo 'data-url="' . $api_url . '" ';
				echo 'data-pagination="true" ';
				echo 'data-search="'.( $search ? 'true' : 'false' ).'" ';
				echo 'data-show-header="true" ';
				//echo 'data-side-pagination="server" ';
				echo 'data-page-size="20" ';
				echo 'data-page-list="[20, 50, 100, 200, 500]" ';					
				echo 'data-show-refresh="false" ';
				echo 'data-show-toggle="'.( $toggle ? 'true' : 'false' ).'" ';
				echo 'data-show-columns="'.( $columns ? 'true' : 'false' ).'" ';
				echo 'data-toolbar="#toolbar" ';
				echo 'data-sort-order="desc" ';   
				echo 'data-sort-name="description" ';
				echo 'data-show-export="true" ';
			echo '>';
				echo '<thead>';
				echo '<tr>';
				
					echo '<th ';
						echo 'data-field="state" ';
						echo 'data-checkbox="true" ';
					echo '>';
					echo '</th>';
					
					echo '<th ';
						echo 'data-field="htmlImg" ';
						echo 'data-sortable="false" ';
					echo '>';
						echo '';
					echo '</th>';
					
					echo '<th ';
						echo 'data-field="htmlTwtName" ';
						echo 'data-sortable="true" ';
					echo '>';
						echo 'Name';
					echo '</th>';
					
					echo '<th ';
						echo 'data-field="leadTwtFollowers" ';
						echo 'data-sortable="true" ';
					echo '>';
						echo 'Followers';
					echo '</th>';
					
					if( $this->parent->user->is_admin ){
						
						echo '<th ';
							echo 'data-field="leadEmail" ';
							echo 'data-sortable="true" ';
						echo '>';
							echo 'Email <span class="label label-warning pull-right"> admin </span>';
						echo '</th>';
					}										
					
					/*
					echo '<th ';
						echo 'data-field="via" ';
						echo 'data-sortable="true" ';
					echo '>';
						echo 'Via';
					echo '</th>';
					*/

					echo '<th ';
						echo 'data-field="leadDescription" ';
						echo 'data-sortable="true" ';
					echo '>';
						echo 'Description';
					echo '</th>';

				echo '</tr>';
				echo '</thead>';
			echo '</table>';
			
		echo '</form>';
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