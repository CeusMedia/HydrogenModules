<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Mission_Filter_Type extends View_Helper_Work_Mission_Filter_AbstractFilter
{
	public function render(): string
	{
		$changedTypes	= array_diff( $this->values, $this->selected );
		$typeIcons	= [
			0	=> HtmlTag::create( 'i', "", ['class' => "icon-wrench"] ),
			1	=> HtmlTag::create( 'i', "", ['class' => "icon-time"] ),
		];
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) ){
			$typeIcons	= [
				0	=> HtmlTag::create( 'i', "", ['class' => "fa fa-fw fa-thumb-tack"] ),
				1	=> HtmlTag::create( 'i', "", ['class' => "fa fa-fw fa-clock-o"] ),
			];
		}

		$list	= [];
		foreach( $this->values as $type ){
			$input	= HtmlTag::create( 'input', NULL, [
				'type'		=> 'checkbox',
				'name'		=> 'types[]',
				'id'		=> 'type-'.$type,
				'value'		=> $type,
				'checked'	=> in_array( $type, $this->selected ) ? "checked" : NULL
			] );
			$label	= $input.'&nbsp;'.$typeIcons[$type].'&nbsp;'.$this->words['types'][$type];
			$label	= HtmlTag::create( 'label', $label, ['class' => 'checkbox'] );
			$list[]	= HtmlTag::create( 'li', $label, ['class' => 'filter-type type-'.$type] );
		}
		$buttonIcon			= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-filter'] ).'&nbsp;';
		$labelFilter	= HtmlTag::create( 'span', $this->words['filters']['type'], ['class' => 'hidden-phone'] );
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedTypes ? "btn-info" : "" );
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'button', $buttonIcon.$buttonLabel, ['class'	=> $buttonClass, 'data-toggle' => 'dropdown'] ),
			HtmlTag::create( 'ul', $list, ['class' => 'dropdown-menu'] ),
		], ['class' => 'btn-group', 'id' => 'types'] );
	}
}
