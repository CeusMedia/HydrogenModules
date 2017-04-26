<?php
class View_Helper_Work_Mission_Filter_Priority{

	public function __construct( $env ){
		$this->env	= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'work/mission' );
	}

	public function setModalRegistry( $modalRegistry ){
		$this->modalRegistry	= $modalRegistry;
	}

	public function setValues( $all, $selected ){
		$this->values	= $all;
		$this->selected	= $selected;
	}

	public function render(){
		$changedPriorities	= array_diff( $this->values, $this->selected );
		$list	= array();
		foreach( $this->values as $priority ){
			$input	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'priorities[]',
				'id'		=> 'priority-'.$priority,
				'value'		=> $priority,
				'checked'	=> in_array( $priority, $this->selected ) ? "checked" : NULL
			) );
			$label	= UI_HTML_Tag::create( 'label', $input.' './*$priority.' - '.*/$this->words['priorities'][$priority], array( 'class' => 'checkbox' ) );
			$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'filter-priority priority-'.$priority ) );
		}
		$buttonIcon		= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exclamation' ) ).'&nbsp;';
		$labelFilter	= $this->words['filters']['priority'];
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedPriorities ? "btn-info" : "" );
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', $buttonIcon.$buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
			UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group', 'id' => 'priorities' ) );
	}
}
