<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as Html;
use CeusMedia\HydrogenFramework\Environment;

/**
 * Modal for POST via AJAX.
 */
class View_Helper_Info_Contact_Form_Modal
{
	protected Environment $env;
	protected Dictionary $moduleConfig;
	protected int|string|NULL $id		= NULL;
	protected ?string $subject			= NULL;
	protected ?string $heading			= NULL;
	protected ?string $type				= NULL;
	protected array $types				= [];
	protected array $moduleWords;

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env			= $env;
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.info_contact.', TRUE );
		$this->moduleWords	= $this->env->getLanguage()->getWords( 'info/contact' );
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		if( !$this->id )
			throw new RuntimeException( 'No ID set' );

		$w		= (object) $this->moduleWords['modal-form'];
		$form	= $this->renderForm();

		$iconCancel	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
		$iconSave	= Html::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
		$modal	= new BootstrapModalDialog( $this->id );
//		$modal->setFormAction( './info/contact/form'.( $this->from ? '?from='.$this->from : '' ) );
		$modal->setFormAction( './ajax/info/contact/form' );
		$modal->setFormSubmit( 'ModuleInfoContactForm.sendContactForm(this)' );
		$modal->setHeading( $this->heading ?: $this->moduleWords['form']['heading'] );
		$modal->setBody( $form );
		$modal->setFade( !FALSE );
		$modal->setAttributes( ['class' => 'modal-info-contact-form'] );
		$modal->setSubmitButtonLabel( $iconSave.'&nbsp;'.$w->buttonSave );
		$modal->setSubmitButtonClass( 'btn btn-primary not-btn-large' );
		$modal->setCloseButtonLabel( $iconCancel.'&nbsp;'.$w->buttonCancel );
		$modal->setCloseButtonClass( 'btn btn-small' );
		return $modal->render();
	}

	//  --  SETTERS  --  //

	/**
	 *	@param		int|string		$id
	 *	@return		self
	 */
	public function setId( int|string $id ): self
	{
		$this->id		= $id;
		return $this;
	}

	/**
	 *	@param		string		$heading
	 *	@return		self
	 */
	public function setHeading( string $heading ): self
	{
		$this->heading		= $heading;
		return $this;
	}

	/**
	 *	@param		string		$subject
	 *	@return		self
	 */
	public function setSubject( string $subject ): self
	{
		$this->subject	= $subject;
		return $this;
	}

	/**
	 *	@param		?string		$type
	 *	@return		self
	 */
	public function setType( ?string $type ): self
	{
		$this->type		= $type;
		return $this;
	}

	/**
	 *	@param		array		$types
	 *	@return		self
	 */
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

	/**
	 *	@return		string
	 */
	protected function renderForm(): string
	{
		$w			= (object) $this->moduleWords['modal-form'];
		$optType	= $this->renderTypeOptions();

		$fieldSubject	= [
			Html::create( 'label', $w->labelSubject, ['for' => 'input_subject', 'class' => 'mandatory required'] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'subject',
				'id'		=> 'input_subject',
				'class'		=> 'span12',
				'required'	=> 'required',
				'value'		=> htmlentities( $this->subject, ENT_QUOTES, 'utf-8' ),
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
		$fieldBody	= [
			Html::create( 'label', $w->labelBodyQuestion, ['for' => 'input_body', 'class' => 'mandatory required optional type type-question' ] ),
			Html::create( 'label', $w->labelBodyRequest, ['for' => 'input_body', 'class' => 'mandatory required optional type type-request' ] ),
			Html::create( 'label', $w->labelBodyProblem, ['for' => 'input_body', 'class' => 'mandatory required optional type type-problem' ] ),
			Html::create( 'textarea', NULL, [
				'name'		=> 'body',
				'id'		=> 'input_body',
				'class'		=> 'span12',
				'rows'		=> 4,
				'required'	=> 'required',
			] ),
		];
		$fieldEmail	= [
			Html::create( 'label', $w->labelEmail, ['for' => 'input_email', 'class' => 'mandatory required'] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'email',
				'id'		=> 'input_email',
				'class'		=> 'span12',
				'required'	=> 'required',
			] ),
		];
		$fieldPhone	= [
			Html::create( 'label', $w->labelPhone, ['for' => 'input_phone', 'class' => ''] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'phone',
				'id'		=> 'input_phone',
				'class'		=> 'span12',
			] ),
		];
		$fieldPerson	= [
			Html::create( 'label', $w->labelPerson, ['for' => 'input_person', 'class' => 'mandatory required'] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'person',
				'id'		=> 'input_person',
				'class'		=> 'span12',
				'required' => 'required',
			] ),
		];
		$fieldCompany	= [
			Html::create( 'label', $w->labelCompany, ['for' => 'input_company'] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'company',
				'id'		=> 'input_company',
				'class'		=> 'span12',
			] ),
		];
		$fieldStreet	= [
			Html::create( 'label', $w->labelStreet, ['for' => 'input_street'/*, 'class' => 'mandatory required'*/] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'street',
				'id'		=> 'input_street',
				'class'		=> 'span12',
//				'required'	=> 'required',
			] ),
		];
		$fieldPostcode	= [
			Html::create( 'label', 'PLZ', ['for' => 'input_postcode'/*, 'class' => 'mandatory required'*/] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'postcode',
				'id'		=> 'input_postcode',
				'class'		=> 'span12',
//				'required'	=> 'required',
			] ),
		];
		$fieldCity	= [
			Html::create( 'label', $w->labelCity, ['for' => 'input_city'/*, 'class' => 'mandatory required'*/] ),
			Html::create( 'input', NULL, [
				'type'		=> 'text',
				'name'		=> 'city',
				'id'		=> 'input_city',
				'class'		=> 'span12',
//				'required'	=> 'required',
			] ),
		];
		if( !$this->moduleConfig->get( 'modal.show.company' ) )
			$fieldCompany	= '';

		return Html::create( 'div', [
			Html::create( 'div', [
				Html::create( 'div', $fieldSubject, ['class' => 'span10 offset1'] ),
			], ['class' => 'row-fluid'] ),
			Html::create( 'div', [
				Html::create( 'div', $fieldType, ['class' => 'span10 offset1'] ),
			], ['class' => 'row-fluid'] ),
			Html::create( 'div', [
				Html::create( 'div', $fieldBody, ['class' => 'span10 offset1'] ),
			], ['class' => 'row-fluid'] ),
			Html::create( 'div', [
				Html::create( 'div', $fieldEmail, ['class' => 'span6 offset1'] ),
				Html::create( 'div', $fieldPhone, ['class' => 'span4'] ),
			], ['class' => 'row-fluid'] ),
			Html::create( 'div', [
				Html::create( 'div', $fieldPerson, ['class' => 'span5 offset1'] ),
				Html::create( 'div', $fieldCompany, ['class' => 'span5'] ),
			], ['class' => 'row-fluid'] ),
			Html::create( 'div', [
				Html::create( 'div', $fieldStreet, ['class' => 'span5 offset1'] ),
				Html::create( 'div', $fieldCity, ['class' => 'span3'] ),
				Html::create( 'div', $fieldPostcode, ['class' => 'span2'] ),
			], ['class' => 'row-fluid optional type type-request'] ),
		] );
	}

	/**
	 *	@return		string
	 */
	protected function renderTypeOptions(): string
	{
		//  TYPES: Enabled through config and hook param
		$typesConfig	= [];
		foreach( $this->moduleConfig->getAll( 'modal.show.type.' ) as $key => $value )
			if( $value )
				$typesConfig[]	= $key;
		$typesHook		= $this->types ?: $typesConfig;
		$typesEnabled	= array_intersect( $typesConfig, $typesHook );

		//  DEFAULT TYPE: Set by config or hook param
		$defaultType		= NULL;
		$defaultTypeConfig	= $this->moduleConfig->get( 'modal.default.type' );
		if( in_array( $defaultTypeConfig, $typesEnabled ) )
			$defaultType	= $defaultTypeConfig;
		if( $this->type )
			if( in_array( $this->type, $typesEnabled ) )
				$defaultType	= $this->type;

		//  TYPE OPTIONS: Reduce to enabled types
		$optType	= $this->moduleWords['form-types'];
		foreach( $optType as $optTypeKey => $optTypeValue )
			if( !in_array( $optTypeKey, $typesEnabled ) )
				unset( $optType[$optTypeKey] );

		//  TYPE OPTIONS: Render with default type
		return HtmlElements::Options( $optType, $defaultType );
	}
}
