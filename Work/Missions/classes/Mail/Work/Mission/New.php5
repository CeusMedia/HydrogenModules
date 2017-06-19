<?php
class Mail_Work_Mission_New extends Mail_Work_Mission_Change{

	protected $languageSection	= 'mail-new';

	public function generate( $data = array() ){
		parent::generate( $data );
		$this->addBodyClass( 'job-work-mission-mail-new' );
		return $this->setHtml( $this->renderBody( $data ) );
	}

	public function renderBody( $data ){
		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	kriss: realize date format in module config

		$mission	= $data['mission'];
		$this->setSubjectFromMission( $mission );

		$this->enlistFact( 'type', $this->labelsTypes[$mission->type] );
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$model		= new Model_Project( $this->env );
			$project	= $model->get( $mission->projectId );
			$this->enlistFact( 'projectId', $project->title );
		}
		if( (int) $mission->workerId ){
			$worker		= $this->modelUser->get( $mission->workerId );
			$this->enlistFact( 'worker', $this->renderUser( $worker ) );
		}
		$this->enlistFact( 'status', $this->labelsStates[$mission->status] );
		$this->enlistFact( 'priority', $this->labelsPriorities[$mission->priority] );

		$timestampStart	= strtotime( $mission->dayStart );
		$timestampEnd	= strtotime( $mission->dayEnd );
		$dateStart		= date( 'd.m.Y', $timestampStart );
		$dateEnd		= date( 'd.m.Y', $timestampEnd );
		$weekdayStart	= $this->labelsWeekdays[date( 'N', $timestampStart ) % 7];
		$weekdayEnd		= $this->labelsWeekdays[date( 'N', $timestampEnd ) % 7];

		if( $mission->type ){
			$this->enlistFact( 'dayStart', $weekdayStart.', '.$dateStart );
			$this->enlistFact( 'dayEnd', $weekdayEnd.', '.$dateEnd );
			$this->enlistFact( 'timeStart', date( 'H:i', $timestampStart ) );
			$this->enlistFact( 'timeEnd', date( 'H:i', $timestampEnd ) );
		}
		else{
			$this->enlistFact( 'dayWork', $weekdayStart.', '.$dateStart );
			$this->enlistFact( 'dayDue', $weekdayEnd.', '.$dateEnd );
		}
		if( strlen( trim( $mission->location ) ) )
			$this->enlistFact( 'location', $mission->location );
		if( strlen( trim( $mission->reference ) ) )
			$this->enlistFact( 'reference', $mission->reference );

		$list	= UI_HTML_Tag::create( 'dl', $this->facts, array( 'class' => 'dl-horizontal' ) );

		$username	= $data['user']->username;
		$username	= UI_HTML_Tag::create( 'span', $username, array( 'class' => 'text-username' ) );
		$dateFull	= $this->labelsWeekdays[date( 'w' )].', der '.date( "j" ).'.&nbsp;'.$this->labelsMonthNames[date( 'n' )];
		$dateFull	= UI_HTML_Tag::create( 'span', $dateFull, array( 'class' => 'text-date-full' ) );
		$dateShort	= UI_HTML_Tag::create( 'span', date( $formatDate ), array( 'class' => 'text-date-short' ) );
		$greeting	= sprintf( $this->words->greeting, $username, $dateFull, $dateShort );
		$salute		= $this->salutes ? $this->salutes[array_rand( $this->salutes )] : '';
		$url		= $this->baseUrl.'work/mission/'.$mission->missionId;
		$link		= UI_HTML_Tag::create( 'a', $mission->title, array( 'href' => $url ) );
		$modifier	= $this->renderUser( $data['modifier'] );
		$words		= $this->words;
		$baseUrl	= $this->baseUrl;
		$content	= View_Helper_Markdown::transformStatic( $this->env, $mission->content );
		return require( 'templates/work/mission/mails/new.php' );
	}
}
?>
