<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array<Entity_Form> $forms */
/** @var int|string $filterStatus */
/** @var array<string> $filterFormId */
/** @var string $filterFillId */
/** @var string $filterEmail */
/** @var array<string,array<string,string>> $words */

$w			= (object) $words['index.filter'];
$statuses	= $words['statuses'];

$iconFilter		= new Icon( 'search' );
$iconReset		= new Icon( 'search-minus' );

$optForm		= ['' => $w->optionAll];
foreach( $forms as $item )
	if( $item->status > 0 )
		$optForm[(string) $item->formId]	= $item->title;
$optForm		= HtmlElements::Options( $optForm, $filterFormId );

$optStatus		= [
	''									=> $w->optionAll,
	Model_Form_Fill::STATUS_NEW			=> $statuses[Model_Form_Fill::STATUS_NEW],
	Model_Form_Fill::STATUS_CONFIRMED	=> $statuses[Model_Form_Fill::STATUS_CONFIRMED],
	Model_Form_Fill::STATUS_HANDLED		=> $statuses[Model_Form_Fill::STATUS_HANDLED],
	Model_Form_Fill::STATUS_CUSTOMER	=> $statuses[Model_Form_Fill::STATUS_CUSTOMER],
	Model_Form_Fill::STATUS_BOOKING		=> $statuses[Model_Form_Fill::STATUS_BOOKING],
	Model_Form_Fill::STATUS_ORDER		=> $statuses[Model_Form_Fill::STATUS_ORDER],
	Model_Form_Fill::STATUS_NEWSLETTER	=> $statuses[Model_Form_Fill::STATUS_NEWSLETTER],
];
$optStatus		= HtmlElements::Options( $optStatus, $filterStatus );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelId, ['for' => 'input_fillId'] ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'name'		=> 'fillId',
						'id'		=> 'input_fillId',
						'class'		=> 'span12',
						'value'		=> htmlentities( $filterFillId, ENT_QUOTES, 'UTF-8' ),
					] ),
				], ['class' => 'span4'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelEmail.' <small class="muted">'.$w->labelEmailSuffix.'</small>', ['for' => 'input_email'] ),
					HtmlTag::create( 'input', NULL, [
						'type'				=> 'text',
						'name'				=> 'email',
						'id'				=> 'input_email',
						'class'				=> 'span12 autocomplete-query',
						'autocomplete'		=> 'off',
						'autocorrect'		=> 'off',
    					'autocapitalize'	=> 'off',
    					'spellcheck'		=> 'false',
						'value'				=> htmlentities( $filterEmail, ENT_QUOTES, 'UTF-8' ),
					] ),
					HtmlTag::create( 'div', '', [
						'id'		=> 'input_email-autocomplete-list',
						'class'		=> 'autocomplete-list',
					] ),
				], ['class' => 'span12 autocomplete-wrapper'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelStatus, ['for' => 'input_status'] ),
					HtmlTag::create( 'select', $optStatus, [
						'name'		=> 'status',
						'id'		=> 'input_status',
						'class'		=> 'span12',
					] ),
				], ['class' => 'span12'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', $w->labelForms, ['for' => 'input_formId'] ),
					HtmlTag::create( 'select', $optForm, [
						'name'		=> 'formId[]',
						'id'		=> 'input_formId',
						'class'		=> 'span12',
						'multiple'	=> 'multiple',
						'size'		=> 12,
					] ),
				], ['class' => 'span12'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'button', $iconFilter.'&nbsp;'.$w->buttonFilter, [
						'type'	=> 'submit',
						'name'	=> 'filter',
						'class'	=> 'btn btn-small btn-info',
					] ),
					HtmlTag::create( 'a', $iconReset.'&nbsp;'.$w->buttonReset, [
						'href'	=> './manage/form/fill/filter/reset',
						'class'	=> 'btn btn-small btn-inverse',
					] ),
				], ['class' => 'btn-group'] ),
			], ['class' => 'buttonbar'] ),
		], ['action' => './manage/form/fill/filter', 'method' => 'post'] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
