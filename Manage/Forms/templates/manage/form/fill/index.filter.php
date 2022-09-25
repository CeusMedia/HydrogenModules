<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconFilter		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );

$optForm		= array( '' => '- alle -' );
foreach( $forms as $item )
	if( $item->status > 0 )
		$optForm[$item->formId]	= $item->title;
$optForm		= UI_HTML_Elements::Options( $optForm, $filterFormId );

$optStatus		= array(
	''									=> '- alle -',
	Model_Form_Fill::STATUS_NEW			=> 'unbestätigt',
	Model_Form_Fill::STATUS_CONFIRMED	=> 'gültig',
	Model_Form_Fill::STATUS_HANDLED		=> 'behandelt',
);
$optStatus		= UI_HTML_Elements::Options( $optStatus, $filterStatus );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Filter' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'ID', array( 'for' => 'input_fillId' ) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'fillId',
						'id'		=> 'input_fillId',
						'class'		=> 'span12',
						'value'		=> htmlentities( $filterFillId, ENT_QUOTES, 'UTF-8' ),
					) ),
				), array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'E-Mail <small class="muted">(ungefähr)</small>', array( 'for' => 'input_email' ) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'email',
						'id'		=> 'input_email',
						'class'		=> 'span12',
						'value'		=> htmlentities( $filterEmail, ENT_QUOTES, 'UTF-8' ),
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Zustand', array( 'for' => 'input_status' ) ),
					HtmlTag::create( 'select', $optStatus, array(
						'name'		=> 'status',
						'id'		=> 'input_status',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Formulare', array( 'for' => 'input_formId' ) ),
					HtmlTag::create( 'select', $optForm, array(
						'name'		=> 'formId[]',
						'id'		=> 'input_formId',
						'class'		=> 'span12',
						'multiple'	=> 'multiple',
						'size'		=> 12,
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'button', $iconFilter.'&nbsp;filtern', array(
						'type'	=> 'submit',
						'name'	=> 'filter',
						'class'	=> 'btn btn-small btn-info',
					) ),
					HtmlTag::create( 'a', $iconReset.'&nbsp;leeren', array(
						'href'	=> './manage/form/fill/filter/reset',
						'class'	=> 'btn btn-small btn-inverse',
					) ),
				), array( 'class' => 'btn-group' ) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './manage/form/fill/filter', 'method' => 'post' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
