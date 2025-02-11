<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
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
		$this->moduleWords	= $this->env->getLanguage()->getWords( 'manage/group' );

		$iconCancel		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-arrow-left'] );
		$iconSave		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-check'] );

		$w	= (object) $this->moduleWords['add'];
		$this->setHeading( $w->heading );
		$this->setFormAction( 'manage/group/add' );
		$this->setButtonLabelSubmit( $iconSave.'&nbsp;'.$w->buttonSave );
		$this->setButtonLabelCancel( $iconCancel.'&nbsp;'.$w->buttonCancel );
		$this->trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
		$this->trigger->setModalId( $this->id );
		$this->trigger->setLabel( $this->moduleWords['index']['buttonAdd'] );
	}

	protected function renderBody(): string
	{
		$w			= (object) $this->moduleWords['add'];
		$optType	= $this->renderTypeOptions();

		$fieldTitle	= [
			HtmlTag::create( 'label', $w->labelTitle, ['for' => 'input_title', 'class' => 'mandatory required'] ),
			HtmlTag::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'title',
				'id'		=> 'input_title',
				'class'		=> 'span12',
				'required'	=> 'required',
				'value'		=> htmlentities( $this->title ?? '', ENT_QUOTES, 'utf-8' ),
			] ),
		];
		$fieldType	= [
			HtmlTag::create( 'label', $w->labelType, ['for' => 'input_type'] ),
			HtmlTag::create( 'select', $optType, [
				'name'		=> 'type',
				'id'		=> 'input_type',
				'class'		=> 'span12 has-optionals',
			] ),
		];
		$fieldDescription	= [
			HtmlTag::create( 'textarea', NULL, [
				'name'		=> 'description',
				'id'		=> 'input_description',
				'class'		=> 'span12',
				'rows'		=> 4,
//				'required'	=> 'required',
			] ),
		];

		return HtmlTag::create( 'div', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', $fieldTitle, ['class' => 'span8'] ),
				HtmlTag::create( 'div', $fieldType, ['class' => 'span4'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', $fieldDescription, ['class' => 'span12 '] ),
			], ['class' => 'row-fluid'] ),
		] );
	}

	public function render(): string
	{
		$this->body	= $this->renderBody();
		return parent::render();
	}

	//  --  SETTERS  --  //
	public function setId( int|string $id ): static
	{
		$this->trigger->setModalId( $id );
		return parent::setId( $id );
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
