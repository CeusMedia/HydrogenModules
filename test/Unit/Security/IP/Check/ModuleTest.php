<?php
namespace CeusMedia\HydrogenModulesUnitTest\Security\IP\Check;

use CeusMedia\HydrogenModulesUnitTest\BaseTestCase;
use Logic_IP_Check;
use ReflectionClass;

/**
 * Unit tests for the Security/IP/Check module.
 *
 * Tests cover: IP version detection, bogon checks (IPv4 & IPv6),
 * IPv6 expansion, DNS cache behavior, and the full checkIp() flow.
 */
class ModuleTest extends BaseTestCase
{
	/** @var Logic_IP_Check */
	protected $logic;

	protected function setUp(): void
	{
		parent::setUp();
		$this->installModule('Security:IP:Check');
		$env		= $this->createEnvironment();
		$this->logic	= Logic_IP_Check::getInstance($env);
	}

	// -------------------------------------------------------
	//  getIpVersion() tests
	// -------------------------------------------------------

	public function testGetIpVersion_validIpv4(): void
	{
		$version	= $this->callProtectedMethod($this->logic, 'getIpVersion', ['8.8.8.8']);
		self::assertSame(4, $version);
	}

	public function testGetIpVersion_validIpv6(): void
	{
		$version	= $this->callProtectedMethod($this->logic, 'getIpVersion', ['2001:db8::1']);
		self::assertSame(6, $version);
	}

	public function testGetIpVersion_validIpv6_full(): void
	{
		$version	= $this->callProtectedMethod($this->logic, 'getIpVersion', ['2001:0db8:0000:0000:0000:0000:0000:0001']);
		self::assertSame(6, $version);
	}

	public function testGetIpVersion_validIpv6_loopback(): void
	{
		$version	= $this->callProtectedMethod($this->logic, 'getIpVersion', ['::1']);
		self::assertSame(6, $version);
	}

	public function testGetIpVersion_invalid(): void
	{
		$version	= $this->callProtectedMethod($this->logic, 'getIpVersion', ['not-an-ip']);
		self::assertSame(0, $version);
	}

	public function testGetIpVersion_empty(): void
	{
		$version	= $this->callProtectedMethod($this->logic, 'getIpVersion', ['']);
		self::assertSame(0, $version);
	}

	// -------------------------------------------------------
	//  isBogonIpv4() tests
	// -------------------------------------------------------

	public function testIsBogonIpv4_private_10_x_x_x(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['10.0.0.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('10.0.0.0/8', $result['description']);
	}

	public function testIsBogonIpv4_private_172_16_x_x(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['172.16.0.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('172.16.0.0/12', $result['description']);
	}

	public function testIsBogonIpv4_private_172_31_x_x(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['172.31.255.255']);
		self::assertTrue($result['listed']);
	}

	public function testIsBogonIpv4_private_172_32_not_bogon(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['172.32.0.1']);
		self::assertFalse($result['listed']);
	}

	public function testIsBogonIpv4_private_192_168_x_x(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['192.168.1.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('192.168.0.0/16', $result['description']);
	}

	public function testIsBogonIpv4_loopback(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['127.0.0.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('127.0.0.0/8', $result['description']);
	}

	public function testIsBogonIpv4_zero_network(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['0.0.0.0']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('0.0.0.0/8', $result['description']);
	}

	public function testIsBogonIpv4_link_local(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['169.254.1.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('169.254.0.0/16', $result['description']);
	}

	public function testIsBogonIpv4_carrier_nat(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['100.64.0.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('100.64.0.0/10', $result['description']);
	}

	public function testIsBogonIpv4_carrier_nat_upper(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['100.127.255.255']);
		self::assertTrue($result['listed']);
	}

	public function testIsBogonIpv4_carrier_nat_outside(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['100.128.0.1']);
		self::assertFalse($result['listed']);
	}

	public function testIsBogonIpv4_test_net_1(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['192.0.2.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('TEST-NET-1', $result['description']);
	}

	public function testIsBogonIpv4_test_net_2(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['198.51.100.5']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('TEST-NET-2', $result['description']);
	}

	public function testIsBogonIpv4_test_net_3(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['203.0.113.255']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('TEST-NET-3', $result['description']);
	}

	public function testIsBogonIpv4_multicast(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['224.0.0.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('Multicast', $result['description']);
	}

	public function testIsBogonIpv4_multicast_upper(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['239.255.255.255']);
		self::assertTrue($result['listed']);
	}

	public function testIsBogonIpv4_reserved(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['240.0.0.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('Reserved', $result['description']);
	}

	public function testIsBogonIpv4_reserved_max(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['255.255.255.255']);
		self::assertTrue($result['listed']);
	}

	public function testIsBogonIpv4_6to4_relay(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['192.88.99.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('6to4 Relay', $result['description']);
	}

	public function testIsBogonIpv4_as112(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['192.175.48.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('AS112', $result['description']);
	}

	public function testIsBogonIpv4_benchmarking_198_18(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['198.18.0.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('198.18.0.0/15', $result['description']);
		self::assertStringContainsString('Benchmarking', $result['description']);
	}

	public function testIsBogonIpv4_benchmarking_198_19(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['198.19.0.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('198.18.0.0/15', $result['description']);
		self::assertStringContainsString('Benchmarking', $result['description']);
	}

	public function testIsBogonIpv4_benchmarking_outside_range(): void
	{
		// 198.0.0.1 is not in the 198.18.0.0/15 benchmarking range
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['198.0.0.1']);
		self::assertFalse($result['listed']);
	}

	public function testIsBogonIpv4_ietf_protocol(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['192.0.0.9']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('IETF Protocol Assignment', $result['description']);
	}

	public function testIsBogonIpv4_public_google_dns(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['8.8.8.8']);
		self::assertFalse($result['listed']);
	}

	public function testIsBogonIpv4_public_cloudflare(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['1.1.1.1']);
		self::assertFalse($result['listed']);
	}

	public function testIsBogonIpv4_public_172_32(): void
	{
		// 172.32.x.x is outside the 172.16.0.0/12 bogon range
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv4', ['172.32.100.50']);
		self::assertFalse($result['listed']);
	}

	// -------------------------------------------------------
	//  isBogonIpv6() tests
	// -------------------------------------------------------

	public function testIsBogonIpv6_loopback(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['::1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('::1/128', $result['description']);
		self::assertStringContainsString('Loopback', $result['description']);
	}

	public function testIsBogonIpv6_unspecified(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['::']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('::/128', $result['description']);
		self::assertStringContainsString('Unspecified', $result['description']);
	}

	public function testIsBogonIpv6_ipv4_mapped_prefix(): void
	{
		// ::ffff:192.168.1.1 has first 8 bytes zero but bytes 8-14 non-zero — caught by ::/96 check
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['::ffff:192.168.1.1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('::/96', $result['description']);
		self::assertStringContainsString('IPv4-mapped', $result['description']);
	}

	public function testIsBogonIpv6_documentation(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['2001:db8::1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('Documentation', $result['description']);
	}

	public function testIsBogonIpv6_6to4(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['2002::1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('6to4', $result['description']);
	}

	public function testIsBogonIpv6_teredo(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['2001::1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('TEREDO', $result['description']);
	}

	public function testIsBogonIpv6_orchidv2(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['2001:20::1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('ORCHIDv2', $result['description']);
	}

	public function testIsBogonIpv6_link_local(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['fe80::1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('Link-local', $result['description']);
	}

	public function testIsBogonIpv6_unique_local_fc(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['fc00::1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('Unique local', $result['description']);
	}

	public function testIsBogonIpv6_unique_local_fd(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['fd00::1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('Unique local', $result['description']);
	}

	public function testIsBogonIpv6_multicast(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['ff00::1']);
		self::assertTrue($result['listed']);
		self::assertStringContainsString('Multicast', $result['description']);
	}

	public function testIsBogonIpv6_multicast_upper(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['ffff::1']);
		self::assertTrue($result['listed']);
	}

	public function testIsBogonIpv6_public(): void
	{
		// A real public IPv6 address
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['2607:f8b0:4004:800::200e']);
		self::assertFalse($result['listed']);
	}

	public function testIsBogonIpv6_public_2001_4860(): void
	{
		// Google public IPv6
		$result	= $this->callProtectedMethod($this->logic, 'isBogonIpv6', ['2001:4860:4860::8888']);
		self::assertFalse($result['listed']);
	}

	// -------------------------------------------------------
	//  expandIpv6() tests
	// -------------------------------------------------------

	public function testExpandIpv6_compressed(): void
	{
		$expanded	= $this->callProtectedMethod($this->logic, 'expandIpv6', ['2001:db8::1']);
		self::assertSame('20010db8000000000000000000000001', $expanded);
	}

	public function testExpandIpv6_loopback(): void
	{
		$expanded	= $this->callProtectedMethod($this->logic, 'expandIpv6', ['::1']);
		self::assertSame('00000000000000000000000000000001', $expanded);
	}

	public function testExpandIpv6_unspecified(): void
	{
		$expanded	= $this->callProtectedMethod($this->logic, 'expandIpv6', ['::']);
		self::assertSame('00000000000000000000000000000000', $expanded);
	}

	public function testExpandIpv6_full(): void
	{
		$expanded	= $this->callProtectedMethod($this->logic, 'expandIpv6', ['2001:0db8:0000:0000:0000:0000:0000:0001']);
		self::assertSame('20010db8000000000000000000000001', $expanded);
	}

	public function testExpandIpv6_length(): void
	{
		$expanded	= $this->callProtectedMethod($this->logic, 'expandIpv6', ['fe80::1']);
		self::assertSame(32, strlen($expanded));
	}

	// -------------------------------------------------------
	//  isBogon() dispatcher tests
	// -------------------------------------------------------

	public function testIsBogon_dispatchesIpv4(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogon', ['10.0.0.1', 4]);
		self::assertTrue($result['listed']);
	}

	public function testIsBogon_dispatchesIpv6(): void
	{
		$result	= $this->callProtectedMethod($this->logic, 'isBogon', ['::1', 6]);
		self::assertTrue($result['listed']);
	}

	// -------------------------------------------------------
	//  checkIp() integration tests
	// -------------------------------------------------------

	public function testCheckIp_public_ipv4_structure(): void
	{
		$result	= $this->logic->checkIp('8.8.8.8');
		self::assertArrayHasKey('ip', $result);
		self::assertArrayHasKey('ip_version', $result);
		self::assertArrayHasKey('checked', $result);
		self::assertArrayHasKey('clean', $result);
		self::assertArrayHasKey('score', $result);
		self::assertArrayHasKey('sources', $result);
		self::assertSame('8.8.8.8', $result['ip']);
		self::assertSame(4, $result['ip_version']);
		self::assertTrue($result['checked']);
	}

	public function testCheckIp_public_ipv6_structure(): void
	{
		$result	= $this->logic->checkIp('2607:f8b0:4004:800::200e');
		self::assertSame(6, $result['ip_version']);
	}

	public function testCheckIp_bogon_ipv4_penalized(): void
	{
		// By default (skip_local_ips=no), bogon IPs are penalized with score -50
		// because they indicate potential IP spoofing in production.
		// DNSBLs are also checked (e.g. Spamhaus PBL lists private IPs), so the
		// total score may be lower than -50.
		$result	= $this->logic->checkIp('192.168.1.1');
		self::assertFalse($result['clean']);
		self::assertLessThan(0, $result['score']);
		self::assertTrue($result['checked']);
		self::assertArrayHasKey('bogons', $result['sources']);
		self::assertTrue($result['sources']['bogons']['listed']);
		self::assertStringContainsString('Private', $result['sources']['bogons']['description']);
	}

	public function testCheckIp_bogon_with_skip_local_ips(): void
	{
		// With skip_local_ips=yes, bogon IPs are skipped for local development
		$this->setModuleConfig('Security_IP_Check', 'skip_local_ips', 'yes');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$result	= $logic->checkIp('192.168.1.1');
		self::assertTrue($result['clean']);
		self::assertSame(0, $result['score']);
		self::assertFalse($result['checked']);
		self::assertArrayHasKey('bogons', $result['sources']);
		self::assertFalse($result['sources']['bogons']['listed']);
		self::assertStringContainsString('Skipped', $result['sources']['bogons']['description']);
		self::assertStringContainsString('Private', $result['sources']['bogons']['description']);
	}

	public function testCheckIp_local_loopback_penalized(): void
	{
		// By default, 127.0.0.1 is penalized as bogon (spoofing detection).
		// DNSBLs are also checked, so the total score may be lower than -50.
		$result	= $this->logic->checkIp('127.0.0.1');
		self::assertFalse($result['clean']);
		self::assertLessThan(0, $result['score']);
		self::assertTrue($result['sources']['bogons']['listed']);
		self::assertStringContainsString('Loopback', $result['sources']['bogons']['description']);
	}

	public function testCheckIp_local_loopback_with_skip_local_ips(): void
	{
		$this->setModuleConfig('Security_IP_Check', 'skip_local_ips', 'yes');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$result	= $logic->checkIp('127.0.0.1');
		self::assertTrue($result['clean']);
		self::assertSame(0, $result['score']);
		self::assertFalse($result['checked']);
		self::assertStringContainsString('Skipped', $result['sources']['bogons']['description']);
	}

	public function testCheckIp_local_ipv6_loopback_penalized(): void
	{
		// By default, ::1 is penalized as bogon
		$result	= $this->logic->checkIp('::1');
		self::assertFalse($result['clean']);
		self::assertSame(-50, $result['score']);
		self::assertTrue($result['sources']['bogons']['listed']);
	}

	public function testCheckIp_local_ipv6_loopback_with_skip_local_ips(): void
	{
		$this->setModuleConfig('Security_IP_Check', 'skip_local_ips', 'yes');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$result	= $logic->checkIp('::1');
		self::assertTrue($result['clean']);
		self::assertSame(0, $result['score']);
		self::assertFalse($result['checked']);
		self::assertStringContainsString('Skipped', $result['sources']['bogons']['description']);
	}

	public function testCheckIp_bogons_disabled(): void
	{
		$this->setModuleConfig('Security_IP_Check', 'use.list.bogons', 'no');
		// Re-create logic to pick up the new config
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$result	= $logic->checkIp('192.168.1.1');
		self::assertArrayNotHasKey('bogons', $result['sources']);
		// Even with bogons disabled, DNSBLs are still checked for public IPs
		// (bogons source is absent, but DNSBL sources may be present).
	}

	public function testCheckIp_skip_local_ips_with_bogons_disabled(): void
	{
		// skip_local_ips should work independently of use.list.bogons.
		// When both are set, local IPs are still skipped for development.
		$this->setModuleConfig('Security_IP_Check', 'use.list.bogons', 'no');
		$this->setModuleConfig('Security_IP_Check', 'skip_local_ips', 'yes');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$result	= $logic->checkIp('192.168.1.1');
		self::assertTrue($result['clean']);
		self::assertSame(0, $result['score']);
		self::assertFalse($result['checked']);
		// Bogons source shows "Skipped" even though use.list.bogons is disabled,
		// because skip_local_ips detects the bogon internally.
		self::assertArrayHasKey('bogons', $result['sources']);
		self::assertFalse($result['sources']['bogons']['listed']);
		self::assertStringContainsString('Skipped', $result['sources']['bogons']['description']);
		// No DNSBL sources should be present
		self::assertArrayNotHasKey('spamhaus_sbl', $result['sources']);
	}

	public function testCheckIp_spamhaus_sbl_disabled(): void
	{
		$this->setModuleConfig('Security_IP_Check', 'use.list.spamhaus_sbl', 'no');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$result	= $logic->checkIp('8.8.8.8');
		self::assertArrayNotHasKey('spamhaus_sbl', $result['sources']);
	}

	public function testCheckIp_all_dns_disabled(): void
	{
		$this->setModuleConfig('Security_IP_Check', 'use.list.spamhaus_sbl', 'no');
		$this->setModuleConfig('Security_IP_Check', 'use.list.spamhaus_xbl', 'no');
		$this->setModuleConfig('Security_IP_Check', 'use.list.spamhaus_pbl', 'no');
		$this->setModuleConfig('Security_IP_Check', 'use.list.spamhaus_ehn', 'no');
		$this->setModuleConfig('Security_IP_Check', 'use.list.sorbs', 'no');
		$this->setModuleConfig('Security_IP_Check', 'use.list.barracuda', 'no');
		$this->setModuleConfig('Security_IP_Check', 'use.list.spamcop', 'no');
		$this->setModuleConfig('Security_IP_Check', 'use.list.abusix', 'no');
		$this->setModuleConfig('Security_IP_Check', 'use.list.invaluement', 'no');
		$this->setModuleConfig('Security_IP_Check', 'use.list.bogons', 'no');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$result	= $logic->checkIp('8.8.8.8');
		// With all lists disabled, sources should be empty and checked should be false
		self::assertSame([], $result['sources']);
		self::assertSame(0, $result['score']);
		self::assertFalse($result['checked']);
		self::assertTrue($result['clean']);
	}

	public function testCheckIp_new_lists_present(): void
	{
		$result	= $this->logic->checkIp('8.8.8.8');
		self::assertArrayHasKey('barracuda', $result['sources']);
		self::assertArrayHasKey('spamcop', $result['sources']);
		self::assertArrayHasKey('abusix', $result['sources']);
		self::assertArrayHasKey('invaluement', $result['sources']);
	}

	public function testCheckIp_sorbs_deprecated_description(): void
	{
		$result	= $this->logic->checkIp('8.8.8.8');
		self::assertArrayHasKey('sorbs', $result['sources']);
		self::assertStringContainsString('deprecated', $result['sources']['sorbs']['description']);
	}

	// -------------------------------------------------------
	//  DNS cache tests (via reflection)
	// -------------------------------------------------------

	public function testDnsCache_populated_after_lookup(): void
	{
		// checkIp populates the DNS cache; verify via reflection
		$this->logic->checkIp('8.8.8.8');

		$cache	= $this->getProtectedProperty($this->logic, 'dnsCache');
		self::assertIsArray($cache);
		self::assertGreaterThan(0, count($cache), 'DNS cache should contain entries after checkIp');
	}

	public function testDnsCache_ttl_property(): void
	{
		$ttl	= $this->getProtectedProperty($this->logic, 'cacheTtl');
		self::assertSame(3600, $ttl);
	}

	public function testDnsCache_custom_ttl(): void
	{
		$this->setModuleConfig('Security_IP_Check', 'cache_ttl', '60');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$ttl	= $this->getProtectedProperty($logic, 'cacheTtl');
		self::assertSame(60, $ttl);
	}

	// -------------------------------------------------------
	//  logRejectedIp() config tests
	// -------------------------------------------------------

	public function testLogRejectedIp_config_defaults(): void
	{
		$logRejected	= $this->getProtectedProperty($this->logic, 'logRejected');
		$logPostData	= $this->getProtectedProperty($this->logic, 'logPostData');
		$logHeaders	= $this->getProtectedProperty($this->logic, 'logHeaders');
		self::assertTrue($logRejected);
		self::assertTrue($logPostData);
		self::assertTrue($logHeaders);
	}

	public function testLogRejectedIp_disabled(): void
	{
		$this->setModuleConfig('Security_IP_Check', 'log.rejected', 'no');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$logRejected	= $this->getProtectedProperty($logic, 'logRejected');
		self::assertFalse($logRejected);

		// logRejectedIp should return FALSE when disabled
		$result	= $logic->logRejectedIp(
			'1.2.3.4',
			[ 'ip_version' => 4, 'score' => -50, 'sources' => [ 'bogons' => [ 'listed' => TRUE, 'description' => 'Test' ] ] ],
			'POST',
			'/test',
			[ 'key' => 'value' ],
			[ 'user_agent' => 'test' ]
		);
		self::assertFalse($result);
	}

	public function testLogRejectedIp_no_database(): void
	{
		// Without a database connection, logRejectedIp should return FALSE gracefully
		$result	= $this->logic->logRejectedIp(
			'1.2.3.4',
			[ 'ip_version' => 4, 'score' => -50, 'sources' => [ 'bogons' => [ 'listed' => TRUE, 'description' => 'Test' ] ] ],
			'POST',
			'/test',
			[ 'key' => 'value' ],
			[ 'user_agent' => 'test' ]
		);
		// Returns FALSE because no database is configured in test environment
		self::assertFalse($result);
	}

	public function testLogRejectedIp_post_data_disabled(): void
	{
		$this->setModuleConfig('Security_IP_Check', 'log.post_data', 'no');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$logPostData	= $this->getProtectedProperty($logic, 'logPostData');
		self::assertFalse($logPostData);
	}

	public function testLogRejectedIp_headers_disabled(): void
	{
		$this->setModuleConfig('Security_IP_Check', 'log.headers', 'no');
		$env		= $this->createEnvironment();
		$logic	= Logic_IP_Check::getInstance($env);

		$logHeaders	= $this->getProtectedProperty($logic, 'logHeaders');
		self::assertFalse($logHeaders);
	}


	// -------------------------------------------------------
	//  Helper: call protected method via reflection
	// -------------------------------------------------------

	/**
	 * @param object $object
	 * @param string $methodName
	 * @param array $args
	 * @return mixed
	 */
	protected function callProtectedMethod(object $object, string $methodName, array $args = []): mixed
	{
		$reflection	= new ReflectionClass($object);
		$method		= $reflection->getMethod($methodName);
		$method->setAccessible(true);
		return $method->invoke($object, ...$args);
	}

	/**
	 * @param object $object
	 * @param string $propertyName
	 * @return mixed
	 */
	protected function getProtectedProperty(object $object, string $propertyName): mixed
	{
		$reflection	= new ReflectionClass($object);
		$property	= $reflection->getProperty($propertyName);
		$property->setAccessible(true);
		return $property->getValue($object);
	}
}
