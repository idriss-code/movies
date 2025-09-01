<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 *
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    public function findAllOrderedByAddedAt()
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.studio', 's')
            ->leftJoin('m.director', 'd')
            ->addSelect('s', 'd')
            ->orderBy('m.addedAt', 'DESC')
            ->getQuery();
    }

    public function findByStudioOrderedByAddedAt($studio)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.studio', 's')
            ->leftJoin('m.director', 'd')
            ->addSelect('s', 'd')
            ->where('m.studio = :studio')
            ->setParameter('studio', $studio)
            ->orderBy('m.addedAt', 'DESC')
            ->getQuery();
    }

    public function findByDirectorOrderedByAddedAt($director)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.studio', 's')
            ->leftJoin('m.director', 'd')
            ->addSelect('s', 'd')
            ->where('m.director = :director')
            ->setParameter('director', $director)
            ->orderBy('m.addedAt', 'DESC')
            ->getQuery();
    }

    public function findByActorOrderedByAddedAt($actor)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.studio', 's')
            ->leftJoin('m.director', 'd')
            ->leftJoin('m.actors', 'a')
            ->addSelect('s', 'd', 'a')
            ->where('a = :actor')
            ->setParameter('actor', $actor)
            ->orderBy('m.addedAt', 'DESC')
            ->getQuery();
    }

    public function searchMovies($query)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.studio', 's')
            ->leftJoin('m.director', 'd')
            ->leftJoin('m.actors', 'a')
            ->addSelect('s', 'd', 'a')
            ->where('m.title LIKE :query')
            ->orWhere('s.name LIKE :query')
            ->orWhere('CONCAT(d.firstName, \' \', d.lastName) LIKE :query')
            ->orWhere('CONCAT(a.firstName, \' \', a.lastName) LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('m.addedAt', 'DESC')
            ->getQuery();
    }

    public function findByTitleStudioNameAndFormat(string $title, ?string $studioName, ?string $format): ?Movie
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->leftJoin('m.studio', 's')
            ->where('m.title = :title')
            ->setParameter('title', $title);

        if ($studioName) {
            $queryBuilder->andWhere('s.name = :studio_name')
                ->setParameter('studio_name', $studioName);
        } else {
            $queryBuilder->andWhere('m.studio IS NULL');
        }

        if ($format) {
            $queryBuilder->andWhere('m.format = :format')
                ->setParameter('format', $format);
        } else {
            $queryBuilder->andWhere('m.format IS NULL');
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    //    /**
    //     * @return Movie[] Returns an array of Movie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Movie
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}