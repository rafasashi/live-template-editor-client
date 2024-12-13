<?php
	
	$ltple = LTPLE_Client::instance();
	
    $layer_type = $ltple->gallery->get_current_type();
    
    if( !empty($layer_type->ranges) ){
        
        foreach( $layer_type->ranges as $range ){
            
            $checkout_url = add_query_arg( array(
                
                'output' 	=> 'widget',
                'options' 	=> $range['slug'],
            
            ), $ltple->urls->checkout );
            
            echo '<div class="modal fade" id="upgrade_plan_'.$range['slug'].'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'.PHP_EOL;
                
                echo '<div class="modal-dialog modal-full" role="document">'.PHP_EOL;
                    
                    echo '<div class="modal-content">'.PHP_EOL;
                        
                        echo '<div class="modal-header" style="background:#fff !important;">'.PHP_EOL;
                            
                            echo '<h4 class="modal-title" id="myModalLabel" style="color:'.$ltple->settings->navbarColor.';">Upgrade your plan</h4>'.PHP_EOL;
                        
                            echo '<button type="button" class="close m-0 p-0" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:' . $ltple->settings->navbarColor . ';">&times;</span></button>'.PHP_EOL;
                            
                        echo '</div>'.PHP_EOL;
                      
                        echo '<div class="modal-body text-center">'.PHP_EOL;
                            
                            echo '<iframe data-src="' . $checkout_url . '" style="width: 100%;position:relative;bottom: 0;border:0;height:calc( 100vh - 90px);overflow: hidden;"></iframe>';						
                            
                        echo '</div>'.PHP_EOL;

                    echo '</div>'.PHP_EOL;
                    
                echo '</div>'.PHP_EOL;
                
            echo '</div>'.PHP_EOL;
        }
    }