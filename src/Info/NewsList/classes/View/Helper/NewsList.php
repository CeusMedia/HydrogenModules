<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_NewsList implements Countable
{
	protected $env;
	protected $words;
	protected $news		= [];
	protected $limit	= 5;

	public static $defaultAttributes	= array(
		'title'		=> NULL,
		'timestamp'	=> NULL,
		'module'	=> NULL,
		'type'		=> NULL,
		'url'		=> NULL,
		'icon'		=> NULL,
	);


	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->words	= $env->getLanguage()->getWords( 'info/newslist' );
	}

	public function add( $item ): self
	{
		$this->news[]	= $item;
		return $this;
	}

	public function collect( $resource = 'Info_NewsList', $event = 'collectNews', $options = [] ): int
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
		$list	= [];
		foreach( $this->news as $item ){
			$helper		= new View_Helper_TimePhraser( $this->env );
			$key		= $item->timestamp.'.'.microtime( TRUE );
			$icon		= '';
			if( isset( $item->icon ) && strlen( $item->icon ) )
				$icon	= HtmlTag::create( 'i', '', ['class' => $item->icon] ).'&nbsp;';
			$link		= HtmlTag::create( 'a', $icon.$item->title, ['href' => $item->url] );
			$date		= $helper->convert( $item->timestamp, TRUE );
			$type		= HtmlTag::create( 'small', $item->typeLabel, ['class' => "muted"] );
			$list[$key]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $type.'<br/>'.$link, ['class' => 'autocut'] ),
				HtmlTag::create( 'td', $date, ['style' => 'text-align: right'] ),
			) );
			krsort( $list );
		}
		$list	= array_slice( $list, 0, $this->limit );
		$colgroup	= HtmlElements::ColumnGroup( "", "100" );
		$thead	= HtmlTag::create( 'thead', HtmlTag::create( 'tr', array(
			HtmlTag::create( 'th', $this->words['panel']['headerTitle'] ),
			HtmlTag::create( 'th', $this->words['panel']['headerAge'], ['style' => 'text-align: right'] ),
		) ) );
		$tbody	= HtmlTag::create( 'tbody', $list );
		$list	= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array(
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
