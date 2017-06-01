<?php
class View_Admin_Mail_Queue extends CMF_Hydrogen_View{

	public function __onInit(){
	}

	public function ajaxRenderDashboardPanel(){
		$model	= new Model_Mail( $this->env );
		$count	= $model->count( array( 'status' => 1 ) );

		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'dl', array(
				UI_HTML_Tag::create( 'dt', 'nicht versendet' ),
				UI_HTML_Tag::create( 'dd', $count )
			), array( 'class' => 'not-dl-horizontal' ) ),
		) );
	}

	public function enqueue(){
	}

	public function index(){
	}

	public function view(){
	}

	public function renderFact( $key, $value ){
		$words	= $this->env->getLanguage()->getWords( 'admin/mail/queue' );
		if( in_array( $key, array( "object" ) ) )
			return;
		if( preg_match( "/At$/", $key ) ){
			if( !( (int) $value ) )
				return;
			$helper	= new View_Helper_TimePhraser( $this->env );
			$date	= date( 'Y-m-d H:i:s', $value );
			$phrase	= $helper->convert( $value, TRUE, 'vor ' );
			$value	= $phrase.'&nbsp;<small class="muted">('.$date.')</small>';
		}
		else if( preg_match( '/Id$/', $key ) ){
			if( (int) $value === 0 )
				return;
		}
		else if( preg_match( '/Address/', $key ) && strlen( $value ) ){
			$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-envelope' ) );
			$link	= UI_HTML_Tag::create( 'a', $value, array( 'href' => 'mailto:'.$value ) );
			$value	= $icon.'&nbsp;'.$link;
		}
		else if( $key === "status" ){
			$value = $words['states'][$value].' <small class="muted">('.$value.')</small>';
		}
		else{
			if( !strlen( $value ) )
				return;
		}
		$label	= $words['view-facts']['label'.ucfirst( $key )];
		$term	= UI_HTML_Tag::create( 'dt', $label );
		$def	= UI_HTML_Tag::create( 'dd', $value.'&nbsp;' );
		return $term.$def;
	}


	protected function getMailParts( $mail ){
		if( !$mail->object )
			throw new Exception( 'No mail object available' );
		if( $mail->object->mail instanceof \CeusMedia\Mail\Message )								//  modern mail message with parsed body parts
			return $mail->object->mail->getParts( TRUE );

		//  support for older implementation using cmClasses
		if( !class_exists( 'CMM_Mail_Parser' ) )													//  @todo change to \CeusMedia\Mail\Parser
			throw new RuntimeException( 'No mail parser available.' );
		return CMM_Mail_Parser::parseBody( $mail->object->mail->getBody() );
	}

	public function html(){
		try{
			$mail	= $this->getData( 'mail' );
			$helper	= new View_Helper_Mail_View_HTML( $this->env );
			$helper->setMail( $mail );
			print( $helper->render() );
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
		}
		exit;
	}
}
?>
