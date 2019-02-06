<?php
class View_Helper_Info_Contact_Form_Modal{

	protected $id;
	protected $subject;
	protected $heading;

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function render(){
		if( !$this->id )
			throw new RuntimeException( 'No ID set' );

		$words		= $this->env->getLanguage()->getWords( 'info/contact' );
		$w			= (object) $words['form'];
		$optType	= $words['form-types'];
		$optType	= UI_HTML_Elements::Options( $optType );

		$fieldSubject	= array(
			UI_HTML_Tag::create( 'label', $w->labelSubject, array( 'for' => 'input_subject', 'class' => 'mandatory required' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'subject',
				'id'		=> 'input_subject',
				'class'		=> 'span12',
				'required'	=> 'required',
				'value'		=> htmlentities( $this->subject, ENT_QUOTES, 'utf-8' ),
			) ),
		);
		$fieldType	= array(
			UI_HTML_Tag::create( 'label', $w->labelType, array( 'for' => 'input_type' ) ),
			UI_HTML_Tag::create( 'select', $optType, array(
				'name'		=> 'type',
				'id'		=> 'input_type',
				'class'		=> 'span12 has-optionals',
			) ),
		);
		$fieldBodyQuestion	= array(
			UI_HTML_Tag::create( 'label', $w->labelBodyQuestion, array( 'for' => 'input_body0', 'class' => 'mandatory required'  ) ),
			UI_HTML_Tag::create( 'textarea', NULL, array(
				'name'		=> 'body0',
				'id'		=> 'input_body0',
				'class'		=> 'span12',
				'rows'		=> 4,
				'required'	=> 'required',
			) ),
		);
		$fieldBodyRequest	= array(
			UI_HTML_Tag::create( 'label', $w->labelBodyRequest, array( 'for' => 'input_body1', 'class' => 'mandatory required'  ) ),
			UI_HTML_Tag::create( 'textarea', NULL, array(
				'name'		=> 'body1',
				'id'		=> 'input_body1',
				'class'		=> 'span12',
				'rows'		=> 4,
				'required'	=> 'required',
			) ),
		);
		$fieldBodyProblem	= array(
			UI_HTML_Tag::create( 'label', $w->labelBodyProblem, array( 'for' => 'input_body2', 'class' => 'mandatory required'  ) ),
			UI_HTML_Tag::create( 'textarea', NULL, array(
				'name'		=> 'body2',
				'id'		=> 'input_body2',
				'class'		=> 'span12',
				'rows'		=> 4,
				'required'	=> 'required',
			) ),
		);
		$fieldEmail	= array(
			UI_HTML_Tag::create( 'label', $w->labelEmail, array( 'for' => 'input_email', 'class' => 'mandatory required' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'email',
				'id'		=> 'input_email',
				'class'		=> 'span12',
				'required'	=> 'required',
			) ),
		);
		$fieldPhone	= array(
			UI_HTML_Tag::create( 'label', $w->labelPhone, array( 'for' => 'input_phone', 'class' => '' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'phone',
				'id'		=> 'input_phone',
				'class'		=> 'span12',
			) ),
		);
		$fieldPerson	= array(
			UI_HTML_Tag::create( 'label', $w->labelPerson, array( 'for' => 'input_person', 'class' => 'mandatory required' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'person',
				'id'		=> 'input_person',
				'class'		=> 'span12',
				'required' => 'required',
			) ),
		);
		$fieldCompany	= array(
			UI_HTML_Tag::create( 'label', $w->labelCompany, array( 'for' => 'input_company' ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'company',
				'id'		=> 'input_company',
				'class'		=> 'span12',
			) ),
		);
		$fieldStreet	= array(
			UI_HTML_Tag::create( 'label', $w->labelStreet, array( 'for' => 'input_street'/*, 'class' => 'mandatory required'*/ ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'street',
				'id'		=> 'input_street',
				'class'		=> 'span12',
//				'required'	=> 'required',
			) ),
		);
		$fieldCity	= array(
			UI_HTML_Tag::create( 'label', $w->labelCity, array( 'for' => 'input_city'/*, 'class' => 'mandatory required'*/ ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'city',
				'id'		=> 'input_city',
				'class'		=> 'span12',
//				'required'	=> 'required',
			) ),
		);
		$fieldPostcode	= array(
			UI_HTML_Tag::create( 'label', 'PLZ', array( 'for' => 'input_postcode'/*, 'class' => 'mandatory required'*/ ) ),
			UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'text',
				'name'		=> 'postcode',
				'id'		=> 'input_postcode',
				'class'		=> 'span12',
//				'required'	=> 'required',
			) ),
		);

		$form	= UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $fieldSubject, array( 'class' => 'span10 offset1' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $fieldType, array( 'class' => 'span10 offset1' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $fieldBodyQuestion, array( 'class' => 'span10 offset1' ) ),
			), array( 'class' => 'row-fluid optional type type-0' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $fieldBodyRequest, array( 'class' => 'span10 offset1' ) ),
			), array( 'class' => 'row-fluid optional type type-1' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $fieldBodyProblem, array( 'class' => 'span10 offset1' ) ),
			), array( 'class' => 'row-fluid optional type type-2' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $fieldEmail, array( 'class' => 'span6 offset1' ) ),
				UI_HTML_Tag::create( 'div', $fieldPhone, array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $fieldPerson, array( 'class' => 'span5 offset1' ) ),
				UI_HTML_Tag::create( 'div', $fieldCompany, array( 'class' => 'span5' ) ),
			), array( 'class' => 'row-fluid' ) ),
			UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'div', $fieldStreet, array( 'class' => 'span5 offset1' ) ),
				UI_HTML_Tag::create( 'div', $fieldCity, array( 'class' => 'span3' ) ),
				UI_HTML_Tag::create( 'div', $fieldPostcode, array( 'class' => 'span2' ) ),
			), array( 'class' => 'row-fluid optional type type-1' ) ),
			UI_HTML_Tag::create( 'div', array(
			), array( 'class' => 'row-fluid' ) ),
		) );

		$iconCancel	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
		$iconSave	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
		$modal	= new \CeusMedia\Bootstrap\Modal( $this->id );
//		$modal->setFormAction( './info/contact/form'.( $this->from ? '?from='.$this->from : '' ) );
		$modal->setFormAction( './info/contact/ajaxForm' );
		$modal->setFormSubmit( 'ModuleInfoContactForm.sendContactForm(this)' );
		$modal->setHeading( $this->heading ? $this->heading : $words['form']['heading'] );
		$modal->setBody( $form );
		$modal->setFade( !FALSE );
		$modal->setAttributes( array( 'class' => 'modal-info-contact-form' ) );
		$modal->setSubmitButtonLabel( $iconSave.'&nbsp;'.$words['form']['buttonSave'] );
		$modal->setSubmitButtonClass( 'btn btn-primary not-btn-large' );
		$modal->setCloseButtonLabel( $iconCancel.'&nbsp;'.$words['form']['buttonCancel'] );
		$modal->setCloseButtonClass( 'btn btn-small' );
		return $modal->render();
	}

	public function setId( $id ){
		$this->id		= $id;
	}

	public function setSubject( $subject ){
		$this->subject	= $subject;
	}

	public function setHeading( $heading ){
		$this->heading		= $heading;
	}
/*
	public function setFrom( $from ){
		$this->from		= $from;
	}*/
}
