<?php

//use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Work_Mission_Update extends Mail_Work_Mission_Change
{
	protected ?string $languageSection	= 'mail-update';
	protected ?View_Helper_Mail_Facts $helperFacts;

	//  --  PROTECTED  --  //

	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		parent::generate();
		$this->setSubjectFromMission( $this->data['missionBefore'] );
		$this->prepareFacts( $this->data );
		$this->addBodyClass( 'job-work-mission-mail-update' );
		$this->setHtml( $this->renderHtmlMailBody() );
		$this->setText( $this->renderTextMailBody() );
		return $this;
	}

	/**
	 *	@param		object		$old
	 *	@param		object		$new
	 *	@return		void
	 */
	protected function handleChangedEndDay( object $old, object $new ): void
	{
		if( $old->dayEnd && $new->dayEnd ){
			$dateOld		= date( "d.m.Y", strtotime( $old->dayEnd ) );
			$weekdayOld		= $this->labelsWeekdays[date( 'N', strtotime( $old->dayEnd ) ) % 7];
			$labelKey		= $new->type ? 'dayEnd' : 'dayDue';
			$dateEndHtml	= $weekdayOld.',&nbsp;'.$dateOld;
			$dateEndText	= $weekdayOld.', '.$dateOld;
			$diffHtml		= $diffText		= '';
			if( $old->dayEnd !== $new->dayEnd ){
				$days			= round( ( strtotime( $new->dayEnd ) - strtotime( $old->dayEnd ) ) / 3600 / 24 );
				[$diffHtml, $diffText]	= $this->renderDayChangeDiffs( $days );
				$dateNew		= date( "d.m.Y", strtotime( $new->dayEnd ) );
				$weekdayNew		= $this->labelsWeekdays[date( 'N', strtotime( $new->dayEnd ) ) % 7];
				$dateEndHtml	.= ' &rarr; '.$weekdayNew.',&nbsp;'.$dateNew;
				$dateEndText	.= ' -> '.$weekdayNew.', '.$dateNew;
			}
			$this->helperFacts->add( $labelKey, $dateEndHtml.$diffHtml, $dateEndText.$diffText );
		}
	}

	/**
	 *	@param		object		$old
	 *	@param		object		$new
	 *	@return		void
	 */
	protected function handleChangedEndTime( object $old, object $new ): void
	{
		$diffHtml		= '';
		$diffText		= '';
		if( $old->type == 1 ){
			$timeEndHtml	= date( 'H:i', strtotime( $old->dayEnd.' '.$old->timeEnd ) );
			$timeEndText	= date( 'H:i', strtotime( $old->dayEnd.' '.$old->timeEnd ) );
			if( $old->timeEnd != $new->timeEnd ){
				$timeEndHtml	.= ' &rarr; '.date( 'H:i', strtotime( $new->dayEnd.' '.$new->timeEnd ) );
				$timeEndHtml	= $this->renderLabel( $timeEndHtml, $old->timeEnd > $new->timeEnd ? 'success' : 'important' );
				$timeEndText	.= ' -> '.date( 'H:i', strtotime( $new->dayEnd.' '.$new->timeEnd ) );
			}
		}
		else{
			$timeEndText	= date( 'H:i', strtotime( $new->dayEnd.' '.$new->timeEnd ) );
			$timeEndHtml	= $this->renderLabel( $timeEndText, 'info' );
		}
		$this->helperFacts->add( 'timeEnd', $timeEndHtml.$diffHtml, $timeEndText.$diffText );
	}

	/**
	 *	@param		object		$old
	 *	@param		object		$new
	 *	@return		void
	 */
	protected function handleChangedLocation( object $old, object $new ): void
	{
		if( $old->location || $new->location ){
			$locationHtml	= strlen( trim( $old->location ) ) ? trim( $old->location ).' ' : '';
			$locationText	= strlen( trim( $old->location ) ) ? trim( $old->location ).' ' : '';
			if( $old->location !== $new->location ){
				$locationHtml	= $this->renderLabel( $old->location.' &rarr; '.$new->location, 'info' );
				$locationText	.= '-> '.$new->location;
			}
			$this->helperFacts->add( 'location', $locationHtml, $locationText );
		}
	}

	/**
	 *	@param		object		$old
	 *	@param		object		$new
	 *	@return		void
	 */
	protected function handleChangedPriority( object $old, object $new ): void
	{
		$priorityHtml	= $this->labelsPriorities[$old->priority];
		$priorityText	= $this->labelsPriorities[$old->priority];
		if( (int) $old->priority !== (int) $new->priority ){
			$labelClass		= $old->priority < $new->priority ? 'success' : 'important';
			$priorityHtml	= $this->renderLabel( $priorityHtml.' &rarr; '.$this->labelsPriorities[$new->priority], $labelClass );
			$priorityText	= $priorityText.' -> '.$this->labelsPriorities[$new->priority];
		}
		$this->helperFacts->add( 'priority', $priorityHtml, $priorityText );
	}

	/**
	 *	@param		object		$old
	 *	@param		object		$new
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function handleChangedProject( object $old, object $new ): void
	{
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			/** @var Logic_Project $logicProject */
			$logicProject	= Logic_Project::getInstance( $this->env );
			$projectOld		= $old->projectId ? $logicProject->getProject( $old->projectId ) : '-';
			if( $projectOld ){
				$linkProjectOld	= HtmlTag::create( 'a', $projectOld->title, ['href' => './manage/project/view/'.$projectOld->projectId] );
				$projectHtml	= $linkProjectOld;
				$projectText	= $projectOld->title;
				if( $new->projectId && $old->projectId !== $new->projectId ){
					$projectNew		= $logicProject->getProject( $new->projectId );
					$linkProjectNew	= HtmlTag::create( 'a', $projectNew->title, ['href' => './manage/project/view/'.$projectNew->projectId] );
					$projectHtml	.= '<br/>&rarr; '.$linkProjectNew;
					$projectText	= $projectText.PHP_EOL.'-> '.$projectNew->title;
				}
				$this->helperFacts->add( 'projectId', $projectHtml, $projectText );
			}
		}
	}

	protected function handleChangedStartDay( object $old, object $new ): void
	{
		if( $old->dayStart && $new->dayStart ){
			$dateOld		= date( "d.m.Y", strtotime( $old->dayStart ) );
			$weekdayOld		= $this->labelsWeekdays[date( 'N', strtotime( $old->dayStart ) ) % 7];
			$labelKey		= $new->type ? 'dayStart' : 'dayWork';
			$dateStartHtml	= $weekdayOld.',&nbsp;'.$dateOld;
			$dateStartText	= $weekdayOld.', '.$dateOld;
			$diffHtml		= $diffText		= '';
			if( $old->dayStart !== $new->dayStart ){
				$days			= round( ( strtotime( $new->dayStart ) - strtotime( $old->dayStart ) ) / 3600 / 24 );
				[$diffHtml, $diffText]	= $this->renderDayChangeDiffs( $days );

				$dateNew		= date( "d.m.Y", strtotime( $new->dayStart ) );
				$weekdayNew		= $this->labelsWeekdays[date( 'N', strtotime( $new->dayStart ) ) % 7];
				$dateStartHtml	.= ' &rarr; '.$weekdayNew.',&nbsp;'.$dateNew;
				$dateStartText	.= ' -> '.$weekdayNew.', '.$dateNew;
			}
			$this->helperFacts->add( $labelKey, $dateStartHtml.$diffHtml, $dateStartText.$diffText );
		}
	}

	protected function handleChangedStartTime( object $old, object $new ): void
	{
		$diffHtml		= '';
		$diffText		= '';
		if( Model_Mission::TYPE_EVENT == $old->type ){
			$timeStartHtml	= date( 'H:i', strtotime( $old->dayStart.' '.$old->timeStart ) );
			$timeStartText	= date( 'H:i', strtotime( $old->dayStart.' '.$old->timeStart ) );
			if( $old->timeStart != $new->timeStart ){
				$timeStartHtml	.= ' &rarr; '.date( 'H:i', strtotime( $new->dayStart.' '.$new->timeStart ) );
				$timeStartHtml	= $this->renderLabel( $timeStartHtml, $old->timeStart < $new->timeStart ? 'success' : 'important' );
				$timeStartText	.= ' -> '.date( 'H:i', strtotime( $new->dayStart.' '.$new->timeStart ) );
			}
		}
		else{
			$timeStartText	= date( 'H:i', strtotime( $new->dayStart.' '.$new->timeStart ) );
			$timeStartHtml	= $this->renderLabel( $timeStartText, 'info' );
		}
		$this->helperFacts->add( 'timeStart', $timeStartHtml.$diffHtml, $timeStartText.$diffText );
	}

	protected function handleChangedStatus( object $old, object $new ): void
	{
		$statusHtml		= $this->labelsStates[$old->status];
		$statusText		= $this->labelsStates[$old->status];
		if( (int) $old->status !== (int) $new->status ){
			$labelClass		= $old->status < $new->status ? 'success' : 'important';
			$statusHtml		= $this->renderLabel( $statusHtml.' &rarr; '.$this->labelsStates[$new->status], $labelClass );
			$statusText		= $statusText.' -> '.$this->labelsStates[$new->status];
		}
		$this->helperFacts->add( 'status', $statusHtml, $statusText );
	}

	protected function handleChangedTitle( object $old, object $new ): void
	{
		$titleHtml		= $this->renderLinkedTitle( $old );
		$titleText		= $old->title;
		if( $old->title !== $new->title ){
			$titleHtml	= $titleHtml.'<br/>&rarr; '.$this->renderLinkedTitle( $new );
			$titleText	= $titleText.PHP_EOL.'-> '.$new->title;
		}
		$this->helperFacts->add( 'title', $titleHtml, $titleText );
	}

	protected function handleChangedType( object $old, object $new ): void
	{
		$typeHtml	= $this->labelsTypes[$old->type];
		$typeText	= $this->labelsTypes[$old->type];
		if( $old->type !== $new->type ){
			$typeHtml	= $this->renderLabel( $typeHtml.' &rarr; '.$this->labelsTypes[$new->type], 'info' );
			$typeText	= $typeText.' -> '.$this->labelsTypes[$new->type];
		}
		$this->helperFacts->add( 'type', $typeHtml, $typeText );
	}

	/**
	 *	@param		object		$old
	 *	@param		object		$new
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function handleChangedWorker( object $old, object $new ): void
	{
		if( $old->workerId ){
			/** @var ?Entity_User $workerOld */
			$workerOld	= $this->modelUser->get( $old->workerId );
			$workerHtml	= $this->renderUser( $workerOld, TRUE );
			$workerText	= $this->renderUserAsText( $workerOld );
			if( $new->workerId && (int) $old->workerId !== (int) $new->workerId ){
				/** @var ?Entity_User $workerNew */
				$workerNew	= $this->modelUser->get( $new->workerId );
				$workerHtml	= $workerHtml.' &rarr; '.$this->renderUser( $workerNew, TRUE );
				$workerText	= $workerText.' -> '.$this->renderUserAsText( $workerNew );
			}
			$this->helperFacts->add( 'worker', $workerHtml, $workerText );
		}
	}

	/**
	 *	@param		array		$data
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function prepareFacts( array $data ): void
	{
		$old		= $data['missionBefore'];
		$new		= $data['missionAfter'];

		$this->helperFacts	= new View_Helper_Mail_Facts();
		$this->helperFacts->setLabels( $this->labels );
		$this->helperFacts->setTextLabelLength( 13 );

		$this->handleChangedType( $old, $new );
		$this->handleChangedProject( $old, $new );
		$this->handleChangedTitle( $old, $new );
		$this->handleChangedStatus( $old, $new );
		$this->handleChangedPriority( $old, $new );
		$this->handleChangedWorker( $old, $new );
		$this->handleChangedStartDay( $old, $new );
		$this->handleChangedEndDay( $old, $new );
		if( Model_Mission::TYPE_EVENT == $new->type ){
			$this->handleChangedStartTime( $old, $new );
			$this->handleChangedEndTime( $old, $new );
		}
		$this->handleChangedLocation( $old, $new );
	}

	protected function renderDayChangeDiffs( int|float $days ): array
	{
		$signHtml		= $days > 0 ? '&plus;' : '&minus;';
		$signText		= $days > 0 ? '+' : '-';
		$diffHtml		= ' '.$this->renderLabel( $signHtml.abs( round( $days ) ), $days < 0 ? 'important' : 'success' );
		$diffText		= ' ('.$signText.abs( round( $days ) ).')';
		return [$diffHtml, $diffText];
	}

	protected function renderLabel( string $content, ?string $class = NULL ): string
	{
		$class	= 'label'.( $class ? ' label-'.$class : '' );
		return HtmlTag::create( 'span', $content, ['class' => $class] );
	}


	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function renderHtmlMailBody(): string
	{
		$data			= $this->data;
//		$indicator		= new HtmlIndicator();
//		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	 realize date format in module config

		$old			= $data['missionBefore'];
		$new			= $data['missionAfter'];
		$url			= $this->baseUrl.'work/mission/'.$old->missionId;
		$nowWeekday		= $this->labelsWeekdays[date( 'w' )];
		$nowMonth		= $this->labelsMonthNames[date( 'n' )];
		$dateFull		= $nowWeekday.', der '.date( "j" ).'.&nbsp;'.$nowMonth;

		$content		= HtmlTag::create( 'em', $this->words['emptyContent'], ['class' => 'muted'] );
		if( strlen( trim( $new->content ) ) )
			$content	= View_Helper_Markdown::transformStatic( $this->env, $new->content );

		$data	= array_merge( $data, [
			'baseUrl'	=> $this->baseUrl,
			'words'		=> (object) $this->words,
			'values'	=> [
				'type'		=> $this->labelsTypes[$old->type],
				'modifier'	=> $this->renderUser( $this->modelUser->get( $new->modifierId ) ),
				'url'		=> $url,
				'link'		=> HtmlTag::create( 'a', $old->title, ['href' => $url] ),
				'today'		=> [
					'long'	=> HtmlTag::create( 'span', $dateFull, ['class' => 'text-date-full'] ),
					'short'	=> HtmlTag::create( 'span', date( $formatDate ), ['class' => 'text-date-short'] ),
				],
				'content'	=> $content,
			],
			'lists'		=> [
				'facts'		=> $this->helperFacts->render(),
			],
			'texts'		=> [
				'salute'	=> $this->salutes ? $this->salutes[array_rand( $this->salutes )] : '',
			]
		] );
		return $this->loadContentFile( 'mail/work/mission/update.html', $data ) ?? '';
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function renderTextMailBody(): string
	{
		$data			= $this->data;
//		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	 realize date format in module config
		$old			= $data['missionBefore'];
		$new			= $data['missionAfter'];
		/** @var ?Entity_User $modifier */
		$modifier		= $this->modelUser->get( $new->modifierId );
		$nowWeekday		= $this->labelsWeekdays[date( 'w' )];
		$nowMonth		= $this->labelsMonthNames[date( 'n' )];

		$content		= $this->words['emptyContent'];
		if( strlen( trim( $new->content ) ) )
			$content	=  strip_tags( $new->content );

		$data	= array_merge( $data, [
			'baseUrl'	=> $this->baseUrl,
			'words'		=> (object) $this->words,
			'values'	=> [
				'type'		=> $this->labelsTypes[$old->type],
				'modifier'	=> $this->renderUserAsText( $modifier ),
				'link'		=> $this->baseUrl.'work/mission/'.$old->missionId,
				'today'		=> [
					'long'	=> $nowWeekday.', der '.date( "j" ).'.&nbsp;'.$nowMonth,
					'short'	=> date( $formatDate ),
				],
				'content'	=> $content,
			],
			'lists'		=> [
				'facts'		=> $this->helperFacts->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render()
			],
			'texts'		=> [
				'salute'	=> $this->salutes ? $this->salutes[array_rand( $this->salutes )] : '',
			]
		] );
		return $this->loadContentFile( 'mail/work/mission/update.txt', $data ) ?? '';
	}
}
