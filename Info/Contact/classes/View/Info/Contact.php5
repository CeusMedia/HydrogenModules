<?php
class View_Info_Contact extends CMF_Hydrogen_View{

	static public function ___onRenderContent( $env, $context, $module, $data = array() ){
		$pattern		= "/^(.*)(\[contact:form([^\]]+)?\])(.*)$/sU";
		$helperModal	= new View_Helper_Info_Contact_Form_Modal( $env );
		$helperTrigger	= new View_Helper_Info_Contact_Form_Trigger( $env );
		$words			= $env->getLanguage()->getWords( 'info/contact' );
		$defaultAttr	= array(
			'button-class'	=> 'btn',
			'button-label'	=> $words['form']['trigger'],
			'heading'		=> $words['form']['heading'],
			'icon-class'	=> 'fa-envelope',
			'icon-position'	=> 'left',
			'subject'		=> '',
		);
		$modals			= array();
		while( preg_match( $pattern, $data->content ) ){
			$modalId	= 'modal-'.uniqid();
			$code		= preg_replace( $pattern, "\\2", $data->content );
			$code		= preg_replace( '/(\r|\n|\t)/', " ", $code );
			$code		= preg_replace( '/( ){2,}/', " ", $code );
			$code		= str_replace( 'contact:form', "contactForm", trim( $code ) );
			try{
				$node		= new @XML_Element( '<'.substr( $code, 1, -1 ).'/>' );
				$attr		= array_merge( $defaultAttr, $node->getAttributes() );
				if( substr( $attr['button-class'], 0, 4 ) === 'btn-' )
					$attr['button-class']	= 'btn '.$attr['button-class'];
				if( substr( $attr['icon-class'], 0, 3 ) === 'fa-' )
					$attr['icon-class']	= 'fa fa-fw '.$attr['icon-class'];

				$helperModal->setId( $modalId );
				$helperModal->setHeading( $attr['heading'] );
				$helperModal->setSubject( trim( $attr['subject'] ) );
	//			$helperModal->setFrom( $env->getRequest()->get( '__path' ) );
				$helperTrigger->setmodalId( $modalId );
				$helperTrigger->setClass( $attr['button-class'] );
				$helperTrigger->setLabel( $attr['button-label'] );
				$helperTrigger->setIcon( $attr['icon-class'] );
				$helperTrigger->setIconPosition( $attr['icon-position'] );
				$modals[]		= $helperModal->render();
				$subcontent		= $helperTrigger->render();													//  load news panel
			}
			catch( Exception $e ){
				$env->getMessenger()->noteFailure( 'Short code failed: '.$code );
				$subcontent	= '';
			}

			$replacement	= "\\1".$subcontent."\\4";												//  insert content of nested page...
			$data->content	= preg_replace( $pattern, $replacement, $data->content );				//  ...into page content
		}
		$data->content	.= join( $modals );
	}

	public function index(){}
}

class ShortcodeReplace{
	public function render(){}
}
?>
