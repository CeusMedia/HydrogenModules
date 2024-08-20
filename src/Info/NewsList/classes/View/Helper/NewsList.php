<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_NewsList implements Countable
{
	protected Environment $env;
	protected array $words;
	protected array $news		= [];
	protected int $limit		= 5;

	public static array $defaultAttributes	= [
		'title'		=> NULL,
		'timestamp'	=> NULL,
		'module'	=> NULL,
		'type'		=> NULL,
		'url'		=> NULL,
		'icon'		=> NULL,
	];

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env		= $env;
		$this->words	= $env->getLanguage()->getWords( 'info/newslist' );
	}

	/**
	 *	@param		object		$item
	 *	@return		self
	 */
	public function add( object $item ): self
	{
		$this->news[]	= $item;
		return $this;
	}

	/**
	 *	@param		string		$resource
	 *	@param		string		$event
	 *	@param		array		$options
	 *	@return		int
	 *	@throws		ReflectionException
	 */
	public function collect( string $resource = 'Info_NewsList', string $event = 'collectNews', array $options = [] ): int
	{
		$this->env->getCaptain()->callHook( $resource, $event, $this, $options );
		return $this->count();
	}

	/**
	 *	@return		int
	 */
	public function count(): int
	{
		return count( $this->news );
	}

	/**
	 *	@return		array
	 */
	public function get(): array
	{
		return $this->news;
	}

	/**
	 *	@return		bool
	 */
	public function has(): bool
	{
		return (bool) $this->count();
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		if( !$this->news )
			return '';
		$list	= [];
		foreach( $this->news as $item ){
			$helper		= new View_Helper_TimePhraser( $this->env );
			$key		= $item->timestamp.'.'.microtime( TRUE );
			$icon		= '';
			if( isset( $item->icon ) && '' !== trim( $item->icon ) )
				$icon	= HtmlTag::create( 'i', '', ['class' => $item->icon] ).'&nbsp;';
			$link		= HtmlTag::create( 'a', $icon.$item->title, ['href' => $item->url] );
			$date		= $helper->convert( $item->timestamp, TRUE );
			$type		= HtmlTag::create( 'small', $item->typeLabel, ['class' => 'muted'] );
			$list[$key]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $type.'<br/>'.$link, ['class' => 'autocut'] ),
				HtmlTag::create( 'td', $date, ['style' => 'text-align: right'] ),
			] );
			krsort( $list );
		}
		return HtmlTag::create( 'table', [
			HtmlElements::ColumnGroup( '', '100' ),
			HtmlTag::create( 'thead', HtmlTag::create( 'tr', [
				HtmlTag::create( 'th', $this->words['panel']['headerTitle'] ),
				HtmlTag::create( 'th', $this->words['panel']['headerAge'], ['style' => 'text-align: right'] ),
			] ) ),
			HtmlTag::create( 'tbody', array_slice( $list, 0, $this->limit ) )
		], ['class' => 'table table-striped table-fixed'] );
	}

	/**
	 *	@param		int		$limit
	 *	@return		self
	 */
	public function setLimit( int $limit ): self
	{
		$this->limit	= max( 0, $limit );
		return $this;
	}
}
