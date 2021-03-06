<?php

declare(strict_types=1);

namespace Model\Payment\Repositories;

use Model\Payment\Group;
use Model\Payment\Group\SkautisEntity;
use Model\Payment\Group\Type;
use Model\Payment\GroupNotFound;

interface IGroupRepository
{
    /**
     * @throws GroupNotFound
     */
    public function find(int $id) : Group;

    /**
     * @param int[] $ids
     *
     * @return Group[]
     */
    public function findByIds(array $ids) : array;

    /**
     * @param int[] $unitIds
     *
     * @return Group[]
     */
    public function findByUnits(array $unitIds, bool $openOnly) : array;

    /**
     * @return Group[]
     */
    public function findBySkautisEntities(SkautisEntity ...$objects) : array;

    /**
     * @return Group[]
     */
    public function findBySkautisEntityType(Type $type) : array;

    /**
     * @return Group[]
     */
    public function findByBankAccount(int $bankAccountId) : array;

    /**
     * @return Group[]
     */
    public function findBySmtp(int $smtpId) : array;

    public function save(Group $group) : void;

    public function remove(Group $group) : void;
}
