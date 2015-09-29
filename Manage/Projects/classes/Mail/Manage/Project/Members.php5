<?php
class Mail_Manage_Project_Members extends Mail_Manage_Project_Abstract{

	protected function generate( $data = array() ){
		parent::generate( $data );
		$baseUrl	= $this->env->url;
		$config		= $this->env->getConfig();
		$words		= $this->getWords( 'manage/project' );
		$w			= (object) $words['mail-members'];
		$project	= $data['project'];

		$this->setSubject( sprintf( $w->subject, $project->title ) );

		$helperFacts	= $this->collectFacts( $project );

       //  --  RELATED ITEMS  --  //
        $relations      = '';
        $helperRelations    = new View_Helper_ItemRelationLister( $this->env );
        $helperRelations->callForRelations( 'Project', 'listRelations', array( 'projectId' => $project->projectId ) );
        if( $helperRelations->hasRelations() ){
            $relations  = $helperRelations->renderRelations();
            $relations  = UI_HTML_Tag::create( 'h4', $w->headingRelations ).$relations;
        }

		//  --  FORMAT: PLAIN TEXT  --  //
		$helperText	= new View_Helper_Mail_Text( $this->env );
$text	= $helperText->underscore( $config->get( 'app.name' ), '=' ).'

'.sprintf( $w->headingText, $project->title ).'

'.$helperText->underscore( $w->headingFacts ).'
'.$helperFacts->renderAsText();
		$this->addTextBody( $text );

		//  --  FORMAT: HTML  --  //
		$body	= '
<div class="container">
	<h2 class="muted">'.$config->get( 'app.name' ).'</h2>
	<div class="content-panel">
		<h3>'.sprintf( $w->headingHtml, $project->title ).'</h3>
		<div class="content-panel-inner">
			<h4>'.$w->headingFacts.'</h4>
			'.$helperFacts->render().'
			'.$relations.'
			</dl>
		</div>
	</div>
</div>';
		$this->page->addBody( $body );
		$this->addHtmlBody( $this->page->build() );
	}
}
?>