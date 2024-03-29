<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w		= (object) $words['confirm'];
extract( $view->populateTexts( ['top', 'info', 'bottom'], 'html/auth/confirm/' ) );

$panelConfirm	= HTML::DivClass( "content-panel", array(
	HTML::H3( $w->heading ),
	HTML::DivClass( "content-panel-inner", array(
		HtmlTag::create( 'form', array(
			HTML::Label( "confirm_code", $w->labelCode, "mandatory" ),
			HtmlTag::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'confirm_code',
				'id'		=> 'input_confirm_code',
				'class'		=> 'span12 mandatory',
				'required'	=> 'required',
				'value'		=> $pak
			] ),
			HTML::DivClass( "buttonbar", array(
				HtmlTag::create( 'button', '<i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSend, [
					'type'	=> "submit",
					'name'	=> "confirm",
					'class'	=> "btn btn-primary"
				] )
			) )
		), array(
			'action'	=> './auth/confirm'.( $from ? '?from='.$from : '' ),
			'method'	=> 'POST',
			'name'		=> 'auth-confirm'
		) )
	) )
) );

return HTML::DivClass( "auth-confirm-text-top", $textTop ).
HTML::DivClass( "row-fluid", array(
	HTML::DivClass( "span4 offset1", [
		$panelConfirm
	] ),
	HTML::DivClass( "span6", [
		$textInfo
	] )
) ).HTML::DivClass( "auth-confirm-text-bottom", $textBottom );
