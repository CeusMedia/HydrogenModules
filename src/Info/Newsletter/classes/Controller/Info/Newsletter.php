<?php

use CeusMedia\Common\Net\HTTP\PartitionSession;
use CeusMedia\Common\Net\HTTP\Request;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment;

class Controller_Info_Newsletter extends Controller
{
	/**	@var	Logic_Newsletter	$logic */
	protected Logic_Newsletter $logic;
	protected Environment\Resource\Messenger $messenger;
	protected Request $request;
	protected PartitionSession $session;

	/**
	 *	@param		int|string		$readerId
	 *	@param		?string			$key
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function confirm( int|string $readerId, ?string $key = NULL ): void
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
			$this->logic->editReader( $readerId, ['status' => 1] );
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
		$this->addData( 'groups', $this->logic->getGroups( ['type' => array[0, 2], 'status' => '> 0'] ) );
		$this->addData( 'subscriptions', $this->logic->getGroupsOfReader( $readerId ) );
		$this->addData( 'reader', $this->logic->getReader( $readerId ) );
		$this->addData( 'letters', $this->logic->getLettersOfReader( $readerId ) );
		$this->addData( 'letter', $this->logic->getReaderLetter( $letterId ) );
		$this->addData( 'key', $key );
		$this->addData( 'letterId', $letterId );
		$this->addData( 'readerId', $readerId );
	}*/

	/**
	 *	@param		$arg1
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function index( $arg1 = NULL ): void
	{
		$words		= (object) $this->getWords( 'index' );
		if( $this->request->has( 'save' ) ){
			$language			= $this->env->getLanguage()->getLanguage();
			$readersFoundByMail	= $this->logic->getReaders( [
				'email'		=> trim( $this->request->get( 'email' ) ),
				'status'	=> [0, 1],
			] );
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
				$conditions	= [																	//  get active automatic groups
					'status'	=> Model_Newsletter_Group::STATUS_USABLE,						//  status: active
					'type'		=> Model_Newsletter_Group::TYPE_AUTOMATIC,
				];
				foreach( $this->logic->getGroups( $conditions ) as $group )						//  iterate found groups
					$this->logic->addReaderToGroup( $readerId, $group->newsletterGroupId );		//  add reader to group

				$data	= [
					'readerId'		=> $readerId,
					'reader'		=> $reader,
				];
				$mail	= new Mail_Info_Newsletter_Register( $this->env, $data );
				$logicMail	= Logic_Mail::getInstance( $this->env );
				$logicMail->appendRegisteredAttachments( $mail, $language );
				$receiver	= (object) [
					'username'	=> $reader->firstname.' '.$reader->surname,
					'email'		=> $reader->email,
				];
				$logicMail->handleMail( $mail, $receiver, $language );
				$this->messenger->noteSuccess( $words->msgSuccess, $reader->email );
				$this->restart( NULL, TRUE );
			}
		}
		$this->addData( 'data', $this->request->getAll( '', TRUE ) );

		$requestedGroups	= $this->request->get( 'groups' );
		$requestedGroups	= is_array( $requestedGroups ) ? $requestedGroups : [];
		$groups	= $this->logic->getGroups( [
			'status'	=> Model_Newsletter_Group::STATUS_USABLE,								//  status: active
			'type'		=> [																	//  type: default or automatic
				Model_Newsletter_Group::TYPE_DEFAULT,
				Model_Newsletter_Group::TYPE_AUTOMATIC,
			],
		], ['title' => 'ASC'] );
		foreach( $groups as $group )
			$group->isChecked	= in_array( $group->newsletterGroupId, $requestedGroups );
		$this->addData( 'groups', $groups );

		$conditions		= ['status' => Model_Newsletter::STATUS_SENT];
		$orders			= ['newsletterId' => 'DESC'];
		$newsletters	= $this->logic->getNewsletters( $conditions, $orders );
		$latest			= $newsletters ? array_shift( $newsletters ) : NULL;
		$this->addData( 'canShowLatest', $newsletters );
		$this->addData( 'latest', $latest );
	}

	/**
	 *	@param		int|string|NULL		$newsletterId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function preview( int|string|NULL $newsletterId = NULL ): void
	{
		if( !$newsletterId ){
			$newsletters	= $this->logic->getNewsletters( ['status' => '>= 1'], ['newsletterId' => 'DESC'] );
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

	/**
	 *	@param		?string				$emailHash
	 *	@param		int|string|NULL		$readerLetterId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function unregister( ?string $emailHash = NULL, int|string|NULL $readerLetterId = NULL ): void
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
			$reader->groups	= $this->logic->getGroupsOfReader( $reader->newsletterReaderId, [], ['title' => 'ASC'] );
			if( $this->request->has( 'save' ) ){
				$mode	= $this->request->get( 'mode' );
				if( $this->request->has( 'disable' ) )
					$mode	= 'all';

				$unregisterGroups	= $this->request->get( 'groupIds' );
				if( $mode === "all" ){
					$unregisterGroups	= [];
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
					$this->logic->editReader( $reader->newsletterReaderId, ['status' => '-1'] );
					$this->messenger->noteSuccess( $words->msgSuccess, $email );
					$this->restart( 'unregister', TRUE );
				}
				$this->restart( 'unregister', TRUE );
			}
			$this->addData( 'groups', $reader->groups );
		}
		$this->addData( 'readerLetterId', $readerLetterId );
		$this->addData( 'data', (object) ['email' => $email] );
		$this->addData( 'reader', $reader );
	}

	/**
	 *	@param		int|string		$letterId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function track( int|string $letterId ): void
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

	/**
	 *	@param		int|string		$readerLetterId
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function view( int|string $readerLetterId ): void
	{
		try{
			$letter		= $this->logic->getReaderLetter( $readerLetterId );
			$helper		= new View_Helper_Newsletter_Mail( $this->env );
			$helper->setMode( View_Helper_Newsletter_Mail::MODE_HTML );
			$helper->setReaderLetterId( $readerLetterId );
			$helper->setReaderId( $letter->newsletterReaderId );
//			$helper->setNewsletterId( $letter->newsletterId );
			$helper->setData( [
				'readerLetterId'	=> $readerLetterId,
				'newsletterId'		=> $letter->newsletterId,
				'preview'			=> $this->request->has( 'dry' ),
			] );
			print( $helper->render() );
			exit;
		}
		catch( Exception $e ){
			HtmlExceptionPage::display( $e );
			die;
			$this->messenger->noteError( 'Der gewählte Newsletter existiert nicht mehr. Weiterleitung zur Übersicht.' );
			$this->restart( NULL, TRUE );
		}
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
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
