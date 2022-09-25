<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

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
		$list			= [];
		$changedStates	= array_diff( $this->values, $this->selected );
		foreach( $this->values as $status ){
			$input	= HtmlTag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'states[]',
				'id'		=> 'status-'.$status,
				'value'		=> $status,
				'checked'	=> in_array( $status, $this->selected ) ? "checked" : NULL
			) );
			$label	= HtmlTag::create( 'label', $input.' '.$this->words['states'][$status], array( 'class' => 'checkbox' ) );
			$list[]	= HtmlTag::create( 'li', $label, array( 'class' => 'filter-status status-'.$status ) );
		}
		$buttonIcon			= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-spinner' ) ).'&nbsp;';
		$labelFilter	= HtmlTag::create( 'span', $this->words['filters']['status'], array( 'class' => 'hidden-phone' ) );
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedStates ? "btn-info" : "" );
		return HtmlTag::create( 'div', array(
			HtmlTag::create( 'button', $buttonIcon.$buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
			HtmlTag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group', 'id' => 'states' ) );
	}
}
