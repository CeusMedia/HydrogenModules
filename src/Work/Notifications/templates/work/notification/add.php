<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/** @var array<string,array<string,int|float|string>> $words */
/** @var Entity_Role[] $roles */
/** @var string $inputTitle */
/** @var string $inputDateStart */
/** @var string $inputDateEnd */
/** @var string $inputContent */
/** @var string $inputLink */

use CeusMedia\Bootstrap\Icon;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['add'];

$iconCancel	= Icon::create( 'arrow-left' );
$iconSave	= Icon::create( 'check' );

$optStatus	= HtmlElements::Options( $words['statuses'], 0 );

$list	= [];
foreach( $roles as $role ){
	if( 0 === $role->nrUsers )
		continue;
	$input	= HtmlTag::create( 'input', NULL, [
		'type'	=> 'checkbox',
		'name'	=> 'roleIds[]',
		'value'	=> $role->roleId,
	] );
	$nr			= HtmlTag::create( 'small', '('.$role->nrUsers.')', ['class' => 'muted'] );
	$label		= HtmlTag::create( 'label', $input.'&nbsp;'.$role->title.'&nbsp;'.$nr, ['class' => 'checkbox'] );
	$list[$role->title]		= HtmlTag::create( 'li', $label );
};
uksort( $list, 'strnatcasecmp' );
$listRoles	= HtmlTag::create( 'ul', $list, ['class' => 'unstyled'] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $w->heading ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', [
							HtmlTag::create( 'label', $w->labelTitle, ['class' => 'mandatory'] ),
							HtmlTag::create( 'input', NULL, [
								'type'		=> 'text',
								'name'		=> 'title',
								'id'		=> 'input_title',
								'class'		=> 'span12',
								'required'	=> 'required',
								'value'		=> htmlentities( $inputTitle, ENT_QUOTES, 'UTF-8' ),
							] )
						], ['class' => 'span9'] ),
						HtmlTag::create( 'div', [
							HtmlTag::create( 'label', $w->labelStatus ),
							HtmlTag::create( 'select', $optStatus, [
								'name'		=> 'status',
								'id'		=> 'input_status',
								'class'		=> 'span12',
								'disabled'	=> 'disabled',
							] )
						], ['class' => 'span3'] )
					], ['class' => 'row-fluid'] ),
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', [
							HtmlTag::create( 'label', $w->labelDateStart, ['class' => 'mandatory'] ),
							HtmlTag::create( 'input', NULL, [
								'type'		=> 'date',
								'name'		=> 'dateStart',
								'id'		=> 'input_dateStart',
								'class'		=> 'span12',
								'required'	=> 'required',
								'value'		=> htmlentities( $inputDateStart, ENT_QUOTES, 'UTF-8' ),
							] )
						], ['class' => 'span3'] ),
						HtmlTag::create( 'div', [
							HtmlTag::create( 'label', $w->labelDateEnd, [] ),
							HtmlTag::create( 'input', NULL, [
								'type'	=> 'date',
								'name'	=> 'dateEnd',
								'id'	=> 'input_dateEnd',
								'class'	=> 'span12',
								'value'		=> htmlentities( $inputDateEnd, ENT_QUOTES, 'UTF-8' ),
							] )
						], ['class' => 'span3'] ),
						HtmlTag::create( 'div', [
							HtmlTag::create( 'label', $w->labelLink, [] ),
							HtmlTag::create( 'input', NULL, [
								'type'		=> 'text',
								'name'		=> 'link',
								'id'		=> 'input_link',
								'class'		=> 'span12',
								'value'		=> htmlentities( $inputLink, ENT_QUOTES, 'UTF-8' ),
							] )
						], ['class' => 'span6'] ),
					], ['class' => 'row-fluid'] ),
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', [
							HtmlTag::create( 'label', $w->labelContent, ['class' => 'mandatory'] ),
							HtmlTag::create( 'textarea', htmlentities( $inputContent, ENT_QUOTES, 'UTF-8' ) , [
								'type'		=> 'text',
								'name'		=> 'content',
								'id'		=> 'input_content',
								'class'		=> 'span12 TinyMCE',
								'rows'		=> 10,
							] )
						], ['class' => 'span12'] )
					], ['class' => 'row-fluid'] ),
				], ['class' => 'span9'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'h4', 'Empfänger' ),
					HtmlTag::create( 'div', 'Die Benutzer dieser Rollen werden die Nachricht sehen.', ['class' => 'alert alert-info'] ),
					HtmlTag::create( 'div', $listRoles ),
				], ['class' => 'span3'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, [
					'href'	=> './work/notification',
					'class'	=> 'btn btn-small',
				] ),
				' ',
				HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-success',
				] ),
			], ['class' => 'buttonbar'] )
		], [
			'action'	=> './work/notification/add',
			'method'	=> 'POST'
		] )
	], ['class' => 'content-panel-inner'] )
], ['class' => 'content-panel content-panel-form'] );

