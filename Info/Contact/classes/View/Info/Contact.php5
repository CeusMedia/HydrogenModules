<?php
class View_Info_Contact extends CMF_Hydrogen_View{

	static public function ___onRenderContent( CMF_Hydrogen_Environment $env, $context, $module, $data = array() ){
		$processor		= new Logic_Shortcode( $env );
		$processor->setContent( $data->content );
		$words			= $env->getLanguage()->getWords( 'info/contact' );
		$shortCodes		= array(
			'contact:form'		=> array(
				'button-class'	=> 'btn',
				'button-label'	=> $words['form']['trigger'],
				'heading'		=> $words['form']['heading'],
				'icon-class'	=> 'fa-envelope',
				'icon-position'	=> 'left',
				'subject'		=> '',
			)
		);
		foreach( $shortCodes as $shortCode => $defaultAttributes ){
			if( !$processor->has( $shortCode ) )
				continue;
			$helperModal	= new View_Helper_Info_Contact_Form_Modal( $env );
			$helperTrigger	= new View_Helper_Info_Contact_Form_Trigger( $env );
			while( ( $attr = $processor->find( $shortCode, $defaultAttributes ) ) ){
				try{
					if( substr( $attr['button-class'], 0, 4 ) === 'btn-' )
						$attr['button-class']	= 'btn '.$attr['button-class'];
					if( substr( $attr['icon-class'], 0, 3 ) === 'fa-' )
						$attr['icon-class']	= 'fa fa-fw '.$attr['icon-class'];

					$modalId	= 'modal-'.uniqid();
					$helperModal->setId( $modalId );
					$helperModal->setHeading( $attr['heading'] );
					$helperModal->setSubject( trim( $attr['subject'] ) );
		//			$helperModal->setFrom( $env->getRequest()->get( '__path' ) );
					$helperTrigger->setmodalId( $modalId );
					$helperTrigger->setClass( $attr['button-class'] );
					$helperTrigger->setLabel( $attr['button-label'] );
					$helperTrigger->setIcon( $attr['icon-class'] );
					$helperTrigger->setIconPosition( $attr['icon-position'] );
					$replacement	= $helperTrigger->render().$helperModal->render();		//  load news panel
					$processor->replaceNext(
						$shortCode,
						$replacement
					);
				}
				catch( Exception $e ){
					$env->getMessenger()->noteFailure( 'Short code failed: '.$code );
					break;
				}
			}
		}
		$data->content	= $processor->getContent();
	}

	public function index(){}
}

class ShortcodeReplace{
	public function render(){}
}
?>
