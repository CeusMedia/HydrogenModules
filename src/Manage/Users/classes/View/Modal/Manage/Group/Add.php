<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as Html;
use CeusMedia\HydrogenFramework\Environment;

class View_Modal_Manage_Group_Add
{
	protected Environment $env;
	protected Dictionary $moduleConfig;
	protected int|string|NULL $id		= NULL;
	protected ?string $heading			= NULL;
	protected ?string $title			= NULL;
	protected ?string $type				= NULL;
	protected array $types				= [];
	protected array $moduleWords;

	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.manage_users.', TRUE );
		$this->moduleWords	= $this->env->getLanguage()->getWords( 'manage/group' );
	}

	public function render(): string
	{
		if( !$this->id )
			throw new RuntimeException( 'No ID set' );

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

		$form	= Html::create( 'div', [
			Html::create( 'div', [
				Html::create( 'div', $fieldTitle, ['class' => 'span10 offset1'] ),
			], ['class' => 'row-fluid'] ),
			Html::create( 'div', [
				Html::create( 'div', $fieldType, ['class' => 'span10 offset1'] ),
			], ['class' => 'row-fluid'] ),
			Html::create( 'div', [
				Html::create( 'div', $fieldDescription, ['class' => 'span10 offset1'] ),
			], ['class' => 'row-fluid'] ),
		] );

		$iconCancel	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
		$iconSave	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
		$modal	= new BootstrapModalDialog( $this->id );
//		$modal->setFormAction( './info/contact/form'.( $this->from ? '?from='.$this->from : '' ) );
		$modal->setFormAction( './manage/group/add' );
//		$modal->setFormSubmit( 'ModuleGroupModal.validate(this)' );
		$modal->setHeading( $this->heading ?: $this->moduleWords['modal-group-add']['heading'] );
		$modal->setBody( $form );
		$modal->setFade( !FALSE );
		$modal->setAttributes( ['class' => 'modal-manage-group-add'] );
		$modal->setSubmitButtonLabel( $iconSave.'&nbsp;'.$w->buttonSave );
		$modal->setSubmitButtonClass( 'btn btn-primary not-btn-large' );
		$modal->setCloseButtonLabel( $iconCancel.'&nbsp;'.$w->buttonCancel );
		$modal->setCloseButtonClass( 'btn btn-small' );
		return $modal->render();
	}

	//  --  SETTERS  --  //
	public function setId( int|string $id ): self
	{
		$this->id		= $id;
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
