<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<object> $forms */
/** @var int|string|NULL $filterStatus */
/** @var ?string $filterFormId */
/** @var ?string $filterFillId */
/** @var ?string $filterEmail */

$iconFilter		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search'] );
$iconReset		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-search-minus'] );

$optForm		= ['' => '- alle -'];
foreach( $forms as $item )
	if( $item->status > 0 )
		$optForm[$item->formId]	= $item->title;
$optForm		= HtmlElements::Options( $optForm, $filterFormId );

$optStatus		= [
	''									=> '- alle -',
	Model_Form_Fill::STATUS_NEW			=> 'unbestätigt',
	Model_Form_Fill::STATUS_CONFIRMED	=> 'gültig',
	Model_Form_Fill::STATUS_HANDLED		=> 'behandelt',
];
$optStatus		= HtmlElements::Options( $optStatus, $filterStatus );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Filter' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'ID', ['for' => 'input_fillId'] ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'fillId',
						'id'		=> 'input_fillId',
						'class'		=> 'span12',
						'value'		=> htmlentities( $filterFillId, ENT_QUOTES, 'UTF-8' ),
					) ),
				), ['class' => 'span4'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'E-Mail <small class="muted">(ungefähr)</small>', ['for' => 'input_email'] ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'email',
						'id'		=> 'input_email',
						'class'		=> 'span12',
						'value'		=> htmlentities( $filterEmail, ENT_QUOTES, 'UTF-8' ),
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Zustand', ['for' => 'input_status'] ),
					HtmlTag::create( 'select', $optStatus, [
						'name'		=> 'status',
						'id'		=> 'input_status',
						'class'		=> 'span12',
					] ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Formulare', ['for' => 'input_formId'] ),
					HtmlTag::create( 'select', $optForm, [
						'name'		=> 'formId[]',
						'id'		=> 'input_formId',
						'class'		=> 'span12',
						'multiple'	=> 'multiple',
						'size'		=> 12,
					] ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'button', $iconFilter.'&nbsp;filtern', [
						'type'	=> 'submit',
						'name'	=> 'filter',
						'class'	=> 'btn btn-small btn-info',
					] ),
					HtmlTag::create( 'a', $iconReset.'&nbsp;leeren', [
						'href'	=> './manage/form/fill/filter/reset',
						'class'	=> 'btn btn-small btn-inverse',
					] ),
				), ['class' => 'btn-group'] ),
			), ['class' => 'buttonbar'] ),
		), ['action' => './manage/form/fill/filter', 'method' => 'post'] ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );
