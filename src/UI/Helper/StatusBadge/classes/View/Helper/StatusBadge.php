<?php
declare(strict_types=1);

use CeusMedia\Bootstrap\Badge;
use CeusMedia\Common\Renderable;

class View_Helper_StatusBadge implements Renderable, Stringable
{
	const STATUS_NEUTRAL	= 0;
	const STATUS_POSITIVE	= 1;
	const STATUS_NEGATIVE	= 2;
	const STATUS_TRANS		= 3;

	const TYPE_BADGE		= 1;
	const TYPE_LABEL		= 2;
	const TYPES				= [
		self::TYPE_BADGE,
		self::TYPE_LABEL,
	];

	protected ?int $status	= NULL;
	protected int $type		= self::TYPE_BADGE;

	protected array $colorMap	= [
		self::TYPE_BADGE	=> [
			self::STATUS_NEUTRAL	=> Badge::CLASS_INFO,
			self::STATUS_POSITIVE	=> Badge::CLASS_SUCCESS,
			self::STATUS_NEGATIVE	=> Badge::CLASS_IMPORTANT,
			self::STATUS_TRANS		=> Badge::CLASS_WARNING,
		],
		self::TYPE_LABEL	=> [
			self::STATUS_NEUTRAL	=> Badge::CLASS_INFO,
			self::STATUS_POSITIVE	=> Badge::CLASS_SUCCESS,
			self::STATUS_NEGATIVE	=> Badge::CLASS_IMPORTANT,
			self::STATUS_TRANS		=> Badge::CLASS_WARNING,
		]
	];

	protected array $statusMap;

	protected array $labelMap;

	/**
	 *	Static constructor.
	 *	@param		int|NULL		$status
	 *	@param		array			$statusMap
	 *	@param		array			$labelMap
	 *	@return		self
	 */
	public static function create( ?int $status = NULL, array $statusMap = [], array $labelMap = [] ): self
	{
		return new self( $status, $statusMap, $labelMap );
	}

	/**
	 *	Constructor.
	 *	@param		int|NULL		$status
	 *	@param		array			$statusMap
	 *	@param		array			$labelMap
	 *	@return		void
	 */
	public function __construct( ?int $status = NULL, array $statusMap = [], array $labelMap = [] )
	{
		$this->status		= $status;
		$this->statusMap	= $statusMap;
		$this->labelMap		= $labelMap;
	}

	/**
	 *	@return		string
	 */
	public function __toString(): string
	{
		return $this->render();
	}

	/**
	 *	Renders HTML of component, needs status and status map and label map to be set before.
	 *	@return		string
	 *	@throws		RuntimeException	if missed to set status map, label map or status
	 *	@throws		RuntimeException
	 *	@throws		RuntimeException
	 */
	public function render(): string
	{
		if( NULL === $this->status )
			throw new RuntimeException( 'No status set' );
		if( [] == $this->statusMap )
			throw new RangeException( 'No status map set' );
		if( [] == $this->labelMap )
			throw new RuntimeException( 'No label map set' );

		$map	= array_flip( array_filter( $this->statusMap, function( $value ){
			return NULL !== $value;
		} ) );
		if( !array_key_exists( $this->status, $map ) )
			throw new RangeException( 'No status mapping available for this status' );
		$innerStatus	= $map[$this->status];
		$colorClass		= $this->colorMap[$this->type][$innerStatus];
		$colorClass		= ( self::TYPE_BADGE === $this->type ? 'badge ' : 'label ' ).$colorClass;

		if( !array_key_exists( $innerStatus, $this->labelMap ) )
			throw new RangeException( 'No label mapping available for this status' );
		$label	= $this->labelMap[$innerStatus];

		$badge	= new Badge( $label, $colorClass );
		return $badge->render();
	}

	/**
	 *	@param		array		$map
	 *	@return		self
	 */
	public function setColorMap( array $map ): self
	{
		$this->colorMap	= $map;
		return $this;
	}

	/**
	 *	Set label for each status as map of self::STATUS_* => [label].
	 *	@param		array		$map
	 *	@return		self
	 */
	public function setLabelMap( array $map ): self
	{
		$this->labelMap	= [
			self::STATUS_POSITIVE	=> $map[self::STATUS_POSITIVE] ?? NULL,
			self::STATUS_NEGATIVE	=> $map[self::STATUS_NEGATIVE] ?? NULL,
			self::STATUS_NEUTRAL	=> $map[self::STATUS_NEUTRAL] ?? NULL,
			self::STATUS_TRANS		=> $map[self::STATUS_TRANS] ?? NULL,
		];
		return $this;
	}

	/**
	 *	Sets your status, which will be translated into internal status, label and color during rendering.
	 *	@param		int		$status
	 *	@return		self
	 *	@throws		RangeException	if given status is not within status map (if map is already set)
	 */
	public function setStatus( int $status ): self
	{
		if( [] !== $this->statusMap ){
			if( !in_array( $status, $this->statusMap ) )
				throw new RangeException( 'Invalid status: '.$status );
		}
		$clone	= clone( $this );
		$clone->status	= $status;
		return $clone;
	}

	/**
	 *	Set your status for each internal status as map of self::STATUS_* => [your-status].
	 *	@param		array $map
	 *	@return		self
	 */
	public function setStatusMap( array $map ): self
	{
		$this->statusMap	= [
			self::STATUS_POSITIVE	=> $map[self::STATUS_POSITIVE] ?? NULL,
			self::STATUS_NEGATIVE	=> $map[self::STATUS_NEGATIVE] ?? NULL,
			self::STATUS_NEUTRAL	=> $map[self::STATUS_NEUTRAL] ?? NULL,
			self::STATUS_TRANS		=> $map[self::STATUS_TRANS] ?? NULL,
		];
		return $this;
	}

	/**
	 *	Set render type, either TYPE_BADGE or TYPE_LABEL.
	 *	Will decide which Bootstrap style is to be used during rendering.
	 *	@param		int		$type
	 *	@return		self
	 *	@throws		RangeException	if given type is neither TYPE_BADGE nor TYPE_LABEL
	 */
	public function setType( int $type ): self
	{
		if( !in_array( $type, self::TYPES, TRUE ) )
			throw new RangeException( 'Invalid type: '.$type );
		$this->type		= $type;
		return $this;
	}
}
