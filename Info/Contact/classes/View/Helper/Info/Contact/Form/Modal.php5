<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use UI_HTML_Tag as Html;

class View_Helper_Info_Contact_Form_Modal
{
	protected $id;
	protected $subject;
	protected $heading;
	protected $type;
	protected $types;
	protected $moduleConfig;
	protected $moduleWords;

	public function __construct( $env )
	{
		$this->env			= $env;
		$this->moduleConfig	= $env->getConfig()->getAll( 'module.info_contact.', TRUE );
		$this->moduleWords	= $this->env->getLanguage()->getWords( 'info/contact' );
	}

	public function render()
	{
		if( !$this->id )
			throw new RuntimeException( 'No ID set' );

		$w			= (object) $this->moduleWords['modal-form'];
		$optType	= $this->renderTypeOptions();

		$fieldSubject	= array(
			Html::create( 'label', $w->labelSubject, array( 'for' => 'input_subject', 'class' => 'mandatory required' ) ),
			Html::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'subject',
				'id'		=> 'input_subject',
				'class'		=> 'span12',
				'required'	=> 'required',
				'value'		=> htmlentities( $this->subject, ENT_QUOTES, 'utf-8' ),
			) ),
		);
		$fieldType	= array(
			Html::create( 'label', $w->labelType, array( 'for' => 'input_type' ) ),
			Html::create( 'select', $optType, array(
				'name'		=> 'type',
				'id'		=> 'input_type',
				'class'		=> 'span12 has-optionals',
			) ),
		);
		$fieldBody	= array(
			Html::create( 'label', $w->labelBodyQuestion, array( 'for' => 'input_body', 'class' => 'mandatory required optional type type-question'  ) ),
			Html::create( 'label', $w->labelBodyRequest, array( 'for' => 'input_body', 'class' => 'mandatory required optional type type-request'  ) ),
			Html::create( 'label', $w->labelBodyProblem, array( 'for' => 'input_body', 'class' => 'mandatory required optional type type-problem'  ) ),
			Html::create( 'textarea', NULL, array(
				'name'		=> 'body',
				'id'		=> 'input_body',
				'class'		=> 'span12',
				'rows'		=> 4,
				'required'	=> 'required',
			) ),
		);
		$fieldEmail	= array(
			Html::create( 'label', $w->labelEmail, array( 'for' => 'input_email', 'class' => 'mandatory required' ) ),
			Html::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'email',
				'id'		=> 'input_email',
				'class'		=> 'span12',
				'required'	=> 'required',
			) ),
		);
		$fieldPhone	= array(
			Html::create( 'label', $w->labelPhone, array( 'for' => 'input_phone', 'class' => '' ) ),
			Html::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'phone',
				'id'		=> 'input_phone',
				'class'		=> 'span12',
			) ),
		);
		$fieldPerson	= array(
			Html::create( 'label', $w->labelPerson, array( 'for' => 'input_person', 'class' => 'mandatory required' ) ),
			Html::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'person',
				'id'		=> 'input_person',
				'class'		=> 'span12',
				'required' => 'required',
			) ),
		);
		$fieldCompany	= array(
			Html::create( 'label', $w->labelCompany, array( 'for' => 'input_company' ) ),
			Html::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'company',
				'id'		=> 'input_company',
				'class'		=> 'span12',
			) ),
		);
		$fieldStreet	= array(
			Html::create( 'label', $w->labelStreet, array( 'for' => 'input_street'/*, 'class' => 'mandatory required'*/ ) ),
			Html::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'street',
				'id'		=> 'input_street',
				'class'		=> 'span12',
//				'required'	=> 'required',
			) ),
		);
		$fieldCity	= array(
			Html::create( 'label', $w->labelCity, array( 'for' => 'input_city'/*, 'class' => 'mandatory required'*/ ) ),
			Html::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'city',
				'id'		=> 'input_city',
				'class'		=> 'span12',
//				'required'	=> 'required',
			) ),
		);
		$fieldPostcode	= array(
			Html::create( 'label', 'PLZ', array( 'for' => 'input_postcode'/*, 'class' => 'mandatory required'*/ ) ),
			Html::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'postcode',
				'id'		=> 'input_postcode',
				'class'		=> 'span12',
//				'required'	=> 'required',
			) ),
		);
		if( !$this->moduleConfig->get( 'modal.show.company' ) )
			$fieldCompany	= '';

		$form	= Html::create( 'div', array(
			Html::create( 'div', array(
				Html::create( 'div', $fieldSubject, array( 'class' => 'span10 offset1' ) ),
			), array( 'class' => 'row-fluid' ) ),
			Html::create( 'div', array(
				Html::create( 'div', $fieldType, array( 'class' => 'span10 offset1' ) ),
			), array( 'class' => 'row-fluid' ) ),
			Html::create( 'div', array(
				Html::create( 'div', $fieldBody, array( 'class' => 'span10 offset1' ) ),
			), array( 'class' => 'row-fluid' ) ),
			Html::create( 'div', array(
				Html::create( 'div', $fieldEmail, array( 'class' => 'span6 offset1' ) ),
				Html::create( 'div', $fieldPhone, array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
			Html::create( 'div', array(
				Html::create( 'div', $fieldPerson, array( 'class' => 'span5 offset1' ) ),
				Html::create( 'div', $fieldCompany, array( 'class' => 'span5' ) ),
			), array( 'class' => 'row-fluid' ) ),
			Html::create( 'div', array(
				Html::create( 'div', $fieldStreet, array( 'class' => 'span5 offset1' ) ),
				Html::create( 'div', $fieldCity, array( 'class' => 'span3' ) ),
				Html::create( 'div', $fieldPostcode, array( 'class' => 'span2' ) ),
			), array( 'class' => 'row-fluid optional type type-request' ) ),
		) );

		$iconCancel	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
		$iconSave	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
		$modal	= new BootstrapModalDialog( $this->id );
//		$modal->setFormAction( './info/contact/form'.( $this->from ? '?from='.$this->from : '' ) );
		$modal->setFormAction( './info/contact/ajax/form' );
		$modal->setFormSubmit( 'ModuleInfoContactForm.sendContactForm(this)' );
		$modal->setHeading( $this->heading ? $this->heading : $words['form']['heading'] );
		$modal->setBody( $form );
		$modal->setFade( !FALSE );
		$modal->setAttributes( array( 'class' => 'modal-info-contact-form' ) );
		$modal->setSubmitButtonLabel( $iconSave.'&nbsp;'.$w->buttonSave );
		$modal->setSubmitButtonClass( 'btn btn-primary not-btn-large' );
		$modal->setCloseButtonLabel( $iconCancel.'&nbsp;'.$w->buttonCancel );
		$modal->setCloseButtonClass( 'btn btn-small' );
		return $modal->render();
	}

	//  --  SETTERS  --  //
	public function setId( $id ): self
	{
		$this->id		= $id;
		return $this;
	}

	public function setHeading( $heading ): self
	{
		$this->heading		= $heading;
		return $this;
	}

	public function setSubject( $subject ): self
	{
		$this->subject	= $subject;
		return $this;
	}

	public function setType( $type ): self
	{
		$this->type		= $type;
		return $this;
	}

	public function setTypes( $types ): self
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
	protected function renderTypeOptions()
	{
		//  TYPES: Enabled thru config and hook param
		$typesConfig	= array();
		foreach( $this->moduleConfig->getAll( 'modal.show.type.' ) as $key => $value )
			if( $value )
				$typesConfig[]	= $key;
		$typesHook		= $this->types ? $this->types : $typesConfig;
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
		$optType	= UI_HTML_Elements::Options( $optType, $defaultType );
		return $optType;
	}
}
