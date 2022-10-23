<?php
abstract class Mail_Form_Abstract extends Mail_Abstract
{
	/**
	 *	Sets subject without applying default subject extensions.
	 *	Overrides default mail subject handling to avoid adding default subject prefix or template.
	 *	@access		public
	 *	@param		string		$subject		Subject to set on mail
	 *	@param		boolean		$usePrefix		For compatibility only, no function
	 *	@param		boolean		$useTemplate	For compatibility only, no function
	 *	@return		self
	 */
	public function setSubject( string $subject, bool $usePrefix = TRUE, bool $useTemplate = TRUE ): self
	{
		$this->mail->setSubject( $subject, $this->encodingSubject );
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function applyFillData( string $content, $fill ): string
	{
		$data	= json_decode( $fill->data, TRUE );
		while( preg_match( '/\[data_(\S+)\]/su', $content ) ){
			$identifier		= preg_replace( '/.*\[data_(\S+)\].*/su', "\\1", $content );
			$replace		= '';
			if( isset( $data[$identifier] ) ){
				$replace	= $data[$identifier]['value'];
				if( in_array( $data[$identifier]['type'], ['select'] ) )
					$replace	= $data[$identifier]['valueLabel'];
			}
			$pattern		= '/'.preg_quote( '[data_'.$identifier.']' ).'/su';
			$content		= preg_replace( $pattern, $replace, $content, 1 );
		}
		return $content;
	}

	protected function applyHelpers( string $content, $fill, $form, bool $extended = FALSE ): string
	{
		while( preg_match( '/\[helper_(\S+)\]/su', $content ) ){
			$identifier		= preg_replace( '/.*\[helper_(\S+)\].*/su', "\\1", $content );
			$replace		= '';
			if( $identifier === "fill_person" ){
				$helperPerson	= new View_Helper_Form_Fill_Person( $this->env );
				$helperPerson->setFill( $fill );
				$helperPerson->setForm( $form );
				$replace		= $helperPerson->render();
			}
			else if( $identifier === "fill_data" ){
				$helperData	= new View_Helper_Form_Fill_Data( $this->env );
				if( $extended )
					$helperData->setMode( View_Helper_Form_Fill_Data::MODE_EXTENDED );
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
