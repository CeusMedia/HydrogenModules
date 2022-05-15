<?php
class Mail_Job_Report extends Mail_Abstract
{
	protected function generate(): self
	{
		$data	= $this->data;
//		$words	= $this->env->getLanguage()->getWords( 'resource/jobs' );
		$words	= array(
			'job-run-statuses'	=> array(
				Model_Job_Run::STATUS_TERMINATED	=> 'terminiert',
				Model_Job_Run::STATUS_FAILED		=> 'gescheitert',
				Model_Job_Run::STATUS_ABORTED		=> 'abgebrochen',
				Model_Job_Run::STATUS_PREPARED		=> 'vorbereitet',
				Model_Job_Run::STATUS_RUNNING		=> 'läuft',
				Model_Job_Run::STATUS_DONE			=> 'erledigt',
			),
			'job-run-types'		=> array(
				Model_Job_Run::TYPE_MANUALLY		=> 'manuell',
				Model_Job_Run::TYPE_SCHEDULED		=> 'geplant',
			)
		);

		$data['words']	= $words;

		$this->addCommonStyle( 'module.resource.jobs-mail.css' );
//		print_m( $data );
		$this->setSubject( vsprintf( 'Job: %s: %s', array(
			$data['definition']->identifier,
			$words['job-run-statuses'][$data['run']->status],
		) ) );
		$this->setHtml( $this->renderHtml() );
		$this->setText( $this->renderText() );
		return $this;
	}

	protected function renderHtml(): string
	{
		$blockException	= $this->renderExceptionBlockAsHtml( $this->data );
		$blockFacts		= $this->renderFactsBlockAsHtml( $this->data );

		$title	= $this->data['run']->title  ?: $this->data['definition']->identifier;
		$html	= '
		<div>
			<h2><span class="muted">Job:</span> '.$title.'</h2>
			'.$blockFacts.'
			'.$blockException.'
			<small>
				<h4>Raw Data Object</h4>
				'.print_m( $this->data, NULL, NULL, TRUE, 'html' ).'
			</small>
		</div>
		<style>
		</style>';
		return $html;

	}

	protected function renderText(): self
	{
		return json_encode( $this->data, JSON_PRETTY_PRINT );
	}

	protected function parseTraceString( string $trace ): array
	{
		$list	= array();
		foreach( explode( PHP_EOL, $trace ) as $nr => $line ){
			$matches	= array();
			$item		= (object) array(
				'nr'	=> $nr,
				'file'	=> NULL,
				'line'	=> NULL,
				'call'	=> NULL,
			);
			if( preg_match( '@^(#(\d+) )((\S+)\((\d+)\)): (.+)$@', trim( $line ), $matches ) ){
				$item->file	= $matches[4];
				$item->line	= $matches[5];
				$item->call	= $matches[6];
			}
			else if( preg_match( '@^(#(\d+) )(.+)$@', trim( $line ), $matches ) ){
				$item->call	= $matches[3];
			}
			$list[]	= $item;
		}
		return $list;
	}

	protected function renderExceptionBlockAsHtml( $data ): string
	{
		$message		= json_decode( $data['run']->message );
		if( $message->type !== 'throwable' )
			return '';
		$trace		= $this->renderTraceStringAsHtml( $message->trace );
		$file		= $this->stripAppPathFromAbsoluteFilePath( $message->file );
		$exception	= '
		<div class="content-block block-exception">
			<h4>Exception</h4>
			<div class="facts-list">
				<div class="fact-label">Message</div>
				<div class="fact-value">'.$message->message.'</div>
				<div class="fact-label">File / Line</div>
				<div class="fact-value">'.$file.' #'.$message->line.'</div>
				<div class="fact-label">Stack Trace</div>
				<div>'.$trace.'</div>
			</dl>
		</div>';
		return $exception;
	}

	protected function renderFactsBlockAsHtml( $data ): string
	{
		$statusClasses	= array(
			Model_Job_Run::STATUS_TERMINATED	=> 'label-inverse',
			Model_Job_Run::STATUS_FAILED		=> 'label-important',
			Model_Job_Run::STATUS_ABORTED		=> '',
			Model_Job_Run::STATUS_PREPARED		=> 'label-warning',
			Model_Job_Run::STATUS_RUNNING		=> 'label-info',
			Model_Job_Run::STATUS_DONE			=> 'label-success',
		);
		$durationHelper	= new Alg_Time_Duration();
		$typeWord		= $data['words']['job-run-types'][$data['run']->type];
		$typeLabel		= $typeWord;
		$status			= $data['run']->status;
		$statusClass	= $statusClasses[$status];
		$statusWord		= $data['words']['job-run-statuses'][$status];
		$statusLabel	= UI_HTML_Tag::create( 'span', $statusWord, array( 'class' => 'label '.$statusClass ) );
		$seconds		= $data['run']->finishedAt - $data['run']->ranAt;
		$duration		= $seconds ? $durationHelper->convertSecondsToDuration( $seconds, ' ' ) : '0s';
		$message		= json_decode( $data['run']->message );

		$facts	= array();
		if( $data['run']->title )
			$facts['Job-ID']	= $data['definition']->identifier;
		$facts['Typ']		= $typeLabel;
		$facts['Status']	= $statusLabel;
		$facts['Gestartet']	= date( 'd.m.Y H:i:s', $data['run']->ranAt );
		$facts['Beendet']	= date( 'd.m.Y H:i:s', $data['run']->finishedAt );
		$facts['Laufzeit']	= $duration;
		$facts['Ergebnis']	= $message->type;

		$facts	= '<div class="content-block block-facts"><h4>Fakten</h4>'.$this->renderFactsAsHtml( $facts ).'</div>';
		return $facts;
	}

	protected function renderTraceStringAsHtml( $traceAsString ): string
	{
		$list	= array();
		$traces	= $this->parseTraceString( $traceAsString );
		foreach( $traces as $item ){
			$nr	= count( $traces ) - $item->nr;
			if( $item->file ){
				$file	= $this->stripAppPathFromAbsoluteFilePath( $item->file );
				$list[]	= '<div class="trace-item">
					<div class="trace-item-call">'.$nr.'. '.$item->call.'</div>
					<div class="trace-item-file">'.$file.' ('.$item->line.')</div>
				</div>';
			}
			else{
				$list[]	= '<div class="trace-item">
					<div class="trace-item-call">'.$nr.'. '.$item->call.'</div>
				</div>';
			}
		}
		$list	= '<div class="trace-list">'.join( $list ).'</div>';
		return $list;
	}

	protected function renderFactsAsHtml( array $facts, ?string $listClass = 'dl-horizontal', ?string $listId = NULL ): string
	{
		$list	= array();
		foreach( $facts as $key => $value ){
			$list[]	= UI_HTML_Tag::create( 'dt', $key );
			$list[]	= UI_HTML_Tag::create( 'dd', $value );
		}
		return UI_HTML_Tag::create( 'dl', $list, array(
			'class'	=> $listClass,
			'id'	=> $listId
		) );
	}

	protected function stripAppPathFromAbsoluteFilePath( string $filePath ): string
	{
		return preg_replace( '@^'.preg_quote( $this->env->uri, '@' ).'@', '', $filePath );
	}
}
