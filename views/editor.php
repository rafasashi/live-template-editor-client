<?php

$iframe_url = $this->urls->editor . '?uri=' . $this->layer->id . '&lk=' . md5( 'layer' . $this->layer->id . $this->_time ) . '&_=' . $this->_time;

if( !empty($_GET['key']) && isset($_GET['output']) && $_GET['output'] == 'embedded' && !empty($this->layer->embedded) ){
	
	$iframe_url .= '&le=' . urlencode($_GET['le']);
} 

echo'<div class="loadingIframe" style="width: 100%;position: relative;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');height:64px;"></div>';

echo'<iframe id="editorIframe" src=" ' . $iframe_url .'" style="margin-top: -65px;position: relative;width: 100%;top: 0;bottom: 0;border:0;height: 1300px;overflow: hidden;"></iframe>';