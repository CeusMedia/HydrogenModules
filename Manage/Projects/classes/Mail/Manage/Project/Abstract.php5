<?php
abstract class Mail_Manage_Project_Abstract extends Mail_Abstract{

	protected function generate( $data = array() ){
//		$this->addThemeStyle( $modules->has( 'UI_Bootstrap' ) ? 'bootstrap.min.css' : 'mail.min.css' );
/*		if( $this->env->getModules()->has( 'UI_CSS_Panel' ) ){
			$options	= $this->env->getConfig()->getAll( 'module.ui_css_panel.', TRUE );
			if( $options->get( 'enabled' ) ){
				$this->page->addBodyClass( 'content-panel-style-'.$options->get( 'style' ) );
				$this->addCommonStyle( 'layout.panels.css' );
			}
		}*/
	}

	protected function collectFacts( $project ){
		$view		= new CMF_Hydrogen_View( $this->env );
		$logic		= new Logic_Project( $this->env );
		$words		= $this->getWords( 'manage/project' );
		$members	= $logic->getProjectUsers( $project->projectId );

		$helperFacts	= new View_Helper_Mail_Facts();
		$helperFacts->setLabels( array_merge( $words['edit'], $words['mails'] ) );

		//  --  FACTS: TITLE, DESCRIPTION & LINK  --  //
		$helperFacts->add( 'title', $project->title );
		$helperFacts->add( 'description', $view->renderContent( $project->description ), $project->description );
		if( $project->url )
			$helperFacts->add( 'url', UI_HTML_Tag::create( 'a', $project->url, array(
				'href'		=> $project->url,
			) ) );

		//  --  FACTS: MEMBERS  --  //
		$list	= array();
		foreach( $members as $nr => $member ){
			$list[]	= $member->username;
			$members[$nr] = UI_HTML_Tag::create( 'li', $member->username );
		}
		$helperFacts->add( 'members', UI_HTML_Tag::create( 'ul', $members ), join( ", ", $list ) );

		//  --  FACTS: DATES & TIMES  --  //
		$time		= date( 'H:i:s', $project->createdAt );
		$date		= date( 'd.m.Y', $project->createdAt );
		$smallTime	= UI_HTML_Tag::create( 'small', '('.$time.')', array( 'class' => 'muted' ) );
		$helperFacts->add( 'createdAt', $date.' '.$smallTime );
		if( $project->modifiedAt ){
			$time		= date( 'H:i:s', $project->modifiedAt );
			$date		= date( 'd.m.Y', $project->modifiedAt );
			$smallTime	= UI_HTML_Tag::create( 'small', '('.$time.')', array( 'class' => 'muted' ) );
			$helperFacts->add( 'modifiedAt', $date.' '.$smallTime );
		}

		//  --  FACTS: PRIORITY & STATUS  --  //
		$smallPriority	= UI_HTML_Tag::create( 'small', '('.$project->priority.')', array( 'class' => 'muted' ) );
		$labelPriority	= $words['priorities'][$project->priority];
		$helperFacts->add( 'priority', $labelPriority.' '.$smallPriority );

		$direction		= NULL;
		$direction		= in_array( $project->status, array( 3 ) ) ? 1 : $direction;
		$direction		= $project->status < 0 ? -1 : $direction;
		$smallStatus	= UI_HTML_Tag::create( 'small', '('.$project->status.')', array( 'class' => 'muted' ) );
		$labelStatus	= $words['states'][$project->status];
		$textStatus		= $labelStatus.' ('.$project->status.')';
		$helperFacts->add( 'status', $labelStatus.' '.$smallStatus, NULL, $direction );

		return $helperFacts;
	}
}
?>
