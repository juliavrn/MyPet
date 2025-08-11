<?php

namespace App\Repository;

use App\Entity\Like;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Like>
 */
class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    public function findLikeByUserAndBlogPost(int $userId, int $blogPostId): ?Like
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :userId')
            ->andWhere('l.blogPost = :blogPostId')
            ->setParameter('userId', $userId)
            ->setParameter('blogPostId', $blogPostId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countLikesByBlogPost(int $blogPostId): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.blogPost = :blogPostId')
            ->setParameter('blogPostId', $blogPostId)
            ->getQuery()
            ->getSingleScalarResult();
    }
} 