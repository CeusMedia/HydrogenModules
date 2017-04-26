<?php
class View_Helper_Work_Mission_Filter_Type{

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
		$changedTypes	= array_diff( $this->values, $this->selected );
		$typeIcons	= array(
			0	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-wrench" ) ),
			1	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-time" ) ),
		);
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) ){
			$typeIcons	= array(
				0	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "fa fa-fw fa-thumb-tack" ) ),
				1	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "fa fa-fw fa-clock-o" ) ),
			);
		}

		$list	= array();
		foreach( $this->values as $type ){
			$input	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'types[]',
				'id'		=> 'type-'.$type,
				'value'		=> $type,
				'checked'	=> in_array( $type, $this->selected ) ? "checked" : NULL
			) );
			$label	= $input.'&nbsp;'.$typeIcons[$type].'&nbsp;'.$this->words['types'][$type];
			$label	= UI_HTML_Tag::create( 'label', $label, array( 'class' => 'checkbox' ) );
			$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'filter-type type-'.$type ) );
		}
		$buttonIcon			= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-filter' ) ).'&nbsp;';
		$labelFilter	= $this->words['filters']['type'];
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedTypes ? "btn-info" : "" );
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', $buttonIcon.$buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
			UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group', 'id' => 'types' ) );
	}
}
