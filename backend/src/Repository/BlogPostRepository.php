<?php

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPost>
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    //    /**
    //     * @return BlogPost[] Returns an array of BlogPost objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?BlogPost
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Récupère les statistiques des articles de blog pour l'administration
     */
    public function getBlogPostStats(): array
    {
        $qb = $this->createQueryBuilder('b');
        
        $total = $qb->select('COUNT(b.id)')->getQuery()->getSingleScalarResult();
        
        $published = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.isPublished = :published')
            ->setParameter('published', true)
            ->getQuery()
            ->getSingleScalarResult();
            
        $draft = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.isPublished = :published')
            ->setParameter('published', false)
            ->getQuery()
            ->getSingleScalarResult();
        
        return [
            'total' => (int) $total,
            'published' => (int) $published,
            'draft' => (int) $draft
        ];
    }

    /**
     * Récupère tous les articles avec l'auteur et les statistiques
     */
    public function findAllWithAuthorAndStats(): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.author', 'a')
            ->leftJoin('b.comments', 'c')
            ->leftJoin('b.likes', 'l')
            ->addSelect('a', 'c', 'l')
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
