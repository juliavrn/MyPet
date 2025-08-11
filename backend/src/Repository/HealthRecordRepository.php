<?php

namespace App\Repository;

use App\Entity\HealthRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HealthRecord>
 *
 * @method HealthRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method HealthRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method HealthRecord[]    findAll()
 * @method HealthRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HealthRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HealthRecord::class);
    }

    public function save(HealthRecord $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HealthRecord $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve les enregistrements de santé pour un animal donné
     */
    public function findByPet(int $petId, int $limit = 30): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.pet = :petId')
            ->setParameter('petId', $petId)
            ->orderBy('h.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve l'enregistrement de santé pour un animal à une date donnée
     */
    public function findByPetAndDate(int $petId, \DateTimeInterface $date): ?HealthRecord
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.pet = :petId')
            ->andWhere('h.date = :date')
            ->setParameter('petId', $petId)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve les enregistrements de santé pour un animal dans une période donnée
     */
    public function findByPetAndDateRange(int $petId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.pet = :petId')
            ->andWhere('h.date >= :startDate')
            ->andWhere('h.date <= :endDate')
            ->setParameter('petId', $petId)
            ->setParameter('startDate', $startDate->format('Y-m-d'))
            ->setParameter('endDate', $endDate->format('Y-m-d'))
            ->orderBy('h.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les enregistrements avec des signes de maladie
     */
    public function findWithHealthIssues(int $petId, int $limit = 10): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.pet = :petId')
            ->andWhere('h.signsOfIllness = :true OR h.fever = :true OR h.vomiting = :true OR h.limping = :true')
            ->setParameter('petId', $petId)
            ->setParameter('true', true)
            ->orderBy('h.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les enregistrements récents (7 derniers jours)
     */
    public function findRecentRecords(int $petId): array
    {
        $sevenDaysAgo = new \DateTime();
        $sevenDaysAgo->modify('-7 days');

        return $this->createQueryBuilder('h')
            ->andWhere('h.pet = :petId')
            ->andWhere('h.date >= :sevenDaysAgo')
            ->setParameter('petId', $petId)
            ->setParameter('sevenDaysAgo', $sevenDaysAgo->format('Y-m-d'))
            ->orderBy('h.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return HealthRecord[] Returns an array of HealthRecord objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HealthRecord
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
