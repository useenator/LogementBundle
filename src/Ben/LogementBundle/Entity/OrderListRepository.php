<?php

namespace Ben\LogementBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * OrderListRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrderListRepository extends EntityRepository
{
	public function findbyLogement($logement) {     
       $qb = $this->createQueryBuilder('o')
                ->leftJoin('o.logement', 'l')
                ->andWhere('l.id = :logement')
                ->setParameter('logement', $logement)
                ->Orderby('o.created', 'DESC')
                ;
        return $qb->getQuery()->getResult();
    }
}