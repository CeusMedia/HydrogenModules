<?php
$w		= (object) $words['confirm'];
extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/local/confirm/' ) );

$panelConfirm	= HTML::DivClass( "content-panel", array(
	HTML::H3( $w->heading ),
	HTML::DivClass( "content-panel-inner", array(
		UI_HTML_Tag::create( 'form', array(
			HTML::Label( "confirm_code", $w->labelCode, "mandatory" ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'confirm_code',
				'id'		=> 'input_confirm_code',
				'class'		=> 'span12 mandatory',
				'required'	=> 'required',
				'value'		=> $pak
			) ),
			HTML::DivClass( "buttonbar", array(
				UI_HTML_Tag::create( 'button', '<i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSend, array(
					'type'	=> "submit",
					'name'	=> "confirm",
					'class'	=> "btn btn-primary btn-large"
				) )
			) )
		), array(
			'action'	=> './auth/local/confirm'.( $from ? '?from='.$from : '' ),
			'method'	=> 'POST',
			'name'		=> 'auth-confirm'
		) )
	) )
) );

return HTML::DivClass( "auth-confirm-text-top", $textTop ).
HTML::DivClass( "row-fluid", array(
	HTML::DivClass( "span4 offset1", array(
		$panelConfirm
	) ),
	HTML::DivClass( "span6", array(
		$textInfo
	) )
) ).HTML::DivClass( "auth-confirm-text-bottom", $textBottom );
?>
