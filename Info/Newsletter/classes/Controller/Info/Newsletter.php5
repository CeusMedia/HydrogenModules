<?php
class Controller_Info_Newsletter extends CMF_Hydrogen_Controller
{
	/**	@var	Logic_Newsletter	$logic */
	protected $logic;
	protected $messenger;
	protected $request;
	protected $session;

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment instance
	 *	@param		object						$context	Hook context object
	 *	@param		object						$module		Module object
	 *	@param		public						$arguments	Map of hook arguments
	 *	@return		void
	 *	@todo		finish implementation, extract to hook class and register in module config
	 */
	public static function __onRenderServicePanels( CMF_Hydrogen_Environment $env, $context, $module, $payload = array() )
	{
		if( empty( $payload['orderId'] ) || empty( $payload['paymentBackends'] ) )
			return;
		$view		= new CMF_Hydrogen_View( $env );
//		$modelOrder	= new Model_Shop_Order( $env );
//		$order		= $modelOrder->get( $payload['orderId'] );

		$path	= 'html/info/newsletter/';
		$files	= array(
			1	=> 'finishTop.html',
			3	=> 'finishAbove.html',
			5	=> 'finish.html',
			7	=> 'finishBelow.html',
			9	=> 'finishBottom.html',
		);
		foreach( $files as $priority => $file ){
			if( $view->hasContentFile( $path.$file ) ){
				$content	= $view->loadContentFile( $path.$file );
				$context->registerServicePanel( 'Newsletter:'.$priority, $content, $priority );
			}
		}



		$localeFile	= 'html/info/newsletter/finishPanel.html';
		if( $view->hasContentFile( $localeFile ) ){
			$content	= $view->loadContentFile( $localeFile );
			$context->registerServicePanel( 'Newsletter', $content, 8 );
		}
	}

	public function confirm( $readerId, $key = NULL )
	{
		$words		= (object) $this->getWords( 'confirm' );
		$reader		= $this->logic->getReader( $readerId );

		if( !$reader || $reader->status != 0 )
			$this->messenger->noteError( $words->msgErrorReaderInvalid );
		else if( !strlen( trim( $key ) ) )
			$this->messenger->noteError( $words->msgErrorKeyMissing );
		else if( $key !== substr( md5( 'InfoNewsletterSalt:'.$readerId ), 10, 10 ) )
			$this->messenger->noteError( $words->msgErrorKeyInvalid );
		else{
			$this->logic->editReader( $readerId, array( 'status' => 1 ) );
			$this->messenger->noteSuccess( $words->msgSuccess, $reader->email );
		}
		$this->restart( NULL, TRUE );
	}

/*	public function edit( $readerId, $letterId, $key = NULL ){
		$request	= $this->env->getRequest();
		$reader		= $this->logic->getReader( $readerId );
		if( !$reader ){
			$this->env->getMessenger()->noteError( 'Sie sind nicht beim Newsletter angemeldet.' );
			$this->restart( NULL, TRUE );
		}
		$this->messenger->noteNotice( substr( md5( $readerId.'_'.$letterId.'_'.$reader->email ), 10, 10 ) );
		if( $key !== substr( md5( $readerId.'_'.$letterId.'_'.$reader->email ), 10, 10 ) ){
			$this->env->getMessenger()->noteError( 'Zugriff verweigert' );
			$this->restart( NULL, TRUE );
		}
		if( $request->has( 'save' ) ){

			print_m( $request->get( 'groups' ) );
			die;

		}
		$this->addData( 'reader', $reader );
		$this->addData( 'groups', $this->logic->getGroups( array( 'type' => array( 0, 2 ), 'status' => '> 0' ) ) );
		$this->addData( 'subscriptions', $this->logic->getGroupsOfReader( $readerId ) );
		$this->addData( 'reader', $this->logic->getReader( $readerId ) );
		$this->addData( 'letters', $this->logic->getLettersOfReader( $readerId ) );
		$this->addData( 'letter', $this->logic->getReaderLetter( $letterId ) );
		$this->addData( 'key', $key );
		$this->addData( 'letterId', $letterId );
		$this->addData( 'readerId', $readerId );
	}*/

	public function index( $arg1 = NULL )
	{
		$words		= (object) $this->getWords( 'index' );
		if( $this->request->has( 'save' ) ){
			$language			= $this->env->getLanguage()->getLanguage();
			$readersFoundByMail	= $this->logic->getReaders( array(
				'email'		=> trim( $this->request->get( 'email' ) ),
				'status'	=> array( 0, 1 ),
			) );
			if( !strlen( trim( $this->request->get( 'email' ) ) ) )
				$this->messenger->noteError( $words->msgErrorNoEmail );
			else if( !strlen( trim( $this->request->get( 'firstname' ) ) ) )
				$this->messenger->noteError( $words->msgErrorNoFirstname );
			else if( !strlen( trim( $this->request->get( 'surname' ) ) ) )
				$this->messenger->noteError( $words->msgErrorNoSurname );
			else if( $readersFoundByMail )
				$this->messenger->noteError( $words->msgErrorEmailRegistered );
			else {
				$data					= $this->request->getAll();
				$data['language']		= $language;
				$data['status']			= 0;
				$data['registeredAt']	= time();
				$readerId	= $this->logic->addReader( $data );
				$reader		= $this->logic->getReader( $readerId );

				if( $this->request->get( 'groups' ) )
					foreach( $this->request->get( 'groups' ) as $groupId )						//  iterate selected groups
						$this->logic->addReaderToGroup( $readerId, $groupId );					//  add reader to group
				$conditions	= array(															//  get active automatic groups
					'status'	=> Model_Newsletter_Group::STATUS_USABLE,						//  status: active
					'type'		=> Model_Newsletter_Group::TYPE_AUTOMATIC,
				);
				foreach( $this->logic->getGroups( $conditions ) as $group )						//  iterate found groups
					$this->logic->addReaderToGroup( $readerId, $group->newsletterGroupId );		//  add reader to group

				$data	= array(
					'readerId'		=> $readerId,
					'reader'		=> $reader,
				);
				$mail	= new Mail_Info_Newsletter_Register( $this->env, $data );
				$logicMail	= Logic_Mail::getInstance( $this->env );
				$logicMail->appendRegisteredAttachments( $mail, $language );
				$receiver	= (object) array(
					'username'	=> $reader->firstname.' '.$reader->surname,
					'email'		=> $reader->email,
				);
				$logicMail->handleMail( $mail, $receiver, $language );
				$this->messenger->noteSuccess( $words->msgSuccess, $reader->email );
				$this->restart( NULL, TRUE );
			}
		}
		$this->addData( 'data', $this->request->getAll( '', TRUE ) );

		$requestedGroups	= $this->request->get( 'groups' );
		$requestedGroups	= is_array( $requestedGroups ) ? $requestedGroups : array();
		$groups	= $this->logic->getGroups( array(
			'status'	=> Model_Newsletter_Group::STATUS_USABLE,								//  status: active
			'type'		=> array(																//  type: default or automatic
				Model_Newsletter_Group::TYPE_DEFAULT,
				Model_Newsletter_Group::TYPE_AUTOMATIC,
			),
		), array( 'title' => 'ASC' ) );
		foreach( $groups as $group )
			$group->isChecked	= in_array( $group->newsletterGroupId, $requestedGroups );
		$this->addData( 'groups', $groups );

		$conditions		= array( 'status' => Model_Newsletter::STATUS_SENT );
		$orders			= array( 'newsletterId' => 'DESC' );
		$newsletters	= $this->logic->getNewsletters( $conditions, $orders );
		$latest			= $newsletters ? array_shift( $newsletters ) : NULL;
		$this->addData( 'canShowLatest', $newsletters );
		$this->addData( 'latest', $latest );
	}

	public function preview( $newsletterId = NULL )
	{
		if( !$newsletterId ){
			$newsletters	= $this->logic->getNewsletters( array( 'status' => '>= 1' ), array( 'newsletterId' => 'DESC' ) );
			if( $newsletters ){
				$latest			= array_shift( $newsletters );
				$newsletterId	= $latest->newsletterId;
			}
		}

		if( !$newsletterId )
			return;

		$newsletter	= $this->logic->getNewsletter( $newsletterId );
		if( !$newsletter || !( $newsletter->status >= 1 ) ){
			return;
			$this->messenger->noteError( 'Invalid newsletter ID.' );
			$this->restart( NULL, TRUE );
		}
		$helper	= new View_Helper_Newsletter( $this->env, $newsletter->newsletterTemplateId );
		$html	= $helper->renderNewsletterHtml( $newsletter->newsletterId );
		print( $html );
		exit;
	}

	public function unregister( $emailHash = NULL, $readerLetterId = NULL )
	{
		$email		= trim( $emailHash ? base64_decode( $emailHash ) : $this->request->get( 'email' ) );
		$words		= (object) $this->getWords( 'unregister' );
		$reader		= NULL;

		if( strlen( $email ) ){
			$reader	= $this->logic->getActiveReaderFromEmail( $email, TRUE, FALSE );
			if( !$reader ){
				$this->messenger->noteError( $words->msgInvalidEmail, $email );
				$this->restart( 'unregister', TRUE );
			}
			$reader->groups	= $this->logic->getGroupsOfReader( $reader->newsletterReaderId, array(), array( 'title' => 'ASC' ) );
			if( $this->request->has( 'save' ) ){
				$mode	= $this->request->get( 'mode' );
				if( $this->request->has( 'disable' ) )
					$mode	= 'all';

				$unregisterGroups	= $this->request->get( 'groupIds' );
				if( $mode === "all" ){
					$unregisterGroups	= array();
					foreach( $reader->groups as $registeredGroup )
						$unregisterGroups[]	= $registeredGroup->newsletterGroupId;
				}

				if( !$this->request->has( 'disable' ) && !$unregisterGroups ){
					$this->messenger->noteNotice( 'Keine Änderungen vorgenommen.' );
					$this->restart( 'unregister', TRUE );
				}
				foreach( $unregisterGroups as $unregisterGroupId ){
					foreach( $reader->groups as $registeredGroup ){
						if( $unregisterGroupId == $registeredGroup->newsletterGroupId ){
							$this->logic->removeReaderFromGroup( $reader->newsletterReaderId, $unregisterGroupId );
							$this->messenger->noteSuccess( $words->msgSuccessUngroup, $registeredGroup->title );
						}
					}
				}
				if( $this->request->has( 'disable' ) ){
					$this->logic->editReader( $reader->newsletterReaderId, array( 'status' => '-1' ) );
					$this->messenger->noteSuccess( $words->msgSuccess, $email );
					$this->restart( 'unregister', TRUE );
				}
				$this->restart( 'unregister', TRUE );
			}
			$this->addData( 'groups', $reader->groups );
		}
		$this->addData( 'readerLetterId', $readerLetterId );
		$this->addData( 'data', (object) array( 'email' => $email ) );
		$this->addData( 'reader', $reader );
	}

	public function track( $letterId )
	{
		$pixelGIF	= "R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";
		if( !$this->request->has( 'dry' ) ){
			$referer	= getEnv( 'HTTP_REFERER' );
			error_log( $referer."\n", 3, "ref.log" );
			$this->logic->setReaderLetterStatus(
				$letterId,
				Model_Newsletter_Reader_Letter::STATUS_OPENED
		 	);
		}
		header( "Content-type: image/gif" );
		print base64_decode( $pixelGIF );
		exit;
	}

	public function view( $readerLetterId )
	{
		try{
			$letter		= $this->logic->getReaderLetter( $readerLetterId );
			$helper		= new View_Helper_Newsletter_Mail( $this->env );;
			$helper->setMode( View_Helper_Newsletter_Mail::MODE_HTML );
			$helper->setReaderLetterId( $readerLetterId );
			$helper->setReaderId( $letter->newsletterReaderId );
//			$helper->setNewsletterId( $letter->newsletterId );
			$helper->setData( array(
				'readerLetterId'	=> $readerLetterId,
				'newsletterId'		=> $letter->newsletterId,
				'preview'			=> $this->request->has( 'dry' ),
			) );
			print( $helper->render() );
			exit;
		}
		catch( Exception $e ){
			UI_HTML_Exception_Page::display( $e );
			die;
			$this->messenger->noteError( 'Der gewählte Newsletter existiert nicht mehr. Weiterleitung zur Übersicht.' );
			$this->restart( NULL, TRUE );
		}
	}

	//  --  PROTECTED  --  //

	protected function __onInit()
	{
		$this->logic		= new Logic_Newsletter( $this->env );
		$this->session		= $this->env->getSession();
		$this->request		= $this->env->getRequest();
		$this->messenger	= $this->env->getMessenger();

		$hostReferer	= parse_url( getEnv( 'HTTP_REFERER' ), PHP_URL_HOST );
		$hostSelf		= parse_url( $this->env->getConfig()->get( 'app.base.url' ), PHP_URL_HOST );

		if( $hostReferer && $hostReferer !== $hostSelf )
			$this->env->getPage()->addBodyClass( 'iframed' );
	}
}
