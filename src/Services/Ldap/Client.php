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
        return ldap_search($this->connection, $this->generateDn($additionalDn), $query);
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

    /**
     * @param resource $result
     * @return resource
     */
    public function first($result)
    {
        assert(is_resource($result));

        return ldap_first_entry($this->connection, $result);
    }

    /**
     * @param resource $entry
     */
    public function getDn($entry): ?string
    {
        assert(is_resource($entry));

        return ldap_get_dn($this->connection, $entry) ?: null;
    }

    public function add(array $data, array $rdn = []): bool
    {
        return ldap_add($this->connection, $this->generateDn($rdn), $data);
    }

    public function modify(array $data, string $dn): bool
    {
        return ldap_modify($this->connection, $dn, $data);
    }

    public function remove(string $dn): bool
    {
        return ldap_delete($this->connection, $dn);
    }

    public function generateDn(array $rdn = []): string
    {
        $dc = '';

        foreach (array_merge($rdn, $this->target->getDomain()) as $key => $domainComponent) {
            $dnKey = is_string($key) ? $key : 'dc';

            $domainComponent = is_array($domainComponent) ? $domainComponent : [$domainComponent];

            foreach ($domainComponent as $domainComponentElement) {
                $dc .= sprintf('%s=%s,', $dnKey, $domainComponentElement);
            }
        }

        return substr($dc, 0, -1);
    }
}
