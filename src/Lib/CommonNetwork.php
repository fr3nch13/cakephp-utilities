<?php
declare(strict_types=1);

/**
 * CommonNetwork
 */

namespace Fr3nch13\Utilities\Lib;

/**
 * Common Network Utility
 *
 * Holds common functions needed for translating different ipv4 stuff.
 *
 * @TODO Add the ability to calculate ipv6, for now, just return null/false/empty strings.
 */
class CommonNetwork
{
    /**
     * The hostrname of the host machine.
     *
     * @var null|string
     */
    protected $myHostname = null;

    /**
     * Primary interface.
     *
     * @var null|array<string, mixed>
     */
    public $myInterface;

    /**
     * List of interfaces.
     *
     * @var array<string, mixed>
     */
    public $myInterfaces = [];

    /**
     * List of ip addresses for the host machine.
     *
     * @var array<string, string>
     */
    protected $myIpaddresses = [];

    /**
     * Validates a human readable ip address.
     *
     * @param string $ip The IP Address to validate.
     * @return bool True if the IP Address is valid, or false if not.
     */
    public function validateIP(string $ip): bool
    {
        // if the ip address was given with the netmask.
        if (strpos($ip, '/') !== false) {
            return false;
        }

        //make sure it's a valid ip address.
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        // Make sure it's only ipv4, we'll deal with ipv6 later.
        $preg = '/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/';
        if (!preg_match($preg, $ip)) {
            return false;
        }

        // rely on php's code function
        return inet_pton($ip) !== false;
    }

    /**
     * Convert a CIDR to a Netmask.
     * e.g. 21 = 255.255.248.0
     *
     * @param string $network_cidr The cidr to convert.
     * @return null|string The Netmask.
     */
    public function cidrToNetmask(string $network_cidr): ?string
    {
        $parts = explode('/', $network_cidr);
        if (count($parts) != 2) {
            return null; // Malformed string isn't a cidr format: x.x.x.x/x
        }

        $network = $parts[0];
        $cidr = $parts[1];
        if (!$this->validateIP($network)) {
            return null;
        }
        $bin = '';
        for ($i = 1; $i <= 32; $i++) {
            $bin .= $cidr >= $i ? '1' : '0';
        }
        $netmask = $this->long2ip(bindec($bin));
        if ($netmask == '0.0.0.0') {
            return null;
        }

        return $netmask;
    }

    /**
     * Get Network Address from a CIDR subnet.
     * e.g. 10.0.2.56/21 = 10.0.0.0
     *
     * @param string $network_cidr The network's cidr.
     * @return null|string The IP Address
     */
    public function cidrToNetwork(string $network_cidr): ?string
    {
        $parts = explode('/', $network_cidr);
        if (count($parts) != 2) {
            return null; // Malformed string isn't a cidr format: x.x.x.x/x
        }

        $network = $parts[0];
        $cidr = $parts[1];
        if (!$this->validateIP($network)) {
            return null;
        }

        $network = $this->long2ip($this->ip2long($network) & -1 << 32 - (int)$cidr);

        return $network;
    }

    /**
     * Get the Network's IP range from a CIDR.
     * e.g. 10.1.10.0/28 = array(10.1.10.0, 10.1.10.3)
     *
     * @param string $network_cidr The Network CIDR.
     * @param bool $long If we should return the range as IP Addresses, or long integers.
     * @return array<int, int|string|null> The first and last IP Address in the Network.
     * @throws \Throwable
     */
    public function cidrToRange(string $network_cidr, bool $long = false): array
    {
        $range = [
            0 => null,
            1 => null,
        ];

        $parts = explode('/', $network_cidr);
        if (count($parts) != 2) {
            return $range; // Malformed string isn't a cidr format: x.x.x.x/x
        }

        $network = $parts[0];
        $cidr = $parts[1];

        if (!$this->validateIP($network)) {
            return $range;
        }

        $range[0] = $this->long2ip($this->ip2long($network) & -1 << 32 - (int)$cidr);
        $range[1] = $this->long2ip($this->ip2long($network) + pow(2, 32 - (int)$cidr) - 1);
        if ($long) {
            $range[0] = $this->ip2long($range[0]);
            $range[1] = $this->ip2long($range[1]);
        }

        return $range;
    }

    /**
     * Get the array of IP Addresses from a CIDR.
     * e.g. 10.1.10.0/28 = array(10.1.10.0, 10.1.10.1, 10.1.10.2, 10.1.10.3)
     *
     * @param string $network_cidr The network's CIDR.
     * @return array<int, string> The list of ip addresses in the network.
     */
    public function cidrToIpArray(string $network_cidr): array
    {
        $parts = explode('/', $network_cidr);
        if (count($parts) != 2) {
            return []; // Malformed string isn't a cidr format: x.x.x.x/x
        }

        $network = $parts[0];
        $cidr = $parts[1];

        if (!$this->validateIP($network)) {
            return [];
        }
        $cidr = (int)$cidr;
        $ipcount = pow(2, 32 - $cidr);
        $network = $this->long2ip($this->ip2long($network) & -1 << 32 - (int)$cidr);
        $start = $this->ip2long($network);
        $iparr = [];
        for ($beat = 0; $beat < $ipcount; $beat++) {
            $iparr[$beat] = $this->long2ip($start + $beat);
        }

        return $iparr;
    }

    /**
     * Convert Netmask to the CIDR.
     * e.g. 255.255.255.128 = 25
     *
     * @param string $netmask The Netmask representing the Network.
     * @return int The CIDR for the Network.
     */
    public function netmaskToCidr(string $netmask): int
    {
        $bits = 0;
        if (!$this->validateIP($netmask)) {
            return $bits;
        }
        $netmask = explode('.', $netmask);
        foreach ($netmask as $octect) {
            $bits += strlen(str_replace('0', '', decbin((int)$octect)));
        }

        return $bits;
    }

    /**
     * If the IP Address is in the Network.
     * e.g. is 10.5.21.30 in 10.5.16.0/20 == true
     *      is 192.168.50.2 in 192.168.30.0/23 == false
     *
     * @param string $ip The IP Address to check.
     * @param string $network_cidr The Network CIDR to check.
     * @return bool True if it's in the Network, False if it's not.
     */
    public function ipInCidr(string $ip, string $network_cidr): bool
    {
        $parts = explode('/', $network_cidr);
        if (count($parts) != 2) {
            return false; // Malformed string isn't a cidr format: x.x.x.x/x
        }

        $network = $parts[0];
        $cidr = $parts[1];

        $cidr = (int)$cidr;
        try {
            if (($this->ip2long($network) & ~1 << 32 - $cidr - 1) == $this->ip2long($network)) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Return a list of IP Addresses for a netmask.
     *
     * @param string $ipaddress The Ip Address.
     * @param string $netmask The Netmask.
     * @return array<int, string> The list of IP Addresses.
     */
    public function netmaskToArray(string $ipaddress, string $netmask): array
    {
        $netmask = trim($netmask);
        $cidr = $this->netmaskToCidr($netmask);

        return $this->cidrToIpArray($ipaddress . '/' . $cidr);
    }

    /**
     * Converts a long integer to an IP Address.
     *
     * @param mixed|float|int $ip The Integer/float.
     * @return null|string The IP Address.
     */
    public function long2ip($ip): ?string
    {
        if (!is_int($ip) && !is_float($ip)) {
            return null;
        }

        $long = intval(4294967295 - ($ip - 1));

        $ip = \long2ip(-$long);
        if ($ip) {
            return strval($ip);
        }

        return null;
    }

    /**
     * Converts an IP Address to a long integer.
     *
     * @param string $ip The IP Address.
     * @return int The integer representing the IP Address.
     */
    public function ip2long(string $ip): ?int
    {
        $long = (int)sprintf('%u', \ip2long($ip));

        return $long;
    }

    /**
     * Gets the interface name of the given ip address, or all if none is given.
     *
     * @param string $ip The ip address of the interface to get.
     * @return null|array<string, mixed> Get the name of the interface.
     */
    public function getMyInterfaces(string $ip): ?array
    {
        if (empty($this->myInterfaces)) {
            $cmdWhich = __('which ifconfig');
            $output = [];
            exec($cmdWhich, $output, $return_var);
            if ($return_var || !$output) {
                return null;
            }
            $cmdIfconfig = array_pop($output);
            exec($cmdIfconfig, $output, $return_var);
            if ($return_var || !$output) {
                return null;
            }
            $currentInt = null;
            foreach ($output as $line) {
                // get the interface name, and track it.
                $out = [];
                if (preg_match('/^([A-z]*\d+)\:*\s+/', $line, $out)) {
                    $currentInt = $out[1];
                    if (!isset($this->myInterfaces[$currentInt])) {
                        $this->myInterfaces[$currentInt] = [
                            'interface' => $currentInt,
                            'ip' => null,
                            'netmask' => null,
                            'broadcast' => null,
                            'ip6' => null,
                            'mac' => null,
                        ];
                    }
                    continue;
                }
                // make sure we have the interface name.
                if ($currentInt) {
                    // get the ip info
                    $out = [];
                    if (
                        preg_match('/^\s+inet\s+([0-9.]+)\s+netmask\s+([0-9.]+)\s+broadcast\s+([0-9.]+)/', $line, $out)
                    ) {
                        $this->myInterfaces[$currentInt]['ip'] = $out[1];
                        $this->myInterfaces[$currentInt]['netmask'] = $out[2];
                        $this->myInterfaces[$currentInt]['broadcast'] = $out[3];
                        continue;
                    }
                    // get the ipv6
                    $out = [];
                    if (preg_match('/^\s+inet6\s+([0-9A-Fa-f:.]+)\s+/', $line, $out)) {
                        $this->myInterfaces[$currentInt]['ip6'] = $out[1];
                        continue;
                    }
                    // get the MAC Address
                    $out = [];
                    if (preg_match('/^\s+ether\s+([0-9A-Fa-f:.]+)\s+/', $line, $out)) {
                        $this->myInterfaces[$currentInt]['mac'] = strtoupper(str_replace(':', '', $out[1]));
                        continue;
                    }
                }
            }
        }
        if (!empty($this->myInterfaces)) {
            foreach ($this->myInterfaces as $interface) {
                if ($interface['ip'] == $ip) {
                    return $interface;
                }
            }
        }

        return null;
    }

    /**
     * Gets the primary ip for this host.
     *
     * @return null|array<string, string> The primary ip address.
     */
    public function getPrimaryIps(): ?array
    {
        if (empty($this->myIpaddresses)) {
            //try to get my hostname
            if (!isset($this->myHostname)) {
                $this->myHostname = $this->gethostname();
                if (!$this->myHostname) {
                    return null;
                }
            }
            $dnsRecords = dns_get_record($this->myHostname);
            if ($dnsRecords) {
                $ips = [];
                foreach ($dnsRecords as $dnsRecord) {
                    if (isset($dnsRecord['ip'])) {
                        $dnsRecord['ip'] = strval($dnsRecord['ip']);
                        $ips[$dnsRecord['ip']] = $dnsRecord['ip'];
                    }
                }
                if ($ips) {
                    $this->myIpaddresses = $ips;

                    return $this->myIpaddresses;
                }
            }
        }

        return null;
    }

    /**
     * Gets my hostname
     *
     * @return null|string The hostname of the system this is ruynning on.
     */
    public function gethostname(): ?string
    {
        $cmdHostname = __('hostname --fqdn');
        exec($cmdHostname, $output, $return_var);
        if ($return_var || !$output) {
            return null;
        }

        return array_pop($output);
    }
}
