<?php declare(strict_types=1);

namespace LinkORB\OrgSync\Services\Ldap;

use LinkORB\OrgSync\DTO\Target;
use UnexpectedValueException;

class Client
{
    /**
     * @var Target\Ldap
     */
    private $target;

    /**
     * @var resource
     */
    private $connection;

    public function __construct(Target\Ldap $target)
    {
        $this->target = $target;
    }

    public function __destruct()
    {
        ldap_unbind($this->connection);
    }

    public function init(): self
    {
        if ($this->connection) {
            return $this;
        }

        $this->connection = ldap_connect($this->target->getBaseUrl());

        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);

        return $this;
    }

    public function bind(): self
    {
        if (!ldap_bind($this->connection, $this->target->getBindRdn(), $this->target->getPassword())) {
            throw new UnexpectedValueException('Binding failed');
        }

        return $this;
    }

    /**
     * @return resource
     */
    public function search(string $query, array $additionalDn = [])
    {
        return ldap_search($this->connection, $this->getDcString($additionalDn), $query);
    }

    /**
     * @param resource $result
     */
    public function count($result): ?int
    {
        assert(is_resource($result));

        $count = ldap_count_entries($this->connection, $result);

        if ($count === false) {
            return null;
        }

        return $count;
    }

    /**
     * @param resource $result
     */
    public function all($result): array
    {
        assert(is_resource($result));

        return ldap_get_entries($this->connection, $result);
    }

    public function add(array $data, array $additionalDn = []): bool
    {
        return ldap_add($this->connection, $this->getDn($data, $additionalDn), $data);
    }

    public function modify(array $data, array $additionalDn = []): bool
    {
        return ldap_modify($this->connection, $this->getDn($data, $additionalDn), $data);
    }

    public function remove(string $dn): bool
    {
        return ldap_delete($this->connection, $dn);
    }

    public function getDn(array $data, array $additionalDn = []): string
    {
        $excludeKeys = ['dc', 'objectClass', 'uniqueMember'];
        $dn = '';

        foreach ($data as $key => $item) {
            if (in_array($key, $excludeKeys, true)) {
                continue;
            }

            $dn .= sprintf('%s=%s+', $key, $item);
        }

        return substr($dn, 0, -1) . ',' . $this->getDcString($additionalDn);
    }

    private function getDcString(array $additionalDn = []): string
    {
        $dc = '';

        foreach (array_merge($additionalDn, $this->target->getDomain()) as $key => $domainComponent) {
            $dnKey = is_string($key) ? $key : 'dc';

            $domainComponent = is_array($domainComponent) ? $domainComponent : [$domainComponent];

            foreach ($domainComponent as $domainComponentElement) {
                $dc .= sprintf('%s=%s,', $dnKey, $domainComponentElement);
            }
        }

        return substr($dc, 0, -1);
    }
}
