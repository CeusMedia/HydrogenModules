<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as Html;
use CeusMedia\HydrogenFramework\Environment;

class View_Modal_Manage_Group_Add extends View_Helper_Bootstrap_Modal
{
	public View_Helper_Bootstrap_Modal_Trigger $trigger;

	protected ?string $title			= NULL;
	protected ?string $type				= NULL;
	protected array $types				= [];
	protected array $moduleWords;

	public function __construct( Environment $env )
	{
		parent::__construct( $env );
		$this->env			= $env;
		$this->moduleWords	= $this->env->getLanguage()->getWords( 'manage/group' );

		$w	= (object) $this->moduleWords['add'];
		$this->setButtonLabelSubmit( $w->buttonSave );
		$this->setButtonLabelCancel( $w->buttonCancel );
		$this->trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
		$this->trigger->setModalId( $this->id );
		$this->trigger->setLabel( $w->buttonAdd );
	}

	protected function renderBody(): string
	{
		$w			= (object) $this->moduleWords['add'];
		$optType	= $this->renderTypeOptions();

		$fieldTitle	= [
			Html::create( 'label', $w->labelTitle, ['for' => 'input_title', 'class' => 'mandatory required'] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'title',
				'id'		=> 'input_title',
				'class'		=> 'span12',
				'required'	=> 'required',
				'value'		=> htmlentities( $this->title, ENT_QUOTES, 'utf-8' ),
			] ),
		];
		$fieldType	= [
			Html::create( 'label', $w->labelType, ['for' => 'input_type'] ),
			Html::create( 'select', $optType, [
				'name'		=> 'type',
				'id'		=> 'input_type',
				'class'		=> 'span12 has-optionals',
			] ),
		];
		$fieldDescription	= [
			Html::create( 'textarea', NULL, [
				'name'		=> 'description',
				'id'		=> 'input_description',
				'class'		=> 'span12',
				'rows'		=> 4,
				'required'	=> 'required',
			] ),
		];

		return Html::create( 'div', [
			Html::create( 'div', [
				Html::create( 'div', $fieldTitle, ['class' => 'span8 offset1'] ),
				Html::create( 'div', $fieldType, ['class' => 'span2 offset0'] ),
			], ['class' => 'row-fluid'] ),
			Html::create( 'div', [
			], ['class' => 'row-fluid'] ),
			Html::create( 'div', [
				Html::create( 'div', $fieldDescription, ['class' => 'span10 offset1'] ),
			], ['class' => 'row-fluid'] ),
		] );
	}

	public function render(): string
	{
		$this->body	= $this->renderBody();
		return parent::render();
	}

	//  --  SETTERS  --  //
	public function setId( int|string $id ): self
	{
		$this->id		= $id;
		$this->trigger->setModalId( $id );
		return $this;
	}

	public function setHeading( string $heading ): self
	{
		$this->heading		= $heading;
		return $this;
	}

	public function setTitle( string $title ): self
	{
		$this->title	= $title;
		return $this;
	}

	public function setType( string $type ): self
	{
		$this->type		= $type;
		return $this;
	}

	public function setTypes( array $types ): self
	{
		$this->types		= $types;
		return $this;
	}
	/*
		public function setFrom( $from ): self
		{
			$this->from		= $from;
		}*/

	//  --  PROTECTED  --  //
	protected function renderTypeOptions(): string
	{
		$optType	= [];
		$types		= $this->types ?? $this->moduleWords['types'];
		foreach( $types as $key => $value )
			$optType[$key]	= $value;

		return HtmlElements::Options( $optType, $this->type );
	}
}
