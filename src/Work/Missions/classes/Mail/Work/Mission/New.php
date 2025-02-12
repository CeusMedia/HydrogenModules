<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Work_Mission_New extends Mail_Work_Mission_Change
{
	protected ?string $languageSection				= 'mail-new';
	protected ?View_Helper_Mail_Facts $helperFacts	= NULL;

	//  --  PROTECTED  --  //

	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		parent::generate();
		$data	= $this->data;
		$this->setSubjectFromMission( $data['mission'] );
		$this->prepareFacts( $data );
		$this->addBodyClass( 'job-work-mission-mail-new' );
		$this->setHtml( $this->renderHtmlMailBody() );
		$this->setText( $this->renderTextMailBody() );
		return $this;
	}

	/**
	 *	@param		array		$data
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function prepareFacts( array $data ): void
	{
		$mission	= $data['mission'];
		$this->helperFacts	= new View_Helper_Mail_Facts();
		$this->helperFacts->setLabels( $this->labels );
		$this->helperFacts->setTextLabelLength( 13 );

		$this->helperFacts->add( 'type', $this->labelsTypes[$mission->type], $this->labelsTypes[$mission->type] );
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$logicProject	= Logic_Project::getInstance( $this->env );
			$project		= $logicProject->getProject( $mission->projectId );
			$link			= HtmlTag::create( 'a', $project->title, ['href' => './manage/project/view/'.$project->projectId] );
			$this->helperFacts->add( 'projectId', $link, $project->title );
		}
		if( (int) $mission->workerId ){
			/** @var ?Entity_User $worker */
			$worker		= $this->modelUser->get( $mission->workerId );
			$this->helperFacts->add( 'worker', $this->renderUser( $worker ), $this->renderUserAsText( $worker ) );
		}
		$this->helperFacts->add( 'status', $this->labelsStates[$mission->status] );
		$this->helperFacts->add( 'priority', $this->labelsPriorities[$mission->priority] );

		$timestampStart	= strtotime( $mission->dayStart.' '.$mission->timeStart );
		$timestampEnd	= strtotime( $mission->dayEnd.' '.$mission->timeEnd );
		$dateStart		= date( 'd.m.Y', $timestampStart );
		$dateEnd		= date( 'd.m.Y', $timestampEnd );
		$timeStart		= date( 'H:i', $timestampStart );
		$timeEnd		= date( 'H:i', $timestampEnd );
		$weekdayStart	= $this->labelsWeekdays[date( 'N', $timestampStart ) % 7];
		$weekdayEnd		= $this->labelsWeekdays[date( 'N', $timestampEnd ) % 7];

		if( $mission->type ){
			if( $weekdayStart == $weekdayEnd ){
				$this->helperFacts->add( 'date', $weekdayStart.', '.$dateStart );
			}
			else{
				$this->helperFacts->add( 'dayStart', $weekdayStart.', '.$dateStart );
				$this->helperFacts->add( 'dayEnd', $weekdayEnd.', '.$dateEnd );
			}
			$timeRange	= $timeStart.' - '.$timeEnd;
			$timeRange	= $this->labels['labelTime_prefix'].$timeRange.$this->labels['labelTime_suffix'];
			$this->helperFacts->add( 'time', $timeRange );
//			$helperFacts->add( 'timeStart', date( 'H:i', $timestampStart ) );
//			$helperFacts->add( 'timeEnd', date( 'H:i', $timestampEnd ) );
		}
		else{
			$this->helperFacts->add( 'dayWork', $weekdayStart.', '.$dateStart );
			$this->helperFacts->add( 'dayDue', $weekdayEnd.', '.$dateEnd );
		}
		if( strlen( trim( $mission->location ) ) )
			$this->helperFacts->add( 'location', $mission->location );
		if( strlen( trim( $mission->reference ) ) )
			$this->helperFacts->add( 'reference', $mission->reference );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function renderHtmlMailBody(): string
	{
		$data			= $this->data;
//		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	 realize date format in module config
		$mission		= $data['mission'];
		$url			= $this->baseUrl.'work/mission/'.$mission->missionId;
		$nowWeekday		= $this->labelsWeekdays[date( 'w' )];
		$nowMonth		= $this->labelsMonthNames[date( 'n' )];
		$dateFull		= $nowWeekday.', der '.date( "j" ).'.&nbsp;'.$nowMonth;

		$content		= HtmlTag::create( 'em', $this->words['emptyContent'], ['class' => 'muted'] );
		if( strlen( trim( $mission->content ) ) )
		 	$content	= View_Helper_Markdown::transformStatic( $this->env, $mission->content );

		$data	= array_merge( $data, [
			'baseUrl'	=> $this->baseUrl,
			'words'		=> $this->words,
			'values'	=> [
				'type'		=> $this->labelsTypes[$mission->type],
				'modifier'	=> $this->renderUser( $this->modelUser->get( $mission->modifierId ) ),
				'url'		=> $url,
				'link'		=> HtmlTag::create( 'a', $mission->title, ['href' => $url] ),
				'today'		=> [
					'long'	=> HtmlTag::create( 'span', $dateFull, ['class' => 'text-date-full'] ),
					'short'	=> HtmlTag::create( 'span', date( $formatDate ), ['class' => 'text-date-short'] ),
				],
				'content'	=> $content,
			],
			'lists'		=> [
				'facts'		=> $this->helperFacts->render().' ',
			],
			'texts'		=> [
				'salute'	=> $this->salutes ? $this->salutes[array_rand( $this->salutes )] : '',
			]
		] );
		return $this->loadContentFile( 'mail/work/mission/new.html', $data ) ?? '';
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
		$mission		= $data['mission'];
		/** @var ?Entity_User $modifier */
		$modifier		= $this->modelUser->get( $mission->modifierId );
		$nowWeekday		= $this->labelsWeekdays[date( 'w' )];
		$nowMonth		= $this->labelsMonthNames[date( 'n' )];

		$content		= $this->words['emptyContent'];
		if( strlen( trim( $mission->content ) ) )
		 	$content	= strip_tags( $mission->content );

		$data	= array_merge( $data, [
			'baseUrl'	=> $this->baseUrl,
			'words'		=> $this->words,
			'values'	=> [
				'type'		=> $this->labelsTypes[$mission->type],
				'modifier'	=> $this->renderUserAsText( $modifier ),
				'link'		=> $this->baseUrl.'work/mission/'.$mission->missionId,
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
		return $this->loadContentFile( 'mail/work/mission/new.txt', $data ) ?? '';
	}
}
