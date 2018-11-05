<?php

namespace AppBundle\Entity\Repository;

class TaskRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOneByIdQuery($id)
    {
        $query = $this->_em->createQuery(
            "
            SELECT p
            FROM AppBundle:Task p
            WHERE p.id = :id
            "
        );
        $query->setParameter('id', $id);
        return $query;
    }
    public function findAllQuery()
    {
        $query = $this->_em->createQuery(
            "
            SELECT p
            FROM AppBundle:Task p
            "
        );
        return $query;
    }

    public function findOnlyOwnByIdQuery($id, $userId)
    {
        $query = $this->_em->createQuery(
            "
            SELECT p
            FROM AppBundle:Task p
            WHERE p.id = :id 
            AND p.user = :userId
            "
        );
        $query->setParameter('id', $id);
        $query->setParameter('userId', $userId);
        return $query;
    }

    public function findOnlyOwnQuery( $userId)
    {
        $query = $this->_em->createQuery(
            "
            SELECT p
            FROM AppBundle:Task p
            WHERE p.user = :userId
            "
        );
        $query->setParameter('userId', $userId);
        return $query;
    }

    public function deleteQuery($id, $userId)
    {
        $query = $this->_em->createQuery(
            "
            DELETE 
            FROM AppBundle:Task p
            WHERE p.id = :id
            AND p.user = :userId
            "
        );
        $query->setParameter('id', $id);
        $query->setParameter('userId', $userId);
        return $query;
    }

}
