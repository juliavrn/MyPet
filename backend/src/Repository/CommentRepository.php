<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findApprovedCommentsByBlogPost(int $blogPostId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.blogPost = :blogPostId')
            ->andWhere('c.isApproved = :isApproved')
            ->setParameter('blogPostId', $blogPostId)
            ->setParameter('isApproved', true)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingComments(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isApproved = :isApproved')
            ->setParameter('isApproved', false)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les statistiques des commentaires pour l'administration
     */
    public function getCommentStats(): array
    {
        $qb = $this->createQueryBuilder('c');
        
        $total = $qb->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();
        
        $approved = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.isApproved = :approved')
            ->setParameter('approved', true)
            ->getQuery()
            ->getSingleScalarResult();
            
        $pending = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.isApproved = :approved')
            ->setParameter('approved', false)
            ->getQuery()
            ->getSingleScalarResult();
        
        return [
            'total' => (int) $total,
            'approved' => (int) $approved,
            'pending' => (int) $pending
        ];
    }

    /**
     * Récupère tous les commentaires avec l'auteur et l'article de blog
     */
    public function findAllWithAuthorAndBlogPost(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.author', 'a')
            ->leftJoin('c.blogPost', 'b')
            ->addSelect('a', 'b')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 