<?php
class Mail_Newsletter extends Mail_Abstract{

	protected function generate( $data = array() ){
		$logic	= new Logic_Newsletter( $this->env );
		$this->data['mailTemplateId']	= 0;
//		$logic->checkTemplateId( $data['templateId'], TRUE );

//		$words		= (object) $this->getWords( 'work/newsletter' );
		$helper	= new View_Helper_Newsletter_Mail( $this->env );
		if( isset( $data['readerLetterId'] ) ){
			$helper->setReaderLetterId( $data['readerLetterId'] );
			$letter	= $logic->getReaderLetter( $data['readerLetterId'] );
			$data['newsletterId']	= $letter->newsletterId;
			$helper->setReaderId( $letter->newsletterReaderId );
		}
		else{
			if( !isset( $data['newsletterId'] ) )
				throw new RuntimeException( 'No newsletter ID set' );
			if( !isset( $data['readerId'] ) )
				throw new RuntimeException( 'No reader ID set' );
			$helper->setNewsletterId( $data['newsletterId'] );
			$helper->setReaderId( $data['readerId'] );
		}

		$newsletter	= $logic->getNewsletter( $data['newsletterId'] );
		$subject	= str_replace( "%date%", date( 'd.m.Y' ), $newsletter->subject );
		$subject	= str_replace( "%time%", date( 'H:i:s' ), $subject );
		$this->setSubject( $subject );

		$this->mail->addHeaderPair( 'X-Auto-Response-Suppress', 'All' );
		$this->mail->setSender( $newsletter->senderAddress, $newsletter->senderName );

		$helper->setData( $data );
		$helper->setMode( View_Helper_Newsletter_Mail::MODE_PLAIN );
		$plain	= $helper->render();
		$this->setText( $plain );

		$helper->setMode( View_Helper_Newsletter_Mail::MODE_HTML );
		$html	= $helper->render();
		if( $this->env->getConfig()->get( 'module.resource_newsletter.premailer.html' ) ){
			$premailer	= new Net_API_Premailer();
			try{
				$response	= $premailer->convertFromHtml( $html, array(
					'preserve_styles'	=> FALSE,
					'remove_ids'		=> TRUE,
					'remove_classes'	=> TRUE,
					'remove_comments'	=> TRUE,
				) );
				$converted	= $premailer->getHtml();
				$html		= $converted;
			}
			catch( Exception $e ){}
		}

		$this->setHtml( $html );

		return (object) array(
			'plain'	=> $plain,
			'html'	=> $html,
		);
	}
}
