<?php
class Mail_Work_Mission_Update extends Mail_Work_Mission_Change{

	protected $languageSection	= 'mail-update';
	protected $helperFacts;

	public function generate( $data = array() ){
		parent::generate( $data );
		$this->setSubjectFromMission( $data['missionBefore'] );
		$this->prepareFacts( $data );
		$this->addBodyClass( 'job-work-mission-mail-update' );
		$this->setHtml( $this->renderHtml( $data ) );
		$this->setText( $this->renderText( $data ) );
	}

	protected function renderLabel( $content, $class = NULL ){
		$class	= 'label'.( $class ? ' label-'.$class : '' );
		return UI_HTML_Tag::create( 'span', $content, array( 'class' => $class ) );
	}

	protected function prepareFacts( $data ){
		$old		= $data['missionBefore'];
		$new		= $data['missionAfter'];

		$this->helperFacts	= new View_Helper_Mail_Facts( $this->env );
		$this->helperFacts->setLabels( (array) $this->labels );
		$this->helperFacts->setTextLabelLength( 13 );

		$typeHtml	= $this->labelsTypes[$old->type];
		$typeText	= $this->labelsTypes[$old->type];
		if( $old->type !== $new->type ){
			$typeHtml	= $this->renderLabel( $typeHtml.' &rarr; '.$this->labelsTypes[$new->type], 'info' );
			$typeText	= $typeText.' -> '.$this->labelsTypes[$new->type];
		}
		$this->helperFacts->add( 'type', $typeHtml, $typeText );

		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$logicProject	= new Logic_Project( $this->env );
			$projectOld		= $old->projectId ? $logicProject->getProject( $old->projectId ) : '-';
			$linkProjectOld	= UI_HTML_Tag::create( 'a', $projectOld->title, array( 'href' => './manage/project/view/'.$projectOld->projectId ) );
			$projectHtml	= $linkProjectOld;
			$projectText	= $projectOld->title;
			if( $new->projectId && $old->projectId !== $new->projectId ){
				$projectNew		= $logicProject->getProject( $new->projectId );
				$linkProjectNew	= UI_HTML_Tag::create( 'a', $projectNew->title, array( 'href' => './manage/project/view/'.$projectNew->projectId ) );
				$projectHtml	.= '<br/>&rarr; '.$linkProjectNew;
				$projectText	= $projectText.PHP_EOL.'-> '.$projectNew->title;
			}
			$this->helperFacts->add( 'projectId', $projectHtml, $projectText );
		}

		$titleHtml		= $this->renderLinkedTitle( $old );
		$titleText		= $old->title;
		if( $old->title !== $new->title ){
			$titleHtml	= $titleHtml.'<br/>&rarr; '.$this->renderLinkedTitle( $new );
			$titleText	= $titleText.PHP_EOL.'-> '.$new->title;
		}
		$this->helperFacts->add( 'title', $titleHtml, $titleText );

		$statusHtml		= $this->labelsStates[$old->status];
		$statusText		= $this->labelsStates[$old->status];
		if( (int) $old->status !== (int) $new->status ){
			$labelClass		= $old->status < $new->status ? 'success' : 'important';
			$statusHtml		= $this->renderLabel( $statusHtml.' &rarr; '.$this->labelsStates[$new->status], $labelClass );
			$statusText		= $statusText.' -> '.$this->labelsStates[$new->status];
		}
		$this->helperFacts->add( 'status', $statusHtml, $statusText );

		$priorityHtml	= $this->labelsPriorities[$old->priority];
		$priorityText	= $this->labelsPriorities[$old->priority];
		if( (int) $old->priority !== (int) $new->priority ){
			$labelClass		= $old->priority < $new->priority ? 'success' : 'important';
			$priorityHtml	= $this->renderLabel( $priorityHtml.' &rarr; '.$this->labelsPriorities[$new->priority], $labelClass );
			$priorityText	= $priorityText.' -> '.$this->labelsPriorities[$new->priority];
		}
		$this->helperFacts->add( 'priority', $priorityHtml, $priorityText );

		if( $old->workerId ){
			$workerOld	= $this->modelUser->get( $old->workerId );
			$workerHtml	= $this->renderUser( $workerOld, TRUE );
			$workerText	= $this->renderUserAsText( $workerOld );
			if( $new->workerId && (int) $old->workerId !== (int) $new->workerId ){
				$workerNew	= $this->modelUser->get( $new->workerId );
				$workerHtml	= $workerHtml.' &rarr; '.$this->renderUser( $workerNew, TRUE );
				$workerText	= $workerText.' -> '.$this->renderUserAsText( $workerNew );
			}
			$this->helperFacts->add( 'worker', $workerHtml, $workerText );
		}

		if( $old->dayStart && $new->dayStart ){
			$dateOld		= date( "d.m.Y", strtotime( $old->dayStart ) );
			$weekdayOld		= $this->labelsWeekdays[date( 'N', strtotime( $old->dayStart ) ) % 7];
			$labelKey		= $new->type ? 'dayStart' : 'dayWork';
			$dateStartHtml	= $weekdayOld.',&nbsp;'.$dateOld;
			$dateStartText	= $weekdayOld.', '.$dateOld;
			$diffHtml		= $diffText		= '';
			if( $old->dayStart !== $new->dayStart ){
				$days			= round( ( strtotime( $new->dayStart ) - strtotime( $old->dayStart ) ) / 3600 / 24 );
				$signHtml		= $days > 0 ? '&plus;' : '&minus;';
				$signText		= $days > 0 ? '+' : '-';
				$diffHtml		= ' '.$this->renderLabel( $signHtml.abs( round( $days ) ), $days < 0 ? 'important' : 'success' );
				$diffText		= ' ('.$signText.abs( round( $days ) ).')';
				$dateNew		= date( "d.m.Y", strtotime( $new->dayStart ) );
				$weekdayNew		= $this->labelsWeekdays[date( 'N', strtotime( $new->dayStart ) ) % 7];
				$dateStartHtml	.= ' &rarr; '.$weekdayNew.',&nbsp;'.$dateNew;
				$dateStartText	.= ' -> '.$weekdayNew.', '.$dateNew;
			}
			$this->helperFacts->add( $labelKey, $dateStartHtml.$diffHtml, $dateStartText.$diffText );
		}

		if( $old->dayEnd && $new->dayEnd ){
			$dateOld		= date( "d.m.Y", strtotime( $old->dayEnd ) );
			$weekdayOld		= $this->labelsWeekdays[date( 'N', strtotime( $old->dayEnd ) ) % 7];
			$labelKey		= $new->type ? 'dayEnd' : 'dayDue';
			$dateEndHtml	= $weekdayOld.',&nbsp;'.$dateOld;
			$dateEndText	= $weekdayOld.', '.$dateOld;
			$diffHtml		= $diffText		= '';
			if( $old->dayEnd !== $new->dayEnd ){
				$days			= round( ( strtotime( $new->dayEnd ) - strtotime( $old->dayEnd ) ) / 3600 / 24 );
				$signHtml		= $days > 0 ? '&plus;' : '&minus;';
				$signText		= $days > 0 ? '+' : '-';
				$diffHtml		= ' '.$this->renderLabel( $signHtml.abs( round( $days ) ), $days < 0 ? 'important' : 'success' );
				$diffText		= ' ('.$signText.abs( round( $days ) ).')';
				$dateNew		= date( "d.m.Y", strtotime( $new->dayEnd ) );
				$weekdayNew		= $this->labelsWeekdays[date( 'N', strtotime( $new->dayEnd ) ) % 7];
				$dateEndHtml	.= ' &rarr; '.$weekdayNew.',&nbsp;'.$dateNew;
				$dateEndText	.= ' -> '.$weekdayNew.', '.$dateNew;
			}
			$this->helperFacts->add( $labelKey, $dateEndHtml.$diffHtml, $dateEndText.$diffText );
		}

		if( $new->type == 1 ){
			$diffHtml		= '';
			$diffText		= '';
			if( $old->type == 1 ){
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

	public function renderHtml( $data ){
		$indicator		= new UI_HTML_Indicator();
		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	kriss: realize date format in module config

		$old			= $data['missionBefore'];
		$new			= $data['missionAfter'];
		$url			= $this->baseUrl.'work/mission/'.$old->missionId;
		$nowWeekday		= $this->labelsWeekdays[date( 'w' )];
		$nowMonth		= $this->labelsMonthNames[date( 'n' )];
		$dateFull		= $nowWeekday.', der '.date( "j" ).'.&nbsp;'.$nowMonth;

		$content		= UI_HTML_Tag::create( 'em', $this->words->emptyContent, array( 'class' => 'muted' ) );
		if( strlen( trim( $new->content ) ) )
		 	$content	= View_Helper_Markdown::transformStatic( $this->env, $new->content );

		$data	= array_merge( $data, array(
			'baseUrl'	=> $this->baseUrl,
			'words'		=> $this->words,
			'values'	=> array(
				'type'		=> $this->labelsTypes[$old->type],
				'modifier'	=> $this->renderUser( $this->modelUser->get( $new->modifierId ) ),
				'url'		=> $url,
				'link'		=> UI_HTML_Tag::create( 'a', $old->title, array( 'href' => $url ) ),
				'today'		=> array(
					'long'	=> UI_HTML_Tag::create( 'span', $dateFull, array( 'class' => 'text-date-full' ) ),
					'short'	=> UI_HTML_Tag::create( 'span', date( $formatDate ), array( 'class' => 'text-date-short' ) ),
				),
				'content'	=> $content,
			),
			'lists'		=> array(
				'facts'		=> $this->helperFacts->render(),
			),
			'texts'		=> array(
				'salute'	=> $this->salutes ? $this->salutes[array_rand( $this->salutes )] : '',
			)
		) );
		return $this->view->loadContentFile( 'mail/work/mission/update.html', $data );
	}

	public function renderText( $data ){
		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	kriss: realize date format in module config
		$old			= $data['missionBefore'];
		$new			= $data['missionAfter'];
		$modifier		= $this->modelUser->get( $new->modifierId );
		$nowWeekday		= $this->labelsWeekdays[date( 'w' )];
		$nowMonth		= $this->labelsMonthNames[date( 'n' )];

		$content		= $this->words->emptyContent;
		if( strlen( trim( $new->content ) ) )
			$content	=  strip_tags( $new->content );

		$data	= array_merge( $data, array(
			'baseUrl'	=> $this->baseUrl,
			'words'		=> $this->words,
			'values'	=> array(
				'type'		=> $this->labelsTypes[$old->type],
				'modifier'	=> $this->renderUserAsText( $modifier ),
				'link'		=> $this->baseUrl.'work/mission/'.$old->missionId,
				'today'		=> array(
					'long'	=> $nowWeekday.', der '.date( "j" ).'.&nbsp;'.$nowMonth,
					'short'	=> date( $formatDate ),
				),
				'content'	=> $content,
			),
			'lists'		=> array(
				'facts'		=> $this->helperFacts->renderAsText()
			),
			'texts'		=> array(
				'salute'	=> $this->salutes ? $this->salutes[array_rand( $this->salutes )] : '',
			)
		) );
		return $this->view->loadContentFile( 'mail/work/mission/update.txt', $data );
	}
}
?>
