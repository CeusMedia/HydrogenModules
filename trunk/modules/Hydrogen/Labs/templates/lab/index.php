<?php
/*  --  INIT  --  */
$dbc = $page = $messenger = $script = $style = $content = $dump = NULL;
if( $dbc = $this->env->get( 'database', FALSE ) )
	$dbc->beginTransaction();
ob_start();
try{
/*  --  YOUR CODE HERE  --  */




/*
try{
	$this->env->get( 'INVALID' );
}
catch( Exception $e ){
	UI_HTML_Exception_Page::display( $e );
	die;
}

*/


/*  --  YOUR CSS  --  */$style	= <<<EOT

EOT;

/*  --  YOUR JS  --  */$script	= <<<EOT
$(document).ready(function(){});

EOT;


/*  --  HTML OUTPUT  --  */
$content	= '
<div id="lab">
	<h2>Lab</h2>
	<div class="chamber" id="lab-chamber-main">
		'.$content.'
	</div>
</div>';






/*  --  DESTRUCTION - STOP CODING HERE  --  */
	$dump	= ob_get_clean();
}
catch( Excepton $e ){$dump	.= UI_HTML_Excepion_View::render( $e );}
$content	.= $dump ? '<div class="dev-dumpbox">'.$dump.'</div>' : '';
isset( $dbc ) ? $dbc->rollBack() : NULL;
return '<style>'.$style.'</style><script>'.$script.'</script>'.$content;
?>