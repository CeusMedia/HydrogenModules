<?php
class Mail_Work_Issue_Change extends Mail_Abstract{

	protected $baseUrl;
	protected $labelsStates;

	protected function generate( $data = array() ){
		$this->baseUrl			= $this->env->getConfig()->get( 'app.base.url' );
		$this->labelsStates		= (array) $this->getWords( 'work/issue', 'states' );

		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'layout.panels.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'site.work.issue.css' );

		$modelIssue		= new Model_Issue( $this->env );
		$issue			= $modelIssue->get( $data['issue']->issueId );

		$html		= $this->renderBody( $data );
		$this->mail->addHtml( $html, 'utf-8', 'quoted-printable' );
		$this->setSubject( '['.$this->labelsStates[$data['issue']->status].'] '.$data['issue']->title );
		return $html;
	}

	public function renderBody( $data ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$words		= $this->env->getLanguage()->getWords( 'work/issue' );

		$modelIssue	= new Model_Issue( $this->env );
		$modelNote	= new Model_Issue_Note( $this->env );

		$helperFacts	= new View_Helper_Work_Issue_ChangeFacts( $this->env );
		$helperNote		= new View_Helper_Work_Issue_ChangeNote( $this->env );

		$notes			= $modelNote->getAllByIndex( 'issueId', $data['issue']->issueId );

		$listChanges	= '';
		if( count( $notes ) > 2 ){
			$helperChanges	= new View_Helper_Work_Issue_Changes( $this->env );
			$helperChanges->setIssue( $data['issue'] );
			$listChanges	= '<br/><h4>Gesamte Entwicklung</h4>'.$helperChanges->render();
		}

		$latestNote		= array_pop( $notes );
		$helperFacts->setNote( $latestNote );
		$helperNote->setNote( $latestNote );

		$body	= '
<div class="navbar navbar-inverse">
	<div class="navbar-inner">
		<div class="container">
			<a href="'.$this->baseUrl.'" class="brand">
				<i class="icon-fire icon-white"></i> '.$wordsMain['main']['title'].'
			</a>
		</div>
	</div>
</div>
<div class="container">
	<big><a href="./work/issue/edit/'.$data['issue']->issueId.'">'.$data['issue']->title.'</a></big>
	<p>'.nl2br( $data['issue']->content ).'</p>
	<br/>
	<h4>Letzte Änderung</h4>
	'.$helperFacts->render().'
	'.$helperNote->render().'
	'.$listChanges.'
	<br/>
</div>';
		$this->page->setBody( $body );
		$html	= $this->page->build();
		return $html;
	}
}
?>
