<?php
class Mail_Work_Mission_Update extends Mail_Abstract{

	protected function generate( $data = array() ){
		$w			= (object) $this->getWords( 'work/mission', 'mail-update' );
		$html		= $this->renderBody( $data );
		$body		= chunk_split( base64_encode( $html ), 78 );
		$mailBody	= new Net_Mail_Body( $body, Net_Mail_Body::TYPE_HTML );
		$mailBody->setContentEncoding( 'base64' );
		$prefix		= $this->env->getConfig()->get( 'module.resource_mail.subject.prefix' );
		$subject	= $w->subject . ': '.$data['missionBefore']->title;
		$this->mail->setSubject( ( $prefix ? $prefix.' ' : '' ) . $subject );
		$this->mail->addBody( $mailBody );
		return $html;
	}

	public function renderBody( $data ){
		$baseUrl		= $this->env->getConfig()->get( 'app.base.url' );
		$w				= (object) $this->getWords( 'work/mission', 'mail-update' );
		$monthNames		= (array) $this->getWords( 'work/mission', 'months' );
		$weekdays		= (array) $this->getWords( 'work/mission', 'days' );
		$salutes		= (array) $this->getWords( 'work/mission', 'mail-salutes' );
		$salute			= $salutes ? $salutes[array_rand( $salutes )] : "";
		$statusLabels	= (array) $this->getWords( 'work/mission', 'states' );
		$priorityLabels	= (array) $this->getWords( 'work/mission', 'priorities' );
		$types			= (array) $this->getWords( 'work/mission', 'types' );
		$indicator		= new UI_HTML_Indicator();
		$titleLength	= 80;#$config->get( 'module.work_mission.mail.title.length' );
		$formatDate		= 'j.n.';#$config->get( 'module.work_mission.mail.format.date' );			//  @todo	kriss: realize date format in module config

		$diff		= array();
		$old		= $data['missionBefore'];
		$new		= $data['missionAfter'];
		$modelUser	= new Model_User( $this->env );

		if( $old->title !== $new->title ){
			$diff[]	= (object) array(
				'label'		=> "Titel",
				'line'		=> $old->title.'<br/>'.$new->title,
			);
		}
		if( $old->status !== $new->status ){
			$diff[]	= (object) array(
				'label'		=> "Status",
				'line'		=> $statusLabels[$old->status].' &rarr; '.$statusLabels[$new->status],
			);
		}
		if( $old->priority !== $new->priority ){
			$diff[]	= (object) array(
				'label'		=> "Priorität",
				'line'		=> $priorityLabels[$old->status].' &rarr; '.$priorityLabels[$new->status],
			);
		}
		if( $old->workerId !== $new->workerId ){
			$workerOld	= $modelUser->get( $old->workerId );
			$workerNew	= $modelUser->get( $new->workerId );
			$diff[]	= (object) array(
				'label'		=> "Bearbeiter",
				'line'		=> $workerOld->username.' &rarr; '.$workerNew->username,
			);
		}
		if( $old->location !== $new->location ){
			$diff[]	= (object) array(
				'label'		=> "Ort",
				'line'		=> $old->location.'<br/>'.$new->location,
			);
		}
		if( $new->dayStart && $old->dayStart !== $new->dayStart ){
			$days			= "";
			if( $old->dayStart && $new->dayStart && $old->dayStart !== $new->dayStart ){
				$days		= ( strtotime( $old->dayStart ) - strtotime( $new->dayStart ) ) / 3600 / 24;
				$sign		= $days < 0 ? '+' : '-';
				$days		= ' <small class="muted">'.$sign.abs( $days ).' Tage(e)</small>';
			}
			$dateOld	= $old->dayStart ? date( "d.m.Y", strtotime( $old->dayStart ) ) : '-';
			$dateNew	= $new->dayStart ? date( "d.m.Y", strtotime( $new->dayStart ) ) : '-';
			$diff[]	= (object) array(
				'label'		=> "Start<!--datum-->",
				'line'		=> $dateOld.' &rarr; '.$dateNew.$days,
			);
		}
		if( $new->dayEnd && $old->dayEnd !== $new->dayEnd ){
			$days			= "";
			if( $old->dayEnd && $new->dayEnd && $old->dayEnd !== $new->dayEnd ){
				$days		= ( strtotime( $old->dayEnd ) - strtotime( $new->dayEnd ) ) / 3600 / 24;
				$sign		= $days < 0 ? '+' : '-';
				$days		= ' <small class="muted">'.$sign.abs( $days ).' Tage(e)</small>';
			}
			$dateOld	= $old->dayEnd ? date( "d.m.Y", strtotime( $old->dayEnd ) ) : '-';
			$dateNew	= $new->dayEnd ? date( "d.m.Y", strtotime( $new->dayEnd ) ) : '-';
			$diff[]	= (object) array(
				'label'		=> "Ende<!--datum-->",
				'line'		=> $dateOld.' &rarr; '.$dateNew.$days,
			);
		}

		$list	= array();
		foreach( $diff as $entry )
			$list[]	= '<dt>'.$entry->label.'</dt><dd>'.$entry->line.'</dd>';
		$list	= '<dl class="not-dl-horizontal">'.join( $list ).'</dl>';

		$heading	= $w->heading ? UI_HTML_Tag::create( 'h3', $w->heading ) : "";
		$username	= $data['user']->username;
		$username	= UI_HTML_Tag::create( 'span', $username, array( 'class' => 'text-username' ) );
		$dateFull	= $weekdays[date( 'w' )].', der '.date( "j" ).'.&nbsp;'.$monthNames[date( 'n' )];
		$dateFull	= UI_HTML_Tag::create( 'span', $dateFull, array( 'class' => 'text-date-full' ) );
		$dateShort	= UI_HTML_Tag::create( 'span', date( $formatDate ), array( 'class' => 'text-date-short' ) );
		$greeting	= sprintf( $w->greeting, $username, $dateFull, $dateShort );

		$type		= $types[$old->type];
		$link		= UI_HTML_Tag::create( 'a', $old->title, array( 'href' => $baseUrl.'work/mission/'.$old->missionId ) );

		$body	= '
'.$heading.'
<div class="text-greeting text-info">'.$greeting.'</div>
<h4>'.$type.': '.$link.'</h4>
<div class="tasks">'.$list.'</div>
<!--<div class="text-salute">'.$salute.'</div>-->
<!--<div class="text-signature">'.$w->textSignature.'</div>-->';

		$this->addPrimerStyle( 'layout.css' );
		$this->addThemeStyle( 'bootstrap.css' );
		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'site.mission.css' );
		$this->addThemeStyle( 'indicator.css' );

		$this->page->addBody( $body );
		$class	= 'moduleWorkMission jobWorkMission job-work-mission-mail-daily';
//		print( $this->page->build( array( 'class' => $class ) ) );
//	die;
		return $this->page->build( array( 'class' => $class ) );
	}
}
?>
