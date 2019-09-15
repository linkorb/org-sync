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
    public function search(string $query)
    {
        return ldap_search($this->connection, $this->getDcString(), $query);
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

    public function add(array $data): bool
    {
        return ldap_add($this->connection, $this->getDn($data), $data);
    }

    public function modify(array $data): bool
    {
        return ldap_modify($this->connection, $this->getDn($data), $data);
    }

    public function remove(string $dn): bool
    {
        return ldap_delete($this->connection, $dn);
    }

    private function getDn(array $data): string
    {
        $excludeKeys = ['dc', 'objectClass'];
        $dn = '';

        foreach ($data as $key => $item) {
            if (in_array($key, $excludeKeys, true)) {
                continue;
            }

            $dn .= sprintf('%s=%s+', $key, $item);
        }

        return substr($dn, 0, -1) . ',' . $this->getDcString();
    }

    private function getDcString(): string
    {
        $dc = '';

        foreach ($this->target->getDomain() as $domainComponent) {
            $dc .= sprintf('dc=%s,', $domainComponent);
        }

        return substr($dc, 0, -1);
    }
}
