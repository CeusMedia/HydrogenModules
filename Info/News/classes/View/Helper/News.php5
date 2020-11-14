<?php
class View_Helper_News{

	protected $limit	= 10;

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function render(){
		$config		= $this->env->getConfig()->getAll( 'module.info_news.', TRUE );
		$model		= new Model_News( $this->env );
		$words		= $this->env->getLanguage()->getWords( 'info/news' );
		$news		= $model->getAllByIndices(
			array( 'status' => 1 ),
			array( 'newsId' => 'DESC' )
		);
		$list	= array();
		foreach( $news as $item ){
			if( $item->startsAt && (int)time() < (int)$item->startsAt )
				continue;
			if( $item->endsAt && (int)time() > (int)$item->endsAt )
				continue;
			$list[]	= $item;
		}
		$list	= array_slice( $list, 0, $this->limit );

		foreach( $list as $nr => $item ){
			$date		= $item->createdAt ? date( "d.m.Y H:i", $item->createdAt ) : "";
			$list[$nr]	= '
<div class="news-list-entry">
	<h4 class="news-list-entry-title">'.$item->title.'</h4>
	<div class="news-list-entry-date">'.$date.'</div>
	<div class="news-list-entry-content col-'.$item->columns.'">'.$item->content.'</div>
</div>';
		}
		if( $list )
			return UI_HTML_Tag::create( 'div', join( $list ), array( 'class' => 'news-list' ) );
//		if( $showOnEmpty ){
//			return '<em><small class="muted">'.$words['index']['empty'].'</small></em>';
//		}
		return '';
	}

	public function setLimit( $limit ){
		$this->limit	= min( 100, max( 1, $limit ) );
	}
}
?>
