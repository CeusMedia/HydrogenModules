<?php
$w		= (object) $words['password'];

$iconSend	= HTML::Icon( 'envelope', TRUE );

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/password/' ) );

$panelPassword	= HTML::DivClass( "content-panel content-panel-form", array(
	HTML::H3( $w->heading ),
	HTML::DivClass( "content-panel-inner", array(
		UI_HTML_Tag::create( 'form', array(
			HTML::DivClass( "row-fluid", array(
				HTML::DivClass( "span12", array(
					UI_HTML_Tag::create( 'label', $w->labelEmail, array(
						'for'	=> "input_password_email",
						'class'	=> "mandatory"
					) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'	=> "text",
						'name'	=> "password_email",
						'id'	=> "input_password_email",
						'class'	=> "span12 mandatory",
						'value'	=> htmlentities( $password_email, ENT_QUOTES, 'UTF-8' )
					) )
				) )
			) ),
			HTML::DivClass( "buttonbar", array(
				UI_HTML_Tag::create( 'button', $iconSend.'&nbsp;'.$w->buttonSend, array(
					'type'	=> "submit",
					'class'	=> "btn btn-primary",
					'name'	=> "sendPassword"
				) )
			) )
		), array(
			'action'	=> "./auth/password",
			'method'	=> "post",
		) )
	) ),
) );

return HTML::DivClass( "auth-password-text-top", $textTop ).
HTML::DivClass( "row-fluid", array(
	HTML::DivClass( "span4 offset1", array(
		$panelPassword
	) ),
	HTML::DivClass( "span6", array(
		$textInfo
	) ),
) ).HTML::DivClass( "auth-password-text-bottom", $textBottom );
?>
