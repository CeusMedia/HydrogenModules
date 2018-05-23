<?php
abstract class Mail_Form_Abstract extends Mail_Abstract{

	protected $app;
	protected $usePremailer;

	public function __onInit(){
		$this->usePremailer	= $this->env->getConfig()->get( 'module.use.premailer' );
	}

	protected function applyFillData( $content, $fill ){
		$data	= json_decode( $fill->data, TRUE );
		while( preg_match( '/\[data_(\S+)\]/su', $content ) ){
			$identifier		= preg_replace( '/.*\[data_(\S+)\].*/su', "\\1", $content );
			$replace		= '';
			if( isset( $data[$identifier] ) ){
				$replace	= $data[$identifier]['value'];
				if( in_array( $data[$identifier]['type'], array( 'select' ) ) )
					$replace	= $data[$identifier]['valueLabel'];
			}
			$pattern		= '/'.preg_quote( '[data_'.$identifier.']' ).'/su';
			$content		= preg_replace( $pattern, $replace, $content, 1 );
		}
		return $content;
	}

	protected function applyHelpers( $content, $fill, $form ){
		while( preg_match( '/\[helper_(\S+)\]/su', $content ) ){
			$identifier		= preg_replace( '/.*\[helper_(\S+)\].*/su', "\\1", $content );
			$replace		= '';
			if( $identifier === "fill_person" ){
				$helperPerson	= new View_Helper_Form_Fill_Person( $this->app );
				$helperPerson->setFill( $fill );
				$helperPerson->setForm( $form );
				$replace		= $helperPerson->render();
			}
			else if( $identifier === "fill_data" ){
				$helperData	= new View_Helper_Form_Fill_Data( $this->app );
				$helperData->setFill( $fill );
				$helperData->setForm( $form );
				$replace		= $helperData->render();
			}
			$pattern		= '/'.preg_quote( '[helper_'.$identifier.']' ).'/su';
			$content		= preg_replace( $pattern, $replace, $content, 1 );
		}
		return $content;
	}
}
