<?php
class Mail_Work_Issue_New extends Mail_Abstract{

	protected $baseUrl;
	protected $words;

	protected function generate( $data = array() ){
		$this->baseUrl		= $this->env->getConfig()->get( 'app.base.url' );
		$this->modelIssue	= new Model_Issue( $this->env );
		$this->modelUser	= new Model_User( $this->env );
		$this->words		= (array) $this->getWords( 'work/issue' );

		$this->addThemeStyle( 'layout.css' );
		$this->addThemeStyle( 'layout.panels.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'site.work.issue.css' );

		$issue		= $data['issue'];

		$html		= $this->renderBody( $data );
		$this->mail->addHtml( $html, 'utf-8', 'quoted-printable' );
		$this->setSubject( '['.$this->words['states'][$issue->status].'] '.$issue->title );
		return $html;
	}

	public function renderBody( $data ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$issue		= $data['issue'];
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
	<big><a href="./work/issue/edit/'.$issue->issueId.'">'.$issue->title.'</a></big>
	<p>'.nl2br( $issue->content ).'</p>
	<br/>
</div>';
		$this->page->setBody( $body );
		$html	= $this->page->build();
		return $html;
	}
}
?>
