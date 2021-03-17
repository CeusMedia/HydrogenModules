<?php
class View_Helper_NewsList implements Countable
{
	protected $env;
	protected $words;
	protected $news		= array();
	protected $limit	= 5;

	public static $defaultAttributes	= array(
		'title'		=> NULL,
		'timestamp'	=> NULL,
		'module'	=> NULL,
		'type'		=> NULL,
		'url'		=> NULL,
		'icon'		=> NULL,
	);


	public function __construct( CMF_Hydrogen_Environment $env )
	{
		$this->env		= $env;
		$this->words	= $env->getLanguage()->getWords( 'info/newslist' );
	}

	public function add( $item ): self
	{
		$this->news[]	= $item;
		return $this;
	}

	public function collect( $resource = 'Info_NewsList', $event = 'collectNews', $options = array() ): int
	{
		$this->env->getCaptain()->callHook( $resource, $event, $this, $options );
		return $this->count();
	}

	public function count(): int
	{
		return count( $this->news );
	}

	public function get(): array
	{
		return $this->news;
	}

	public function has(): bool
	{
		return (bool) $this->count();
	}

	public function render(): string
	{
		if( !$this->news )
			return '';
		$list	= array();
		foreach( $this->news as $item ){
			$helper		= new View_Helper_TimePhraser( $this->env );
			$key		= $item->timestamp.'.'.microtime( TRUE );
			$icon		= '';
			if( isset( $item->icon ) && strlen( $item->icon ) )
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $item->icon ) ).'&nbsp;';
			$link		= UI_HTML_Tag::create( 'a', $icon.$item->title, array( 'href' => $item->url ) );
			$date		= $helper->convert( $item->timestamp, TRUE );
			$type		= UI_HTML_Tag::create( 'small', $item->typeLabel, array( 'class' => "muted" ) );
			$list[$key]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $type.'<br/>'.$link, array( 'class' => 'autocut' ) ),
				UI_HTML_Tag::create( 'td', $date, array( 'style' => 'text-align: right' ) ),
			) );
			krsort( $list );
		}
		$list	= array_slice( $list, 0, $this->limit );
		$colgroup	= UI_HTML_Elements::ColumnGroup( "", "100" );
		$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'th', $this->words['panel']['headerTitle'] ),
			UI_HTML_Tag::create( 'th', $this->words['panel']['headerAge'], array( 'style' => 'text-align: right' ) ),
		) ) );
		$tbody	= UI_HTML_Tag::create( 'tbody', $list );
		$list	= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
			'class'		=> 'table table-striped table-fixed',
		) );
		return $list;
	}

	public function setLimit( int $limit ): self
	{
		$this->limit	= max( 0, $limit );
		return $this;
	}
}
