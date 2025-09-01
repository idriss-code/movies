<?php

namespace App\Repository;

use App\Entity\UserSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSetting>
 *
 * @method UserSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSetting[]    findAll()
 * @method UserSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSetting::class);
    }

    public function findBySessionAndKey(string $sessionId, string $settingKey): ?UserSetting
    {
        return $this->findOneBy([
            'sessionId' => $sessionId,
            'settingKey' => $settingKey
        ]);
    }

    public function findAllBySession(string $sessionId): array
    {
        return $this->findBy(['sessionId' => $sessionId]);
    }
}