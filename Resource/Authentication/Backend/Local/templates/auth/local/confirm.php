<?php
$w		= (object) $words['confirm'];

extract( $view->populateTexts( array( 'top', 'info', 'bottom' ), 'html/auth/local/confirm/' ) );

$panelConfirm	= HTML::DivClass( "content-panel content-panel-form", array(
	HTML::H3( $w->heading ),
	HTML::DivClass( "content-panel-inner", array(
		UI_HTML_Tag::create( 'form', array(
			HTML::DivClass( 'row-fluid', array(
				HTML::DivClass( 'span12', array(
					HTML::Label( "confirm_code", $w->labelCode, "mandatory" ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'hidden',
						'name'		=> 'from',
						'value'		=> $from
					) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'confirm_code',
						'id'		=> 'input_confirm_code',
						'class'		=> 'bs2-span12 bs3-col-md-12 bs4-col-md-12 mandatory',
						'required'	=> 'required',
						'value'		=> $pak
					) ),
				) ),
			) ),
			HTML::DivClass( "buttonbar", array(
				UI_HTML_Tag::create( 'button', '<i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSend, array(
					'type'	=> "submit",
					'name'	=> "confirm",
					'class'	=> "btn btn-primary btn-large"
				) )
			) )
		), array(
			'action'	=> './auth/local/confirm',
			'method'	=> 'POST',
			'name'		=> 'auth-confirm'
		) )
	) )
) );

if( strlen( trim( strip_tags( $textInfo ) ) ) ){
	return $textTop.
		HTML::DivClass( 'bs2-row-fluid bs3-row bs4-row', array(
			HTML::DivClass( 'bs2-span4 bs3-col-md-4 bs4-col-md-4 bs2-offset1 bs3-col-offset-1 bs4-col-offset-1', $panelConfirm ),
			HTML::DivClass( 'bs2-span6 bs3-col-md-6 bs4-col-md-6', $textInfo ),
		) ).$textBottom;
}

if( strlen( trim( strip_tags( $textTop ) ) ) || strlen( trim( strip_tags( $textBottom ) ) ) ){
	return $textTop.$panelConfirm.$textBottom;
}

$env->getPage()->addBodyClass( 'auth-centered' );
return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'div', $panelConfirm, array( 'class' => 'centered-pane' ) )
), array( 'class' => 'centered-pane-container' ) );

//return HTML::DivClass( "auth-confirm-text-top", $textTop ).
//HTML::DivClass( "bs2-row-fluid bs3-row bs4-row", array(
//) ).HTML::DivClass( "auth-confirm-text-bottom", $textBottom );
?>
