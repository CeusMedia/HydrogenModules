<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_Work_Mission_Filter_Status
{
	protected WebEnvironment $env;
	protected array $words;
	protected ?View_Helper_ModalRegistry $modalRegistry		= NULL;
	protected array $values									= [];
	protected array $selected								= [];

	public function __construct( WebEnvironment $env )
	{
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'work/mission' );
	}

	public function setModalRegistry( View_Helper_ModalRegistry $modalRegistry ): self
	{
		$this->modalRegistry	= $modalRegistry;
		return $this;
	}

	public function setValues( array $all, array $selected ): self
	{
		$this->values	= $all;
		$this->selected	= $selected;
		return $this;
	}

	public function render(): string
	{
		$list			= [];
		$changedStates	= array_diff( $this->values, $this->selected );
		foreach( $this->values as $status ){
			$input	= HtmlTag::create( 'input', NULL, [
				'type'		=> 'checkbox',
				'name'		=> 'states[]',
				'id'		=> 'status-'.$status,
				'value'		=> $status,
				'checked'	=> in_array( $status, $this->selected ) ? "checked" : NULL
			] );
			$label	= HtmlTag::create( 'label', $input.' '.$this->words['states'][$status], ['class' => 'checkbox'] );
			$list[]	= HtmlTag::create( 'li', $label, ['class' => 'filter-status status-'.$status] );
		}
		$buttonIcon			= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-spinner'] ).'&nbsp;';
		$labelFilter	= HtmlTag::create( 'span', $this->words['filters']['status'], ['class' => 'hidden-phone'] );
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedStates ? "btn-info" : "" );
		return HtmlTag::create( 'div', [
			HtmlTag::create( 'button', $buttonIcon.$buttonLabel, ['class'	=> $buttonClass, 'data-toggle' => 'dropdown'] ),
			HtmlTag::create( 'ul', $list, ['class' => 'dropdown-menu'] ),
		], ['class' => 'btn-group', 'id' => 'states'] );
	}
}
