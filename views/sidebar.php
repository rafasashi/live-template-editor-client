<?php

$ltple = LTPLE_Client::instance();

$currentTab = $ltple->get_current_tab();

echo '<div id="sidebar">';

	echo '<ul class="nav nav-tabs tabs-left">';
		
		echo apply_filters('ltple_list_sidebar','',$currentTab);
		
	echo '</ul>';
	
echo '</div>';