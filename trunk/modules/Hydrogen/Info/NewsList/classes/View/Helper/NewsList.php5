<?php
class View_Helper_NewsList implements Countable{

	public $news	= array();

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function add( $item ){
		$this->news[]	= $item;
	}

	public function collect( $resource = 'Info_NewsList', $event = 'collectNews', $options = array() ){
		$this->env->getCaptain()->callHook( $resource, $event, $this, $options );
		return $this->count();
	}

	public function count(){
		return count( $this->news );
	}

	public function has(){
		return (bool) $this->count();
	}

	public function render( $limit = 5 ){

		if( !$this->news )
			return '';
		$list	= array();
		foreach( $this->news as $item ){
			$helper		= new View_Helper_TimePhraser( $this->env );
			$key		= $item->timestamp.'.'.microtime( TRUE );
			$link		= UI_HTML_Tag::create( 'a', $item->title, array( 'href' => $item->url ) );
			$date		= $helper->convert( $item->timestamp, TRUE );
			$buttons	= array();
			$type		= '<small class="muted">'.$item->type.'</small>';
			$list[$key]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $type.'<br/>'.$link, array( 'class' => 'autocut' ) ),
				UI_HTML_Tag::create( 'td', $date ),
				UI_HTML_Tag::create( 'td', $buttons ),
			) );
			krsort( $list );
		}
		$list	= array_slice( $list, 0, 5 );
		$colgroup	= UI_HTML_Elements::ColumnGroup( "60%", "40%" );
		$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'Neuigkeit', 'Alter' ) ) );
		$tbody	= UI_HTML_Tag::create( 'tbody', $list );
		$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
			'class'		=> 'table table-striped',
			'style'		=> 'table-layout: fixed'
		) );
		return $list;
	}
}
?>
