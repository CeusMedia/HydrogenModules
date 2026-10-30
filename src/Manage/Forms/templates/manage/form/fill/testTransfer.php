<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var array<Entity_Form_Transfer_Quest> $results */
/** @var bool $sent */

$list		= [];
foreach( $results as $quest ){
	$panelRule		= HtmlTag::create( 'div', [
		HtmlTag::create( 'h4', 'Rule: '.$quest->target->title ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'xmp', str_replace( '    ', '  ', $quest->rule->rules ), ['style' => 'max-height: 300px; overflow-y: auto; border: 1px solid gray; font-size: 0.85em; padding: 0.5em 1em; line-height: 14px;'] ),
		], ['class' => 'panel-inner', 'style' => ''] ),
	], ['class' => 'panel'] );

	$panelFormData	= HtmlTag::create( 'div', [
		HtmlTag::create( 'h4', 'Form Fill' ),
		HtmlTag::create( 'div', [
			print_m( $quest->formData, NULL, NULL, TRUE ),
		], ['class' => 'panel-inner'] )
	], ['class' => 'panel'] );

	$panelTransmitData	= HtmlTag::create( 'div', [
		HtmlTag::create( 'h4', 'Transfer' ),
		HtmlTag::create( 'div', [
			print_m( $quest->data, NULL, NULL, TRUE ),
		], ['class' => 'panel-inner'] )
	], ['class' => 'panel'] );

	$heading	= HtmlTag::create( 'h3', 'Transfer: '.$quest->rule->title.' &rArr; '.$quest->target->title );


	$result			= ['status' => $quest->status, 'errors' => [], 'trace' => ''];
	if( $sent ){
		$result['errors']	= $queue->result->errors;
		$result['trace']	= $queue->result->trace;
	}

	$panelResult	= HtmlTag::create( 'div', [
		HtmlTag::create( 'h4', 'Transfer Status / Result' ),
		HtmlTag::create( 'div', [
			print_m( $result, NULL, NULL, TRUE ),
		], ['class' => 'panel-inner'] )
	], ['class' => 'panel'] );

	unset( $quest->rule );
	unset( $quest->target );
	unset( $quest->formData );
	unset( $quest->data );
	unset( $quest->result );

	$panelInfo		= HtmlTag::create( 'div', [
		HtmlTag::create( 'h3', '' ),
		HtmlTag::create( 'div', [
			print_m( $quest, NULL, NULL, TRUE ),
		], ['class' => 'panel-inner'] )
	], ['class' => 'panel'] );

	$list[]	= HtmlTag::create( 'div', [
		$heading,
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', $panelRule, ['class' => 'span6'] ),
			HtmlTag::create( 'div', $panelResult, ['class' => 'span6'] ),
		], ['class' => 'row-fluid'] ),
		HtmlTag::create( 'hr' ),
		HtmlTag::create( 'h3', 'Data' ),
		HtmlTag::create( 'div', [
			HtmlTag::create( 'div', $panelFormData, ['class' => 'span6'] ),
			HtmlTag::create( 'div', $panelTransmitData, ['class' => 'span6'] ),
		], ['class' => 'row-fluid'] ),
//		$panelInfo,
		HtmlTag::create( 'hr' ),
	] );
}
return HtmlTag::create( 'h3', count( $results ).' Transfer'.( 1 === count( $results ) ? '' : 's' ) ).join( '', $list );
