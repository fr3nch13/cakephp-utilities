<?php
declare(strict_types=1);

/**
 * Test the networking stuff
 *
 * @TODO Mock an environment without any networking at all.
 */

namespace Fr3nch13\Utilities\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use Fr3nch13\Utilities\Lib\CommonNetwork;

class CommonNetworkTest extends TestCase
{
    /**
     * @var \Fr3nch13\Utilities\Lib\CommonNetwork
     */
    public $CN;

    public function setUp(): void
    {
        parent::setUp();
        $this->CN = new CommonNetwork();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->CN);
        parent::tearDown();
    }

    public function testValidateIP(): void
    {
        $this->assertTrue($this->CN->validateIP('10.10.10.10'));
        $this->assertFalse($this->CN->validateIP('10.10.10.010'));
        $this->assertFalse($this->CN->validateIP('10.10.10.260'));
        $this->assertFalse($this->CN->validateIP('10.10.10.0/24'));
        $this->assertFalse($this->CN->validateIP('2001:db8:3333:4444:5555:6666:7777:8888'));
    }

    public function testCidrToNetmask(): void
    {
        $this->assertSame('255.255.255.0', $this->CN->cidrToNetmask('10.10.10.0/24'));
        $this->assertNull($this->CN->cidrToNetmask('10.10.10.10'));
        $this->assertNull($this->CN->cidrToNetmask('10.10.010.0/24'));
        $this->assertNull($this->CN->cidrToNetmask('0.0.0.0/0'));
    }

    public function testCidrToNework(): void
    {
        $this->assertSame('10.10.10.0', $this->CN->cidrToNetwork('10.10.10.0/24'));
        $this->assertNull($this->CN->cidrToNetwork('10.10.10.10'));
        $this->assertNull($this->CN->cidrToNetwork('10.10.010.0/24'));
        $this->assertSame('0.0.0.0', $this->CN->cidrToNetwork('0.0.0.0/0'));
    }

    public function testCidrToRange(): void
    {
        $this->assertSame([
            '10.10.10.0',
            '10.10.10.255',
        ], $this->CN->cidrToRange('10.10.10.0/24'));
        $this->assertSame([
            168430080,
            168430335,
        ], $this->CN->cidrToRange('10.10.10.0/24', true));
        $this->assertSame([
            null,
            null,
        ], $this->CN->cidrToRange('10.10.10.10'));
        $this->assertSame([
            null,
            null,
        ], $this->CN->cidrToRange('10.10.010.0/24'));
        $this->assertSame([
            '0.0.0.0',
            '255.255.255.255',
        ], $this->CN->cidrToRange('0.0.0.0/0'));
        $this->assertSame([
            0,
            4294967295,
        ], $this->CN->cidrToRange('0.0.0.0/0', true));
    }

    public function testCidrToIpArray(): void
    {
        $this->assertSame([
            '10.10.10.0',
        ], $this->CN->cidrToIpArray('10.10.10.0/32'));
        $this->assertSame([
            '10.10.10.0',
            '10.10.10.1',
            '10.10.10.2',
            '10.10.10.3',
        ], $this->CN->cidrToIpArray('10.10.10.0/30'));
        $this->assertSame([], $this->CN->cidrToIpArray('10.10.10.10'));
        $this->assertSame([], $this->CN->cidrToIpArray('10.10.300.0/30'));
    }

    public function testIpInCidr(): void
    {
        $this->assertTrue($this->CN->ipInCidr('10.10.10.10', '10.10.10.0/24'));
        $this->assertFalse($this->CN->ipInCidr('10.10.10.10', '10.10.10.10'));
        $this->assertFalse($this->CN->ipInCidr('10.10.10.10', '10.10.10.0/0'));
        $this->assertFalse($this->CN->ipInCidr('10.10.10.10', 'notacidr'));
        $this->assertFalse($this->CN->ipInCidr('10.10.10.10', 'nota/cidr'));
    }

    public function testNetmaskToCidr(): void
    {
        $this->assertSame(0, $this->CN->netmaskToCidr('10.10.10.0/32'));
        $this->assertSame(0, $this->CN->netmaskToCidr('255.255.255'));
        $this->assertSame(8, $this->CN->netmaskToCidr('10.10.10.10'));
        $this->assertSame(32, $this->CN->netmaskToCidr('255.255.255.255'));
        $this->assertSame(24, $this->CN->netmaskToCidr('255.255.255.0'));
    }

    public function testNetmaskToArray(): void
    {
        $this->assertSame([
            '10.10.10.0',
        ], $this->CN->netmaskToArray('10.10.10.0', '255.255.255.255'));
    }

    public function testLong2ip(): void
    {
        $this->assertSame('10.10.10.0', $this->CN->long2ip(168430080));
        $this->assertSame('0.0.0.0', $this->CN->long2ip(0));
        $this->assertSame('255.255.255.255', $this->CN->long2ip(-1));
        $this->assertSame('0.0.0.1', $this->CN->long2ip(-4294967295));
        $this->assertNull($this->CN->long2ip('10.10.10.10'));
    }

    public function testGetMyInterfaces(): void
    {
        // not cached, return localloop
        $result = $this->CN->getMyInterfaces('127.0.0.1');
        $expected = [
            'interface' => 'lo',
            'ip' => '127.0.0.1',
            'netmask' => '255.0.0.0',
            'broadcast' => null,
            'ip6' => '::1',
            'mac' => null,
        ];
        $this->assertSame($expected, $result);

        // cached, return all
        $result = $this->CN->getMyInterfaces();
        // will include the localhost interface, atleast.
        $this->assertGreaterThan(1, count($result));

        // cached return cached localloop
        $result = $this->CN->getMyInterfaces('127.0.0.1');
        $expected = [
            'interface' => 'lo',
            'ip' => '127.0.0.1',
            'netmask' => '255.0.0.0',
            'broadcast' => null,
            'ip6' => '::1',
            'mac' => null,
        ];
        $this->assertSame($expected, $result);

        // cached return first null;
        $result = $this->CN->getMyInterfaces('255.255.255.255');
        $this->assertNull($result);

        // not cached return first null;
        $this->CN->myInterfaces = null;
        $result = $this->CN->getMyInterfaces('255.255.255.255');
        $this->assertNull($result);
    }

    public function testGetHostname(): void
    {
        // not cached
        $result = $this->CN->gethostname();
        $this->assertNotNull($result);
        $this->assertMatchesRegularExpression('/[a-f0-9\-.]+/', strtolower($result));

        // cached
        $result = $this->CN->gethostname();
        $this->assertNotNull($result);
        $this->assertMatchesRegularExpression('/[a-f0-9\-.]+/', strtolower($result));
    }

    public function testGetPrimaryIps(): void
    {
        // not cached
        $result = $this->CN->getPrimaryIps();
        $this->assertNotNull($result);
        $this->assertIsArray($result);

        // cached
        $result = $this->CN->getPrimaryIps();
        $this->assertNotNull($result);
        $this->assertIsArray($result);
    }
}
