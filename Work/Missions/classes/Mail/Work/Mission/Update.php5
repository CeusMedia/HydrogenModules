<?php
class Mail_Work_Mission_Update extends Mail_Work_Mission_Change{

	protected $languageSection	= 'mail-update';

	public function renderBody( $data ){
		$indicator		= new UI_HTML_Indicator();
		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	kriss: realize date format in module config

		$diff		= array();
		$old		= $data['missionBefore'];
		$new		= $data['missionAfter'];

		$this->setSubjectFromMission( $old );

		$this->enlistFact( 'type', $this->labelsTypes[$old->type] );
		if( $old->type !== $new->type )
			$this->enlistFact( 'type', $this->labelsTypes[$old->type].' &rarr; '.$this->labelsTypes[$new->type], 'label label-info' );


		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$model		= new Model_Project( $this->env );
			$projectOld	= $old->projectId ? $model->get( $old->projectId ) : '-';
			$this->enlistFact( 'projectId', $projectOld->title );
			if( $old->projectId !== $new->projectId ){
				$projectNew	= $new->projectId ? $model->get( $new->projectId ) : '-';
				$this->enlistFact( 'projectId', $projectOld->title.' &rarr; '.$projectNew->title, 'label label-info' );
			}
		}

		$this->enlistFact( 'title', $old->title );
		if( $old->title !== $new->title )
			$this->enlistFact( 'title', $old->title.'<br/>'.$new->title, 'label label-info' );

		$this->enlistFact( 'status', $this->labelsStates[$old->status] );
		if( $old->status !== $new->status )
			$this->enlistFact( 'status', $this->labelsStates[$old->status].' &rarr; '.$this->labelsStates[$new->status], $old->status < $new->status );

		$this->enlistFact( 'priority', $this->labelsPriorities[$old->priority] );
		if( $old->priority !== $new->priority )
			$this->enlistFact( 'priority', $this->labelsPriorities[$old->priority].' &rarr; '.$this->labelsPriorities[$new->priority], $old->priority < $new->priority );

		if( $old->workerId ){
			$worker		= $this->modelUser->get( $old->workerId );
			$this->enlistFact( 'worker', $worker->username );
			if( $old->workerId !== $new->workerId ){
				$workerOld	= $this->modelUser->get( $old->workerId );
				$workerNew	= $this->modelUser->get( $new->workerId );
				$this->enlistFact( 'worker', $workerOld->username.' &rarr; '.$workerNew->username, TRUE );
			}
		}

		if( $old->location || $new->location ){
			$this->enlistFact( 'location', $old->location );
			if( $old->location !== $new->location )
				$this->enlistFact( 'location', $old->location.'<br/>'.$new->location, TRUE );
		}

		if( $old->dayStart && $new->dayStart ){
			$dateOld	= date( "d.m.Y", strtotime( $old->dayStart ) );
			$dateNew	= date( "d.m.Y", strtotime( $new->dayStart ) );
			$key		= $new->type ? 'dayStart' : 'dayWork';
			$this->enlistFact( $key, $dateOld );
			if( $old->dayStart !== $new->dayStart ){
				$days		= round( ( strtotime( $old->dayStart ) - strtotime( $new->dayStart ) ) / 3600 / 24 );
				$sign		= $days < 0 ? '+' : '-';
				$badge		= ' <small class="not-muted">('.$sign.abs( round( $days ) ).')</small>';
				$dateOld	= $this->labelsWeekdays[date( 'N', strtotime( $old->dayStart ) ) % 7].', '.$dateOld;
				$dateNew	= $this->labelsWeekdays[date( 'N', strtotime( $new->dayStart ) ) % 7].', '.$dateNew;
				$this->enlistFact( $key, $dateOld.' &rarr; '.$dateNew/*.$badge*/, $days < 0 );
			}
		}

		if( $old->dayEnd && $new->dayEnd ){
			$dateOld	= date( "d.m.Y", strtotime( $old->dayEnd ) );
			$dateNew	= date( "d.m.Y", strtotime( $new->dayEnd ) );
			$key		= $new->type ? 'dayEnd' : 'dayDue';
			$this->enlistFact( $key, $dateOld );
			if( $old->dayEnd !== $new->dayEnd ){
				$days		= round( ( strtotime( $old->dayEnd ) - strtotime( $new->dayEnd ) ) / 3600 / 24 );
				$sign		= $days < 0 ? '+' : '-';
				$badge		= ' <small class="not-muted">('.$sign.abs( round( $days ) ).')</small>';
				$dateOld	= $this->labelsWeekdays[date( 'N', strtotime( $old->dayEnd ) ) % 7].', '.$dateOld;
				$dateNew	= $this->labelsWeekdays[date( 'N', strtotime( $new->dayEnd ) ) % 7].', '.$dateNew;
				$this->enlistFact( $key, $dateOld.' &rarr; '.$dateNew/*.$badge*/, $days < 0 );
			}
		}

		$list	= array();
		foreach( $diff as $entry )
			$list[]	= '<dt>'.$entry->label.'</dt><dd>'.$entry->line.'</dd>';
		$list		= '<dl class="dl-horizontal">'.join( $list ).'</dl>';
		$list		= UI_HTML_Tag::create( 'dl', $this->facts, array( 'class' => 'dl-horizontal' ) );

		$heading	= $this->words->heading ? UI_HTML_Tag::create( 'h3', $this->words->heading ) : "";
		$username	= $data['user']->username;
		$username	= UI_HTML_Tag::create( 'span', $username, array( 'class' => 'text-username' ) );
		$dateFull	= $this->labelsWeekdays[date( 'w' )].', der '.date( "j" ).'.&nbsp;'.$this->labelsMonthNames[date( 'n' )];
		$dateFull	= UI_HTML_Tag::create( 'span', $dateFull, array( 'class' => 'text-date-full' ) );
		$dateShort	= UI_HTML_Tag::create( 'span', date( $formatDate ), array( 'class' => 'text-date-short' ) );
		$greeting	= sprintf( $this->words->greeting, $username, $dateFull, $dateShort );
		$heading	= $this->words->heading ? UI_HTML_Tag::create( 'h3', $this->words->heading ) : '';
		$greeting	= sprintf( $this->words->greeting, $username, $dateFull, $dateShort );
		$type		= $this->labelsTypes[$old->type];
		$salute		= $this->salutes ? $this->salutes[array_rand( $this->salutes )] : '';
		$url		= $this->baseUrl.'work/mission/'.$old->missionId;
		$words		= $this->words;
		$baseUrl	= $this->baseUrl;
		$content	= View_Helper_Markdown::transformStatic( $this->env, $new->content );
		$link		= UI_HTML_Tag::create( 'a', $old->title, array( 'href' => $url ) );
		$body		= require( 'templates/work/mission/mails/update.php' );

		$this->page->addBody( $body );
		$class	= 'moduleWorkMission jobWorkMission job-work-mission-mail-update';
		return $this->page->build( array( 'class' => $class ) );
	}
}
?>
