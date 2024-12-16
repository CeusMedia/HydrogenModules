<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Environment\Resource\Language;

class Job_Newsletter extends Job_Abstract
{
	protected Dictionary $config;
	protected Logic_Newsletter $logic;
	protected Language $language;
	protected Dictionary $options;
	protected object $words;

	/**
	 * @throws ReflectionException
	 * @throws DateMalformedIntervalStringException
	 * @throws DateInvalidOperationException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 * @todo refactor for scalability: read mail ids first and mail objects in loop
	 */
	public function clean(): void
	{
		$logicMail	= Logic_Mail::getInstance( $this->env );
		$modelMail	= new Model_Mail( $this->env );
		$age		= $this->parameters->get( '--age', '1Y' ) ;
		$threshold	= date_create()->sub( new DateInterval( 'P'.$age ) );
		$conditions	= [
			'status'		=> [
				Model_Mail::STATUS_ABORTED,														//  status: -3
				Model_Mail::STATUS_FAILED,														//  status: -2
				Model_Mail::STATUS_SENT,														//  status: 2
				Model_Mail::STATUS_RECEIVED,													//  status: 3
				Model_Mail::STATUS_OPENED,														//  status: 4
				Model_Mail::STATUS_REPLIED,														//  status: 5
			],
			'mailClass'		=> 'Mail_Newsletter',
			'enqueuedAt' 	=> '< '.$threshold->format( 'U' ),
		];
		$orders		= ['mailId' => 'ASC'];
		$limits		= [];
		/** @var array<Entity_Mail> $mails */
		$mails		= $modelMail->getAll( $conditions, $orders, $limits );
		if( $this->dryMode ){
			$this->out( 'DRY RUN - no changes will be made.' );
			$this->out( 'Would remove '.count( $mails ).' old newsletter mails.' );
		}
		else{
			$count		= 0;
//			$fails		= [];
			foreach( $mails as $mail ){
				$logicMail->removeMail( $mail->mailId );
				$this->showProgress( ++$count, count( $mails ) );
			}
			if( $mails )
				$this->out();
			$this->out( 'Removed '.$count.' newsletter mails.' );
//			$this->showErrors( 'removeNewsletters', $fails );
		}
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function count(): void
	{
		$words		= (object) $this->words->send;													//  get words or like date formats

		$total		= 0;
		$conditions	= ['status' => [
			Model_Newsletter_Queue::STATUS_NEW,
			Model_Newsletter_Queue::STATUS_RUNNING
		]];
		$queues		= $this->logic->getQueues( $conditions );
		foreach( $queues as $queue ){
			$conditions	= [
				'status'			=> [Model_Newsletter_Reader_Letter::STATUS_ENQUEUED],
				'newsletterQueueId'	=> $queue->newsletterQueueId,
			];
			$letters	= $this->logic->getReaderLetters( $conditions );							//  get letters to send
			$total		+= count( $letters );
		}
		$this->out( sprintf( '%d mails in newsletter %d queues.', $total, count( $queues ) ) );
	}

	/**
	 *	@param		bool		$verbose
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function migrate( bool $verbose = FALSE ): void
	{
		if( $verbose ){
			$this->out( '' );
			$this->out( 'Migration::recoverReaderLetterQueueIds' );
		}
		$results	= $this->recoverReaderLetterQueueIds();
		if( $verbose && ( 1 || $results->letters ) )
			$this->out( vsprintf( "Migrated %d letters into %d queues.", [
				$results->letters,
				$results->queues
			] ) );

		if( $verbose ){
			$this->out( '' );
			$this->out( 'Migration::recoverReaderLetterMailIds' );
		}
		$results	= $this->recoverReaderLetterMailIds( $verbose );
		if( $verbose && ( 1 || $results->newsletters ) )
			$this->out( vsprintf( 'Scanned %d newsletters, found %d reader letters and recovered %d mail ID.', [
				$results->newsletters,
				$results->letters,
				$results->recovered,
			] ) );
	}

	/**
	 *	@param		bool		$verbose
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function send( bool $verbose = FALSE ): void
	{
		$words		= (object) $this->words->send;												//  get words or like date formats
		$max		= abs( (int) $this->options->get( 'mailsPerRun' ) );						//  get max number of mails to send in one round
		$sleep		= abs( (float) $this->options->get( 'sleepBetweenMails' ) );				//  get seconds to sleep after each mail
		$logicMail	= Logic_Mail::getInstance( $this->env );
		$conditions	= ['status' => [
			Model_Newsletter_Queue::STATUS_NEW,
			Model_Newsletter_Queue::STATUS_RUNNING
		]];
		$queues		= $this->logic->getQueues( $conditions );
		$queueIds	= [];
		foreach( $queues as $queue ){
			$queueIds[]	= $queue->newsletterQueueId;
			if( $queue->status == Model_Newsletter_Queue::STATUS_NEW )
				$this->logic->setQueueStatus( $queue->newsletterQueueId, 1 );
		}
		if( !$queueIds )
			return;
		$conditions	= [
			'status'			=> [Model_Newsletter_Reader_Letter::STATUS_ENQUEUED],
			'newsletterQueueId'	=> $queueIds,
		];
		$number		= 0;																		//  prepare counter for round limit
		$orders		= [];																	//  no order
		$limits		= [0, $max];															//  limit letters
		$letters	= $this->logic->getReaderLetters( $conditions, $orders, $limits );			//  get letters to send
		$start		= microtime( TRUE );
		if( $letters ){
			while( $letters && ( $max == 0 || $number < $max ) ){								//  iterate letters
				if( $number && $sleep )															//  sleep time is defined and not first mail
					usleep( $sleep * pow( 10, 6 ) );											//  sleep n seconds
				$letter		= array_shift( $letters );											//  get next letter
				$reader		= $letter->reader;													//  shortcut letter reader
				$mail		= new Mail_Newsletter( $this->env, [
					'readerLetterId'	=> $letter->newsletterReaderLetterId,
				] );
				$language	= $this->env->getLanguage()->getLanguage();
				$receiver	= $this->logic->getReader( $letter->newsletterReaderId );
				$logicMail->appendRegisteredAttachments( $mail, $language );
				if( $verbose )
					$this->out( sprintf( 'Sending mail to %s ...', $letter->reader->email ) );

//				$mailId	= $logicMail->handleMail( $mail, $receiver, $language );
//				if( is_int( $mailId ) )
//					$this->logic->setReaderLetterMailId( $letter->newsletterReaderLetterId, $mailId );

				$mailId	= $logicMail->enqueueMail( $mail, $language, $receiver );
				$this->logic->setReaderLetterMailId( $letter->newsletterReaderLetterId, $mailId );

				$this->logic->setReaderLetterStatus(
					$letter->newsletterReaderLetterId,
					Model_Newsletter_Reader_Letter::STATUS_SENT
				);
				$number++;																		//  increase counter for round limit
			}
		}
		$time	= round( microtime( TRUE ) - $start, 3 ) * 1000;
		$this->log( sprintf( 'sent %d mails in %d ms', $number, $time ) );
		foreach( $queues as $queue ){
			$conditions	= [
				'status'			=> [Model_Newsletter_Reader_Letter::STATUS_ENQUEUED],
				'newsletterQueueId'	=> $queue->newsletterQueueId,
			];
			if( !count( $this->logic->getReaderLetters( $conditions ) ) ){
				$newsletter	= $this->logic->getNewsletter( $queue->newsletterId );
				$this->log( sprintf( 'Newsletter %s is done.', $newsletter->title ) );
				$this->logic->editNewsletter( $queue->newsletterId, [
					'status'	=> Model_Newsletter::STATUS_SENT
				] );
				$this->logic->setQueueStatus(
					$queue->newsletterQueueId,
					Model_Newsletter_Queue::STATUS_DONE
				);
			//	@todo		send mail
			}
		}
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();												//  get app config
		$this->logic		= new Logic_Newsletter( $this->env );									//  get module logic
		$this->language		= $this->env->getLanguage();											//  get language support
		$this->options		= $this->config->getAll( 'module.info_newsletter.send.', TRUE );		//  get module options for job
		$this->words		= (object) $this->language->getWords( 'info/newsletter' );				//  get module words
	}

	/**
	 *	@param		bool		$verbose
	 *	@return		object
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function recoverReaderLetterMailIds( bool $verbose = FALSE ): object
	{
		$modelMail		= new Model_Mail( $this->env );
		$countLetters	= 0;
		$countRecovered	= 0;
		$readers		= [];
		$entries		= [];
		$conditions		= ['status' => Model_Newsletter::STATUS_SENT];
		$orders			= ['newsletterId' => 'ASC'];
		$newsletters	= $this->logic->getNewsletters( $conditions, $orders );
		foreach( $newsletters as $newsletter ){
			$letters	= $this->logic->getReaderLetters( [
				'newsletterId'	=> $newsletter->newsletterId,
				'mailId'		=> '0',
			] );
			if( !$letters )
				continue;
			$entries[$newsletter->newsletterId]	= (object) [
				'newsletter'	=> $newsletter,
				'readerLetters'	=> [],
			];
			$countLetters	+= count( $letters );
			foreach( $letters as $letter ){
				if( !isset( $readers[$letter->newsletterReaderId] ) ){
					$reader	= $this->logic->getReader( $letter->newsletterReaderId );
					$readers[$letter->newsletterReaderId]	= $reader;
				}
				$entries[$newsletter->newsletterId]->readerLetters[]	= $letter;
			}
		}
		foreach( array_values( $entries ) as $nr => $entry ){
			$countRecoveredOld	= $countRecovered;
			foreach( $entry->readerLetters as $letter ){
				$mailId	= $modelMail->getByIndices( [
					'mailClass'			=> 'Mail_Newsletter',
					'receiverAddress'	=> $readers[$letter->newsletterReaderId]->email,
					'subject'			=> $entry->newsletter->subject,
				], [], ['mailId'] );
				if( $mailId ){
					$this->logic->setReaderLetterMailId( $letter->newsletterReaderLetterId, $mailId );
					$countRecovered	+= 1;
				}
			}
			if( $verbose ){
				$sign	= $countRecoveredOld != $countRecovered ? '+' : '.';
				$this->showProgress( $nr + 1, count( $entries ), $sign );
			}
		}
		if( $verbose && $newsletters )
			$this->out();
		return (object) [
			'newsletters'	=> count( $entries ),
			'letters'		=> $countLetters,
			'recovered'		=> $countRecovered,
		];
	}

	/**
	 *	@return		object
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function recoverReaderLetterQueueIds(): object
	{
		$modelQueue		= new Model_Newsletter_Queue( $this->env );
		$modelLetter	= new Model_Newsletter_Reader_Letter( $this->env );

		$conditions	= ['newsletterQueueId' => 0];
		$orders		= ['newsletterId'	=> 'ASC', 'newsletterReaderLetterId' => 'ASC'];
		$letters	= $modelLetter->getAll( $conditions, $orders );
		$newsletterIds	= [];
		foreach( $letters as $letter ){
			if( !array_key_exists( $letter->newsletterId, $newsletterIds ) ){
				$newsletterIds[$letter->newsletterId]	= [
					'newsletterId'	=> $letter->newsletterId,
					'creatorId'		=> 0,
					'status'		=> Model_Newsletter_Queue::STATUS_DONE,
					'createdAt'		=> $letter->enqueuedAt,
					'modifiedAt'	=> $letter->enqueuedAt,
				];
			}
		}
		foreach( $newsletterIds as $newsletterId => $queueData ){
			$conditions	= [
				'newsletterQueueId' => 0,
				'newsletterId'		=> $newsletterId,
			];
			$letters	= $modelLetter->getAll( $conditions, $orders );
			$queueId	= $modelQueue->add( $queueData );
			foreach( $letters as $letter ){
				$modelLetter->edit( $letter->newsletterReaderLetterId, [
					'newsletterQueueId'	=> $queueId,
				] );
			}
		}
		return (object) [
			'queues'		=> count( $newsletterIds ),
			'letters'		=> count( $letters ),
		];
	}
}
