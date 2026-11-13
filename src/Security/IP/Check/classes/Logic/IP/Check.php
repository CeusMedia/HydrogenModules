<?php
declare(strict_types=1);

use CeusMedia\HydrogenFramework\Logic\Shared as SharedLogic;

/**
 * IP Check Logic — checks an IP address (IPv4 or IPv6) against known
 * blocklists (Spamhaus SBL/XBL/PBL/EHN, Barracuda BRBL, SpamCop SCBL, Abusix,
 * Invaluement, Sorbs DNSBL [deprecated]) and bogon ranges via parallel DNS-over-HTTPS
 * using curl_multi.
 *
 * Each blocklist is individually toggleable via the module config (`use.list.*` flags).
 *
 * @category    Library
 * @package     CeusMedia.HydrogenModules
 * @author      Christian Würker <christian.wuerker@ceusmedia.de>
 * @copyright   2010-2026 Christian Würker (ceusmedia.de)
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 * @link        https://github.com/CeusMedia/HydrogenModules
 */
class Logic_IP_Check extends SharedLogic
{
	/**
	 * DNS-based blocklist definitions: [config_key => [domain, description, penalty]].
	 * A DNS lookup that returns 127.x.y.z means the IP is listed.
	 *
	 * @var array<string, array{domain: string, description: string, penalty: int}>
	 */
	protected array $dnsBlocklists = [
		'spamhaus_sbl' => [
			'domain'      => 'sbl.spamhaus.org',
			'description' => 'Spamhaus SBL — Spam Block List',
			'penalty'     => -40,
		],
		'spamhaus_xbl' => [
			'domain'      => 'xbl.spamhaus.org',
			'description' => 'Spamhaus XBL — Exploits Block List',
			'penalty'     => -40,
		],
		'spamhaus_pbl' => [
			'domain'      => 'pbl.spamhaus.org',
			'description' => 'Spamhaus PBL — Policy Block List',
			'penalty'     => -30,
		],
		'spamhaus_ehn' => [
			'domain'      => 'ehn.spamhaus.org',
			'description' => 'Spamhaus EHN — Emerging Hosts Network',
			'penalty'     => -20,
		],
		'barracuda' => [
			'domain'      => 'b.barracudacentral.org',
			'description' => 'Barracuda BRBL — Reputation Block List',
			'penalty'     => -40,
		],
		'spamcop' => [
			'domain'      => 'bl.spamcop.net',
			'description' => 'SpamCop SCBL — SpamCop Block List',
			'penalty'     => -40,
		],
		'abusix' => [
			'domain'      => 'dnsbl.abusix.zone',
			'description' => 'Abusix Mail Intelligence DNSBL',
			'penalty'     => -40,
		],
		'invaluement' => [
			'domain'      => 'dnsbl.invaluement.com',
			'description' => 'Invaluement DNSBL',
			'penalty'     => -40,
		],
		'sorbs' => [
			'domain'      => 'dnsbl.sorbs.net',
			'description' => 'Sorbs DNSBL (deprecated — service reportedly shutting down)',
			'penalty'     => -50,
		],
	];

	/**
	 * Simple in-memory cache for DNS lookups.
	 *
	 * @var array<string, array{result: bool, time: int}>
	 */
	protected array $dnsCache = [];

	/**
	 * Cache TTL in seconds.
	 */
	protected int $cacheTtl = 3600;

	/**
	 * Which blocklists are enabled (read from config).
	 *
	 * @var array<string, bool>
	 */
	protected array $enabledLists = [];

	/**
	 * Whether to skip all checks for local/private (bogon) IPs.
	 * When enabled, bogon IPs return immediately with clean:true (for local development).
	 * When disabled (default), bogon IPs are penalized with score -50 (detects IP spoofing).
	 *
	 * @var bool
	 */
	protected bool $skipLocalIps = FALSE;

	/**
	 * Whether logging of rejected IPs is enabled.
	 *
	 * @var bool
	 */
	protected bool $logRejected = TRUE;

	/**
	 * Whether to include POST data in rejected IP logs.
	 *
	 * @var bool
	 */
	protected bool $logPostData = TRUE;

	/**
	 * Whether to include request headers in rejected IP logs.
	 *
	 * @var bool
	 */
	protected bool $logHeaders = TRUE;

	/**
	 * Check an IP address (IPv4 or IPv6) against all enabled blocklists.
	 *
	 * DNS-based blocklists are queried in parallel using curl_multi with DNS-over-HTTPS.
	 *
	 * The bogon check serves two purposes:
	 * 1. For local development: when skip_local_ips is enabled, bogon IPs (127.0.0.1, 192.168.x.x, etc.)
	 *    are skipped entirely to allow testing without DNSBL lookups.
	 * 2. For production: a bogon IP in the request means IP spoofing (e.g. manipulated X-Forwarded-For
	 *    header behind a proxy) — it is penalized with score -50.
	 *
	 *	@param		string		$ip			The IP address to check
	 *	@return		Entity_IP_Check_Result
	 */
	public function checkIp( string $ip ): Entity_IP_Check_Result
	{
		$ipVersion	= $this->getIpVersion( $ip );
		$sources	= [];
		$score		= 0;

		// Bogon detection always runs internally (independent of use.list.bogons config)
		// to support skip_local_ips: a bogon IP should never arrive from the internet
		// because routers drop unroutable addresses. If one does arrive, the source IP was
		// likely manipulated (e.g. via X-Forwarded-For header behind a proxy).
		$isBogonIp	= $this->isBogon( $ip, $ipVersion );
		if( $isBogonIp['listed'] && $this->skipLocalIps ){
			// Local development mode: skip all checks for bogon IPs
			$sources['bogons'] = Entity_IP_Check_Source::fromArray( [
				'listed'		=> FALSE,
				'description'	=> 'Skipped — local IP address (' . $isBogonIp['description'] . ')',
			] );
			return Entity_IP_Check_Result::fromArray( [
				'ip'		=> $ip,
				'ipVersion'	=> $ipVersion,
				'checked'	=> FALSE,
				'clean'		=> TRUE,
				'score'		=> 0,
				'sources'	=> $sources,
			] );
		}

		// The bogon check in the results is only included when use.list.bogons is enabled.
		if( !empty( $this->enabledLists['bogons'] ) ){
			if( $isBogonIp['listed'] ){
				// Production mode: bogon IP detected — penalize as potential spoofing
				$sources['bogons'] = Entity_IP_Check_Source::fromArray( [
					'listed'		=> TRUE,
					'description'	=> $isBogonIp['description'],
				] );
				$score += -50;
			}
			else{
				// IP is public — bogon check passed, no spoofing detected
				$sources['bogons'] = Entity_IP_Check_Source::fromArray( [
					'listed'		=> FALSE,
					'description'	=> 'Bogon addresses',
				] );
			}
		}

		// Collect all enabled DNS-based blocklist queries
		$queries	= [];
		foreach( $this->dnsBlocklists as $configKey => $config ){
			if( empty( $this->enabledLists[$configKey] ) ){
				continue;
			}

			$query	= $this->buildDnsQuery( $ip, $ipVersion, $config['domain'] );
			if( '' !== $query ){
				$queries[$configKey] = Entity_IP_Check_Query::fromArray( [
					'query'		=> $query,
					'domain'	=> $config['domain'],
					'config'	=> $config,
				] );
			}
		}

		// Perform parallel DNS lookups via curl_multi (DNS-over-HTTPS)
		$results	= $this->dnsLookupParallel( $queries );

		// Process results
		foreach( $queries as $configKey => $queryInfo ){
			$cacheKey	= $queryInfo['query'] . '.' . $queryInfo['domain'];
			$listed		= $results[$configKey] ?? FALSE;
			$this->dnsCache[$cacheKey] = ['result' => $listed, 'time' => time()];

			$sources[$configKey] = Entity_IP_Check_Source::fromArray( [
				'listed'		=> $listed,
				'description'	=> $queryInfo['config']['description'],
			] );
			if( $listed ){
				$score += $queryInfo['config']['penalty'];
			}
		}

		// Track whether at least one list was actually checked
		$checked	= !empty( $this->enabledLists['bogons'] ) ||
			count( array_filter( array_intersect_key( $this->enabledLists, $this->dnsBlocklists ) ) ) > 0;

		return Entity_IP_Check_Result::fromArray( [
			'ip'		=> $ip,
			'ipVersion'	=> $ipVersion,
			'checked'	=> $checked,
			'clean'		=> $score >= 0,
			'score'		=> $score,
			'sources'	=> $sources,
		] );
	}

	/**
	 *	Log a rejected IP to the database via Model/Entity.
	 *
	 *	@param		string					$ip				The rejected IP address
	 *	@param		Entity_IP_Check_Result	$result			The checkIp() result
	 *	@param		string|NULL				$requestMethod	HTTP method (GET, POST, etc.)
	 *	@param		string|NULL				$requestUri		Request URI
	 *	@param		array|NULL				$postData		POST body data (only if log.post_data is enabled)
	 *	@param		array|NULL				$headers		Request headers (only if log.headers is enabled)
	 *	@return		bool					TRUE if logged successfully
	 *	@throws		JsonException
	 */
	public function logRejectedIp(
		string $ip,
		Entity_IP_Check_Result $result,
		?string $requestMethod = NULL,
		?string $requestUri = NULL,
		?array $postData = NULL,
		?array $headers = NULL
	): bool
	{
		if( FALSE === $this->logRejected ){
			return FALSE;
		}

		if( NULL === $this->env->getDatabase() ){
			return FALSE;
		}

		// Collect reasons: only sources that flagged the IP
		$reasons	= [];
		foreach( $result->sources as $sourceKey => $source ){
			if( !empty( $source->listed ) ){
				$reasons[]	= $sourceKey;
			}
		}

		// Build entity data array
		$data	= [
			'createdAt'		=> date( 'Y-m-d H:i:s' ),
			'ip'			=> $ip,
			'ipVersion'		=> $result->ipVersion,
			'score'			=> $result->score,
			'reasons'		=> json_encode( $reasons, JSON_THROW_ON_ERROR ),
			'requestMethod'	=> $requestMethod ?: 'UNKNOWN',
			'requestUri'	=> $requestUri ?: 'UNKNOWN',
		];

		if( $this->logHeaders && NULL !== $headers ){
			$data['requestHeaders'] = json_encode( $headers, JSON_THROW_ON_ERROR );
		}

		if( $this->logPostData && NULL !== $postData ){
			$data['postData'] = json_encode( $postData, JSON_THROW_ON_ERROR );
		}

		try{
			Model_Log_API_Reject::getInstance( $this->env )
				->add( Entity_Log_API_Reject::fromArray( $data ) );
			return TRUE;
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e, 'Failed to log rejected IP: ' . $e->getMessage() );
			return FALSE;
		}
	}

	public function logRequestOfRejectedIp( Entity_IP_Check_Result $result ): bool
	{
		// Collect POST data if available
		$request	= $this->env->getRequest();
		$method		= (string) $request->getMethod();
		$postData	= NULL;
		if( in_array( $method, ['POST', 'PUT', 'PATCH'], TRUE ) ){
			$body	= $request->getRawPostData();
			if( '' !== $body ){
				$decoded	= json_decode( $body, TRUE );
				$postData	= is_array( $decoded ) ? $decoded : [ 'raw' => $body ];
			}
		}

		// Collect relevant headers
		$headers	= [
			'user_agent'		=> (string) $request->get( 'HTTP_USER_AGENT', '' ),
			'accept_language'	=> (string) $request->get( 'HTTP_ACCEPT_LANGUAGE', '' ),
			'referer'			=> (string) $request->get( 'HTTP_REFERER', '' ),
		];

		return $this->logRejectedIp(
			$request->getIp(),
			$result,
			$method,
			(string) $request->getUrl( TRUE ),
			$postData,
			$headers
		);
	}

	protected function __onInit(): void
	{
		$moduleConfig	= $this->env->getModules()->get( 'Security_IP_Check' )->getConfigAsDictionary();
		$this->cacheTtl	= (int) $moduleConfig->get( 'cache_ttl', 3600 );

		// Read enabled blocklist flags from config
		$lists	= $moduleConfig->getAll( 'use.list.', TRUE );
		foreach( $lists->getKeys() as $key )
			$this->enabledLists[$key]	= filter_var( $lists->get( $key, 'yes' ), FILTER_VALIDATE_BOOLEAN );

		// Read skip_local_ips flag for local development mode
		$this->skipLocalIps	= filter_var( $moduleConfig->get( 'skip_local_ips', 'no' ), FILTER_VALIDATE_BOOLEAN );

		// Read logging config flags
		$logs	= $moduleConfig->getAll( 'log.', TRUE );
		$this->logRejected	= filter_var( $logs->get( 'rejected', 'yes' ), FILTER_VALIDATE_BOOLEAN );
		$this->logPostData	= filter_var( $logs->get( 'post_data', 'yes' ), FILTER_VALIDATE_BOOLEAN );
		$this->logHeaders	= filter_var( $logs->get( 'headers', 'yes' ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Build the DNS query string for a given IP and blocklist domain.
	 *
	 * For IPv4: reverses the octets (1.2.3.4 → 4.3.2.1.domain).
	 * For IPv6: expands to full hex and reverses nibbles (2001:db8::1 → 1.0.0.0...ip6.domain).
	 */
	protected function buildDnsQuery( string $ip, int $ipVersion, string $domain ): string
	{
		if( 4 === $ipVersion ){
			$octets		= explode( '.', $ip );
			$reversedIp	= implode( '.', array_reverse( $octets ) );
			return $reversedIp . '.' . $domain;
		}

		// IPv6: expand to full 32-hex-char string and reverse as nibbles
		$expanded	= $this->expandIpv6( $ip );
		if( '' === $expanded )
			return '';

		return strrev( $expanded ) . '.ip6.' . $domain;
	}

	/**
	 * Perform parallel DNS lookups using curl_multi with DNS-over-HTTPS.
	 *
	 * Queries are sent to Cloudflare's DNS-over-HTTPS endpoint (1.1.1.1/dns-query)
	 * and executed in parallel. This reduces the total lookup time from the sum of
	 * all individual lookups to the time of the slowest single lookup.
	 *
	 * @param array<string, Entity_IP_Check_Query> $queries
	 *        Associative array of configKey => query info
	 * @return array<string, bool> Associative array of configKey => isListed
	 */
	protected function dnsLookupParallel( array $queries ): array
	{
		$results	= [];
		if( 0 === count( $queries ) )
			return $results;

		// Check cache first — collect queries that need actual DNS lookup
		$uncached	= [];
		foreach( $queries as $configKey => $queryInfo ){
			$cacheKey	= $queryInfo->query . '.' . $queryInfo->domain;
			if( isset( $this->dnsCache[$cacheKey] ) ){
				$cacheEntry	= $this->dnsCache[$cacheKey];
				if( ( time() - $cacheEntry['time'] ) < $this->cacheTtl ){
					$results[$configKey] = $cacheEntry['result'];
					continue;
				}
			}
			$uncached[$configKey] = $queryInfo;
		}

		// If all queries are cached, return early
		if( 0 === count( $uncached ) )
			return $results;

		// Perform parallel DNS-over-HTTPS lookups via curl_multi
		$mh			= curl_multi_init();
		$handleMap	= []; // curl_handle => configKey

		foreach( $uncached as $configKey => $queryInfo ){
			$ch	= curl_init();
			curl_setopt_array( $ch, [
				CURLOPT_URL            => 'https://1.1.1.1/dns-query?name=' . urlencode( $queryInfo['query'] ) . '&type=A',
				CURLOPT_HTTPHEADER     => ['Accept: application/dns-json'],
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_TIMEOUT        => 5,
				CURLOPT_CONNECTTIMEOUT => 2,
				CURLOPT_DNS_USE_GLOBAL_CACHE => FALSE,
			] );
			curl_multi_add_handle( $mh, $ch );
			$handleMap[spl_object_id( $ch )] = ['handle' => $ch, 'configKey' => $configKey];
		}

		// Execute all handles in parallel
		$running	= 0;
		try{
			do{
				curl_multi_exec( $mh, $running );
				curl_multi_select( $mh, 1.0 );
			} while( $running > 0 );
		}
		catch( Throwable $e ){
			$this->env->getLog()->logException( $e, 'DNS parallel lookup failed' );
		}

		// Collect results
		foreach( $handleMap as $objectId => $info ){
			$ch			= $info['handle'];
			$configKey	= $info['configKey'];
			$content	= curl_multi_getcontent( $ch );
			curl_multi_remove_handle( $mh, $ch );
			curl_close( $ch );

			$isListed	= FALSE;
			if( FALSE !== $content ){
				$data	= json_decode( $content, TRUE );
				if( is_array( $data ) && isset( $data['Answer'] ) ){
					foreach( $data['Answer'] as $answer ){
						if( isset( $answer['data'] ) && str_starts_with( $answer['data'], '127.' ) ){
							$isListed	= TRUE;
							break;
						}
					}
				}
			}

			$results[$configKey] = $isListed;
		}

		curl_multi_close( $mh );
		return $results;
	}

	/**
	 * Expand a compressed IPv6 address to its full 32-hex-character form.
	 * e.g. "2001:db8::1" → "20010000000000000000000000000001"
	 */
	protected function expandIpv6( string $ip ): string
	{
		$packed	= inet_pton( $ip );
		if( FALSE === $packed )
			return '';

		// Convert each byte to 2-hex-char and concatenate
		return bin2hex( $packed );
	}

	/**
	 * Determine the IP version (4 or 6).
	 */
	protected function getIpVersion( string $ip ): int
	{
		$flags		= FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
		$filterResult	= filter_var( $ip, FILTER_VALIDATE_IP, $flags );
		if( FALSE === $filterResult )
			return 0; // invalid

		return FALSE !== filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ? 4 : 6;
	}

	/**
	 * Check whether an IP falls into a bogon (reserved/private) range.
	 * Supports both IPv4 and IPv6.
	 */
	protected function isBogon( string $ip, int $ipVersion ): Entity_IP_Check_Source
	{
		if( 4 === $ipVersion )
			return $this->isBogonIpv4( $ip );

		return $this->isBogonIpv6( $ip );
	}

	/**
	 * Check IPv4 bogon ranges.
	 * @param string $ip
	 * @return Entity_IP_Check_Source
	 */
	protected function isBogonIpv4( string $ip ): Entity_IP_Check_Source
	{
		$octets	= explode( '.', $ip );
		if( 4 !== count( $octets ) )
			return Entity_IP_Check_Source::fromArray( [
				'listed'		=> FALSE,
				'description'	=> 'Bogon addresses'
			] );

		$first	= (int) $octets[0];
		$second	= (int) $octets[1];
		$third	= (int) $octets[2];

		// 0.0.0.0/8
		if( 0 === $first )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '0.0.0.0/8 — IETF Protocol Assignment'
			] );

		// 10.0.0.0/8
		if( 10 === $first )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '10.0.0.0/8 — Private (RFC 1918)'
			] );

		// 100.64.0.0/10 (100.64.0.0 - 100.127.255.255)
		if( 100 === $first && $second >= 64 && $second <= 127 )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '100.64.0.0/10 — Carrier-grade NAT (RFC 6598)'
			] );

		// 127.0.0.0/8
		if( 127 === $first )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '127.0.0.0/8 — Loopback'
			] );

		// 169.254.0.0/16
		if( 169 === $first && 254 === $second )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '169.254.0.0/16 — Link-local (RFC 3927)'
			] );

		// 172.16.0.0/12 (172.16.0.0 - 172.31.255.255)
		if( 172 === $first && $second >= 16 && $second <= 31 )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '172.16.0.0/12 — Private (RFC 1918)'
			] );

		// 192.0.2.0/24 (TEST-NET-1) — must come before 192.0.0.0/24
		if( 192 === $first && 0 === $second && 2 === $third )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '192.0.2.0/24 — TEST-NET-1 (RFC 5737)'
			] );

		// 192.0.0.0/24
		if( 192 === $first && 0 === $second )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '192.0.0.0/24 — IETF Protocol Assignment'
			] );

		// 192.88.99.0/24 (6to4 Relay)
		if( 192 === $first && 88 === $second && 99 === $third )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '192.88.99.0/24 — 6to4 Relay (RFC 3068)'
			] );

		// 192.168.0.0/16
		if( 192 === $first && 168 === $second )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '192.168.0.0/16 — Private (RFC 1918)'
			] );

		// 192.175.48.0/24 (AS112-v4)
		if( 192 === $first && 175 === $second && 48 === $third )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '192.175.48.0/24 — AS112-v4 (RFC 7450)'
			] );

		// 198.18.0.0/15 — Benchmarking (RFC 2544) — covers 198.18.0.0 – 198.19.255.255
		if( 198 === $first && $second >= 18 && $second <= 19 )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '198.18.0.0/15 — Benchmarking (RFC 2544)'
			] );

		// 198.51.100.0/24 (TEST-NET-2)
		if( 198 === $first && 51 === $second && 100 === $third )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '198.51.100.0/24 — TEST-NET-2 (RFC 5737)'
			] );

		// 203.0.113.0/24 (TEST-NET-3)
		if( 203 === $first && 0 === $second && 113 === $third )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '203.0.113.0/24 — TEST-NET-3 (RFC 5737)'
			] );

		// 224.0.0.0/4 (Multicast: 224-239)
		if( $first >= 224 && $first <= 239 )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '224.0.0.0/4 — Multicast (RFC 5771)'
			] );

		// 240.0.0.0/4 (Reserved: 240-255)
		if( $first >= 240 )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '240.0.0.0/4 — Reserved (RFC 1122)'
			] );

		return Entity_IP_Check_Source::fromArray( [
			'listed' => FALSE,
			'description' => 'Bogon addresses'
		] );
	}

	/**
	 * Check IPv6 bogon (reserved/private) ranges.
	 * Covers: ::1 (loopback), ::/96 (IPv4-mapped), fe80::/10 (link-local),
	 * fc00::/7 (unique local), 2001:db8::/32 (documentation), :: (unspecified),
	 * 2001:20::/28 (ORCHIDv2), 2002::/16 (6to4), 2001::/32 (TEREDO), ff00::/8 (multicast).
	 */
	protected function isBogonIpv6( string $ip ): Entity_IP_Check_Source
	{
		$packed	= inet_pton( $ip );
		if( FALSE === $packed )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => FALSE,
				'description' => 'Bogon addresses (IPv6)'
			] );

		// inet_pton returns 4 bytes for IPv4-mapped IPv6 addresses (::ffff:x.x.x.x)
		// These are always in the ::/96 bogon range
		if( 4 === strlen( $packed ) )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '::/96 — IPv4-mapped prefix (RFC 4291)'
			] );

		// Convert to a 128-bit integer representation for range checks.
		// PHP integers on 64-bit can handle the high 64 bits directly.
		$high	= (int) unpack( 'N', substr( $packed, 0, 4 ) )[1]; // signed, but we only need prefix matches
		$low	= (int) unpack( 'N', substr( $packed, 4, 4 ) )[1];

		// ::1/128 — Loopback (all zeros except last byte = 1)
		// Must check all 16 bytes, not just high/low, to avoid matching ::ffff:x.x.x.1
		if( 0 === $high && 0 === $low ){
			// All first 8 bytes are zero (high=0, low=0 confirms first 8 bytes)
			// Now check bytes 8-14 are also zero
			$allZeroExceptLast	= TRUE;
			for( $i = 8; $i < 15; $i++ ){
				if( 0 !== ord( $packed[$i] ) ){
					$allZeroExceptLast	= FALSE;
					break;
				}
			}
			if( $allZeroExceptLast && 1 === ord( $packed[15] ) )
				return Entity_IP_Check_Source::fromArray( [
					'listed' => TRUE,
					'description' => '::1/128 — Loopback (RFC 4291)'
				] );

			// ::/128 — Unspecified (all 16 bytes are zero)
			if( $allZeroExceptLast && 0 === ord( $packed[15] ) )
				return Entity_IP_Check_Source::fromArray( [
					'listed' => TRUE,
					'description' => '::/128 — Unspecified (RFC 4291)'
				] );

			// ::/96 — IPv4-compatible/mapped prefix (first 8 bytes zero, remaining non-zero)
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '::/96 — IPv4-mapped prefix (RFC 4291)'
			] );
		}

		// 2001:db8::/32 — Documentation (RFC 3849)
		// 2001:0db8 = 0x20010db8
		if( 0x20010db8 === $high )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '2001:db8::/32 — Documentation (RFC 3849)'
			] );

		// 2002::/16 — 6to4 (RFC 3056)
		// 0x20020000
		if( 0x20020000 === ( $high & 0xffff0000 ) )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '2002::/16 — 6to4 (RFC 3056)'
			] );

		// 2001::/32 — TEREDO prefix (RFC 4380)
		if( 0x20010000 === $high )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '2001::/32 — TEREDO (RFC 4380)'
			] );

		// 2001:20::/28 — ORCHIDv2 (RFC 7343)
		if( 0x20010020 === ( $high & 0xfffffff0 ) )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => '2001:20::/28 — ORCHIDv2 (RFC 7343)'
			] );

		// fe80::/10 — Link-local (RFC 4291)
		// fe80 = 0xfe80, /10 means first 10 bits: 0xfe00 - 0xfeff in high byte range
		if( 0xfe800000 === ( $high & 0xffc00000 ) )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => 'fe80::/10 — Link-local (RFC 4291)'
			] );

		// fc00::/7 — Unique local (RFC 4193)
		// fc00-ffff, /7 means first 7 bits = 0xfc - 0xff
		if( 0xfc000000 === ( $high & 0xfe000000 ) )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => 'fc00::/7 — Unique local (RFC 4193)'
			] );

		// ff00::/8 — Multicast (RFC 4291)
		if( 0xff000000 === ( $high & 0xff000000 ) )
			return Entity_IP_Check_Source::fromArray( [
				'listed' => TRUE,
				'description' => 'ff00::/8 — Multicast (RFC 4291)'
			] );

		return Entity_IP_Check_Source::fromArray( [
			'listed' => FALSE,
			'description' => 'Bogon addresses (IPv6)'
		] );
	}
}
