<?php
class Mail_Form_Manager_Filled extends Mail_Form_Abstract{

	public $fill;
	public $form;

	/* @todo use block of mail */
	public function render(){
		$helperPerson	= new Helper_Form_Fill_Person( $this->env );
		$helperPerson->setFill( $this->fill );
		$helperPerson->setForm( $this->form );

		$helperData		= new Helper_Form_Fill_Data( $this->env );
		$helperData->setFill( $this->fill );
		$helperData->setForm( $this->form );

		$content	= '
<p><big><strong>Hallo!</strong></big></p>
<p>Das DtHPS-Formular "'.$this->form->title.'" wurde ausgefüllt und abgesendet.</p>
'.$helperPerson->render().'
'.$helperData->render().'
<p>Freundliche Grüße,<br/><em>DtHPS GmbH</em></p>';
		return $this->renderPage( $content );
	}

	public function setFill( $fill ){
		$this->fill		= $fill;
		return $this;
	}

	public function setForm( $form ){
		$this->form		= $form;
		return $this;
	}
}

