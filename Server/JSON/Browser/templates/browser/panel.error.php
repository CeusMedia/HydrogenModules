<?php

if( !( isset( $response->exception ) && $response->exception ) )
	return '';

$exception	= (object) array( 'message' => NULL, 'code' => NULL, 'view' => NULL );
$exception->message = $response->exception;
if( 1 || $response->data <= -100 ){
	if( isset( $response->serial ) ){
		$instance = unserialize( $response->serial );
		if( 1 || ( $instance->getCode() >= 200 && $instance->getCode() < 300 ) ){
			$exception->message	= $instance->getMessage();
			$exception->code	= $instance->getCode();
			$exception->view	= UI_HTML_Exception_View::render( $instance );
		}
		unset( $response->serial );
	}
	switch( $exception->code ){
		// ...
	}
/*				switch( $data ){
		case -105:
			$data	= 'Error: '.$exception->message.'.';
			unset( $response->exception );
			$exception	= (object) array( 'message' => NULL, 'code' => NULL, 'view' => NULL );
			break;
	}
*/
	if( preg_match( '/Access denied:/', $exception->message ) ){
		$content	= 'Error: '.$exception->message.'.';
		unset( $response->exception );
		$exception	= (object) array( 'message' => NULL, 'code' => NULL, 'view' => NULL );
	}
}
if( $exception->view )
	$content	= '<div style="float: left">'.$exception->view.'</div>';
else if( $exception->message )
	$content	= '<xmp class="code">'.$exception->message.'</xmp>';

return '
<div class="content-panel content-panel-form">
	<h3>Exception</h3>
	<div class="content-panel-inner">
		<div id="response-error">
			'.$content.'
		</div>
	</div>
</div>
';
