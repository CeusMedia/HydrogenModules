<?php
class View_Helper_Work_Mission_Filter_Status{

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
		$list			= array();
		$changedStates	= array_diff( $this->values, $this->selected );
		foreach( $this->values as $status ){
			$input	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'states[]',
				'id'		=> 'status-'.$status,
				'value'		=> $status,
				'checked'	=> in_array( $status, $this->selected ) ? "checked" : NULL
			) );
			$label	= UI_HTML_Tag::create( 'label', $input.' '.$this->words['states'][$status], array( 'class' => 'checkbox' ) );
			$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'filter-status status-'.$status ) );
		}
		$buttonIcon			= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-spinner' ) ).'&nbsp;';
		$labelFilter	= $this->words['filters']['status'];
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedStates ? "btn-info" : "" );
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', $buttonIcon.$buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
			UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group', 'id' => 'states' ) );
	}
}
