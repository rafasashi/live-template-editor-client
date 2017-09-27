<!DOCTYPE html>

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title></title>
	
	<link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
	<link href='https://fonts.googleapis.com/css?family=Quicksand' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link href="<?php echo $this->assets_url; ?>css/filetree.css" rel="stylesheet" type="text/css">
	
	<style>
	body { background-color:#182f42; color:#fff; font-family:'Quicksand';}
	.container { margin:150px auto; max-width:640px;}
	</style>
	
</head>

<body>
	
	<div class="filetree">
	
	<?php echo $this->layer->get_filetree( $this->layer->layerStaticDir ); ?>

	</div>
	
	<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
	<script src="<?php echo $this->assets_url; ?>js/filetree.js"></script>

</body>