<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Mission_Filter_Priority extends View_Helper_Work_Mission_Filter_AbstractFilter
{
	public function render(): string
	{
		$changedPriorities	= array_diff( $this->values, $this->selected );
		$list	= [];
		foreach( $this->values as $priority ){
			$input	= HtmlTag::create( 'input', NULL, [
				'type'		=> 'checkbox',
				'name'		=> 'priorities[]',
				'id'		=> 'priority-'.$priority,
				'value'		=> $priority,
				'checked'	=> in_array( $priority, $this->selected ) ? "checked" : NULL
			] );
			$label	= HtmlTag::create( 'label', $input.' './*$priority.' - '.*/$this->words['priorities'][$priority], ['class' => 'checkbox'] );
			$list[]	= HtmlTag::create( 'li', $label, ['class' => 'filter-priority priority-'.$priority] );
		}
		$buttonIcon		= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-exclamation'] ).'&nbsp;';
		$labelFilter	= HtmlTag::create( 'span', $this->words['filters']['priority'], ['class' => 'hidden-phone'] );
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedPriorities ? "btn-info" : "" );
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'button', $buttonIcon.$buttonLabel, ['class'	=> $buttonClass, 'data-toggle' => 'dropdown'] ),
			HtmlTag::create( 'ul', $list, ['class' => 'dropdown-menu'] ),
		], ['class' => 'btn-group', 'id' => 'priorities'] );
	}
}
