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
	 *	@throws		ReflectionException
	 *	@throws		DateMalformedIntervalStringException
	 *	@throws		DateInvalidOperationException
	 *	@todo		refactor for scalability: read mail ids first and mail objects in loop
	 */
	public function clean(): void
	{
		$logicMail	= Logic_Mail::getInstance( $this->env );
		$modelMail	= new Model_Mail( $this->env );
		$threshold	= $this->getAgeThreshold( '--age', '1Y' );
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
	 */
	public function count(): void
	{
		$total		= 0;
		$conditions	= ['status' => [
			Model_Newsletter_Queue::STATUS_NEW,
		]];
		$queues		= $this->logic->getQueues( $conditions );
		foreach( $queues as $queue ){
			$conditions	= [
				'status'			=> [Model_Newsletter_Reader_Letter::STATUS_NEW],
				'newsletterQueueId'	=> $queue->newsletterQueueId,
			];
			$letters	= $this->logic->getReaderLetters( $conditions );							//  get letters to send
			$total		+= count( $letters );
		}
		$this->out( sprintf( '%d mails in newsletter %d queues.', $total, count( $queues ) ) );
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	public function migrate(): void
	{
		if( $this->verbose ){
			$this->out( '' );
			$this->out( 'Migration::recoverReaderLetterQueueIds' );
		}
		$results	= $this->recoverReaderLetterQueueIds();
		if( $this->verbose && ( 1 || $results->letters ) )
			$this->out( vsprintf( "Migrated %d letters into %d queues.", [
				$results->letters,
				$results->queues
			] ) );

		if( $this->verbose ){
			$this->out( '' );
			$this->out( 'Migration::recoverReaderLetterMailIds' );
		}
		$results	= $this->recoverReaderLetterMailIds();
		if( $this->verbose && ( 1 || $results->newsletters ) )
			$this->out( vsprintf( 'Scanned %d newsletters, found %d reader letters and recovered %d mail ID.', [
				$results->newsletters,
				$results->letters,
				$results->recovered,
			] ) );
	}

	/**
	 *	Supports verbose mode.
	 *	Does support dry mode.
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function send(): void
	{
		if( $this->dryMode )
			$this->out( 'DRY RUN - no changes will be made.' );

		$number		= 0;																			//  prepare counter
		$resultData	= [];
		$start		= microtime( TRUE );
		$sleep		= abs( (float) $this->options->get( 'sleepBetweenMails' ) );				//  get seconds to sleep after each mail

		$logicMail		= Logic_Mail::getInstance( $this->env );
		$modelLetter	= new Model_Newsletter_Reader_Letter( $this->env );
		$modelReader	= new Model_Newsletter_Reader( $this->env );
		$modelQueue		= new Model_Newsletter_Queue( $this->env );

		/** @var ?Entity_Newsletter_Queue $queue */
		$queue	= $modelQueue->getByIndices(
			['status' => Model_Newsletter_Queue::STATUS_NEW],
			['toBeSent' => 'ASC'],
		);
		if( NULL !== $queue ){
			$resultData['queue']	= $queue->newsletterQueueId;
			$this->logic->setQueueStatus( $queue->newsletterQueueId, Model_Newsletter_Queue::STATUS_RUNNING );

			$letterIds	= $modelLetter->getAllByIndices( [
				'status'			=> [Model_Newsletter_Reader_Letter::STATUS_NEW],
				'newsletterQueueId'	=> $queue->newsletterQueueId,
			], [], [], ['newsletterReaderLetterId'] );

			foreach( $letterIds as $letterId ){													//  iterate letters
				/** @var Entity_Newsletter_Reader_Letter $letter */
				$letter		= $modelLetter->get( $letterId );
				/** @var Entity_Newsletter_Reader $reader */
				$reader		= $modelReader->get( $letter->newsletterReaderId );
				if( $reader->status < Model_Newsletter_Reader::STATUS_CONFIRMED )				//  reader has been disabled meanwhile
					continue;

				$language	= $this->env->getLanguage()->getLanguage();
				$mail		= new Mail_Newsletter( $this->env, ['readerLetter' => $letter], $language );
				$mail->setToBeSentAt( $queue->toBeSentAt );
				$logicMail->appendRegisteredAttachments( $mail, $language );
				if( $this->verbose )
					$this->out( sprintf( 'Sending mail to %s ...', $reader->email ) );

				if( !$this->dryMode ){
					$this->logic->sendReaderLetterMail(
						$letter,
						$mail,
						$language,
						$reader
					);
				}
				$number++;																		//  increase counter for round limit
				unset( $letter, $reader	);
				if( $sleep )																	//  sleep time is defined
					usleep( $sleep * pow( 10, 6 ) );					//  sleep n seconds
			}

			if( !$this->dryMode ){
				$this->logic->editNewsletter( $queue->newsletterId, [
					'status'	=> Model_Newsletter::STATUS_SENT,
					'sentAt'	=> time(),
				] );
				$this->logic->setQueueStatus( $queue, Model_Newsletter_Queue::STATUS_DONE );
			}
			$newsletter	= $this->logic->getNewsletter( $queue->newsletterId );
			$this->log( sprintf( 'Newsletter %s is done.', $newsletter->title ) );
		}

		$resultStatus	= 0 !== $number ? Model_Job_Run::STATUS_SUCCESS : Model_Job_Run::STATUS_DONE;

		$microtime	= microtime( TRUE ) - $start;
		$time		= round( $microtime, 3 ) * 1000;
		$this->log( sprintf( 'sent %d mails in %d ms', $number, $time ) );

		$ratio	= '';
		if( 0 !== $number && 0 !== $microtime )
			$ratio	= round( $number / $microtime, 1 ).' mails/second';

		$resultData['nrDone']		= $number;
		$resultData['runtime']		= $time.'ms';
		$resultData['ratio']		= $ratio;
		$this->setResult( $resultStatus, $number, $resultData );
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
	 *	@return		object
	 *	@throws		ReflectionException
	 */
	protected function recoverReaderLetterMailIds(): object
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
			if( $this->verbose ){
				$sign	= $countRecoveredOld != $countRecovered ? '+' : '.';
				$this->showProgress( $nr + 1, count( $entries ), $sign );
			}
		}
		if( $this->verbose && $newsletters )
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
