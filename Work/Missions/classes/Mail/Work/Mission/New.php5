<?php
class Mail_Work_Mission_New extends Mail_Abstract{

	protected function generate( $data = array() ){
		$w			= (object) $this->getWords( 'work/mission', 'mail-new' );
		$html		= $this->renderBody( $data );
		$body		= chunk_split( base64_encode( $html ), 78 );
		$mailBody	= new Net_Mail_Body( $body, Net_Mail_Body::TYPE_HTML );
		$mailBody->setContentEncoding( 'base64' );
		$prefix		= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= $w->subject . ': ' . $data['mission']->title;
		$this->mail->setSubject( ( $prefix ? $prefix.' ' : '' ) . $subject );
		$this->mail->addBody( $mailBody );
		return $html;
	}

	public function renderBody( $data ){
		$baseUrl		= $this->env->getConfig()->get( 'app.base.url' );
		$w				= (object) $this->getWords( 'work/mission', 'mail-new' );
		$monthNames		= (array) $this->getWords( 'work/mission', 'months' );
		$weekdays		= (array) $this->getWords( 'work/mission', 'days' );
		$types			= (array) $this->getWords( 'work/mission', 'types' );
		$states			= (array) $this->getWords( 'work/mission', 'states' );
		$priorities		= (array) $this->getWords( 'work/mission', 'priorities' );
		$labels			= (object) $this->getWords( 'work/mission', 'add' );
		$salutes		= (array) $this->getWords( 'work/mission', 'mail-salutes' );
		$salute			= $salutes ? $salutes[array_rand( $salutes )] : "";
		$indicator		= new UI_HTML_Indicator();
		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	kriss: realize date format in module config

		$list		= array();
		$mission	= $data['mission'];
		$modelUser	= new Model_User( $this->env );
		$worker		= $modelUser->get( $mission->workerId );

//		$list[]	= '<dt>'.$labels->labelTitle.'<dt><dd>'.$mission->title.'</dd>';
		$list[]	= '<dt>'.$labels->labelType.'<dt><dd>'.$types[$mission->type].'</dd>';
		if( $this->env->getModules()->has( 'Manage_Projects' ) ){
			$model		= new Model_Project( $this->env );
			$project	= $model->get( $mission->projectId );
			$list[]		= '<dt>'.$labels->labelProjectId.'<dt><dd>'.$project->title.'</dd>';
		}
		if( (int) $mission->workerId )
			$list[]	= '<dt>'.$labels->labelWorker.'</dt><dd>'.$worker->username.'</dd>';
		$list[]	= '<dt>'.$labels->labelStatus.'</dt><dd>'.$states[$mission->status].'</dd>';
		$list[]	= '<dt>'.$labels->labelPriority.'</dt><dd>'.$priorities[$mission->priority].'</dd>';

		$timestampStart	= strtotime( $mission->dayStart );
		$timestampEnd	= strtotime( $mission->dayEnd );
		$dateStart		= date( 'd.m.Y', $timestampStart );
		$dateEnd		= date( 'd.m.Y', $timestampEnd );
		$weekdayStart	= $weekdays[date( 'N', $timestampStart ) % 7];
		$weekdayEnd		= $weekdays[date( 'N', $timestampEnd ) % 7];

		if( $mission->type ){
			$list[]	= '<dt>'.$labels->labelDayStart.'</dt><dd>'.$weekdayStart.', '.$datestart.'</dd>';
			$list[]	= '<dt>'.$labels->labelDayEnd.'</dt><dd>'.$weekdayEnd.', '.$dateEnd.'</dd>';
		}
		else{
			$list[]	= '<dt>'.$labels->labelDayWork.'</dt><dd>'.$weekdayStart.', '.$datestart.'</dd>';
			$list[]	= '<dt>'.$labels->labelDayDue.'</dt><dd>'.$weekdayEnd.', '.$dateEnd.'</dd>';
		}
		if( strlen( trim( $mission->location ) ) )
			$list[]	= '<dt>'.$labels->labelLocation.'</dt><dd>'.$mission->location.'</dd>';
		if( strlen( trim( $mission->reference ) ) )
			$list[]	= '<dt>'.$labels->labelReference.'</dt><dd>'.$mission->reference.'</dd>';

		$list	= '<dl class="dl-horizontal">'.join( $list ).'</dl>';

		$username	= $data['user']->username;
		$username	= UI_HTML_Tag::create( 'span', $username, array( 'class' => 'text-username' ) );
		$dateFull	= $weekdays[date( 'w' )].', der '.date( "j" ).'.&nbsp;'.$monthNames[date( 'n' )];
		$dateFull	= UI_HTML_Tag::create( 'span', $dateFull, array( 'class' => 'text-date-full' ) );
		$dateShort	= UI_HTML_Tag::create( 'span', date( $formatDate ), array( 'class' => 'text-date-short' ) );
		$greeting	= sprintf( $w->greeting, $username, $dateFull, $dateShort );
		$url		= $baseUrl.'work/mission/'.$mission->missionId;
		$link		= UI_HTML_Tag::create( 'a', $mission->title, array( 'href' => $url ) );
		$heading	= $w->heading ? UI_HTML_Tag::create( 'h3', $w->heading ) : '';
		$greeting	= sprintf( $w->greeting, $username, $dateFull, $dateShort );
		$body		= require( 'templates/work/mission/mails/new.php' );

		$this->addPrimerStyle( 'layout.css' );
		$this->addThemeStyle( 'bootstrap.css' );
		$this->addThemeStyle( 'bootstrap.respsonsive.css' );
		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'site.mission.css' );
		$this->addThemeStyle( 'indicator.css' );

		$this->page->addBody( $body );
		$class	= 'moduleWorkMission jobWorkMission job-work-mission-mail-new';
		return $this->page->build( array( 'class' => $class ) );
	}
}
?>
