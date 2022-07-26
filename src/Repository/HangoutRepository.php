<?php

namespace App\Repository;

use App\Entity\Hangout;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hangout>
 *
 * @method Hangout|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hangout|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hangout[]    findAll()
 * @method Hangout[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HangoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hangout::class);
    }

    public function add(Hangout $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Hangout $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }



    public function findByFilter($campus,$name,$startDate,$endDate,$imOrginizer,$imIn,$imNotIn,$pastHangout,$user){
        $queryFilter = $this->createQueryBuilder('h')
            ->leftJoin('h.Status','s')
            ->leftJoin('h.hangouts','u') // hangouts correpond à la collection de users dans la table Hangout
            ->addSelect('s','u')
            ->where('s <> 6')
            ->andWhere('s <> 7')
            ->orderBy('h.startTime', 'ASC');
        if($campus){
            $queryFilter->andWhere('h.campusOrganizerSite =:campus')
                ->setParameter('campus', $campus);
        }
        if($name){
            $queryFilter->andWhere('h.name LIKE :name')
                ->setParameter('name', '%'.$name.'%');
        }
        if($startDate && $endDate) {
            $queryFilter->andWhere('h.startTime BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }
        if($imOrginizer) {
            $queryFilter->andWhere('h.organizer  =:user')
                ->setParameter('user', $user);
        }
        if ($imIn and !$imNotIn) {
            $queryFilter->join('h.hangouts', 'sub')
                ->andWhere('sub = :user')
                ->setParameter('user', $user);
        }

        if ($imNotIn and !$imIn) {
            $queryFilter->leftJoin('h.hangouts', 'u')
                ->leftJoin('h.Status', 's')
                ->addSelect('s','u')
                ->andWhere('u.id <> :user')
                ->andWhere('h.organizer <> :user')
                ->andWhere('s.id <> 7')
                ->andWhere('s.id <> 6')
                ->setParameter('user', $user);
        }
        if ($pastHangout) {
            $queryFilter->andWhere('h.Status = 5')
                ->orderBy('h.startTime', 'ASC');
        }

        $res = $queryFilter->getQuery();
        return $res->getResult();
    }

    /**
     * @return Hangout[] Return un tableau de sortie dont l'user n'est pas inscrit
     */
    public function findByStatusCanceled($idUser): array
    {
        return $this->createQueryBuilder('h')
            ->join('h.Status', 's')
            ->join('h.hangouts', 'u')
            ->where('s.id = 6')
            ->setParameter('idUser', $idUser)
            ->andWhere('u.id = :idUser')
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /*
     * on affiche les sortie en filtrant les sortie finit depuis un moi */
    public function findHangoutAvaible(): array
    {
        $qb = $this->createQueryBuilder('h');
        $qb->leftJoin('h.hangouts','u')
        ->addSelect('u')   ;
           $qb->andWhere('h.Status <>  7') ;// ne pas inclure les sorties archivées
            $qb->andWhere('h.Status <> 6'); // ne pas inclure les sorties annulées
            $qb->orderBy('h.startTime', 'ASC');
            $query = $qb->getQuery();
            $result = $query->getResult();

        return $result;
    }


}
