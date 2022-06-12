<?php
$iconFilter		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search' ) );
$iconReset		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-search-minus' ) );

$optForm		= array( '' => '- alle -' );
foreach( $forms as $item )
	$optForm[$item->formId]	= $item->title;
$optForm		= UI_HTML_Elements::Options( $optForm, $filterFormId );

$optStatus		= array(
	''									=> '- alle -',
	Model_Form_Fill::STATUS_NEW			=> 'unbestätigt',
	Model_Form_Fill::STATUS_CONFIRMED	=> 'gültig',
	Model_Form_Fill::STATUS_HANDLED		=> 'behandelt',
);
$optStatus		= UI_HTML_Elements::Options( $optStatus, $filterStatus );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', 'Filter' ),
	UI_HTML_Tag::create( 'div', array(
		UI_HTML_Tag::create( 'form', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'ID', array( 'for' => 'input_fillId' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'fillId',
						'id'		=> 'input_fillId',
						'class'		=> 'span12',
						'value'		=> htmlentities( $filterFillId, ENT_QUOTES, 'UTF-8' ),
					) ),
				), array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'E-Mail <small class="muted">(ungefähr)</small>', array( 'for' => 'input_email' ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'email',
						'id'		=> 'input_email',
						'class'		=> 'span12',
						'value'		=> htmlentities( $filterEmail, ENT_QUOTES, 'UTF-8' ),
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Zustand', array( 'for' => 'input_status' ) ),
					UI_HTML_Tag::create( 'select', $optStatus, array(
						'name'		=> 'status',
						'id'		=> 'input_status',
						'class'		=> 'span12',
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'label', 'Formulare', array( 'for' => 'input_formId' ) ),
					UI_HTML_Tag::create( 'select', $optForm, array(
						'name'		=> 'formId[]',
						'id'		=> 'input_formId',
						'class'		=> 'span12',
						'multiple'	=> 'multiple',
						'size'		=> 12,
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', array(
					UI_HTML_Tag::create( 'button', $iconFilter.'&nbsp;filtern', array(
						'type'	=> 'submit',
						'name'	=> 'filter',
						'class'	=> 'btn btn-small btn-info',
					) ),
					UI_HTML_Tag::create( 'a', $iconReset.'&nbsp;leeren', array(
						'href'	=> './manage/form/fill/filter/reset',
						'class'	=> 'btn btn-small btn-inverse',
					) ),
				), array( 'class' => 'btn-group' ) ),
			), array( 'class' => 'buttonbar' ) ),
		), array( 'action' => './manage/form/fill/filter', 'method' => 'post' ) ),
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
