<?php

namespace App\Service;

use App\Entity\Messages;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;

class MessageService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Récupère toutes les conversations de l'utilisateur (utilisateurs distincts avec qui il a échangé)
     */
    public function getUserConversations(Users $user): array
    {
        $repo = $this->em->getRepository(Messages::class);
        // On récupère tous les utilisateurs avec qui il y a eu un échange
        $qb = $repo->createQueryBuilder('m')
            ->select('DISTINCT CASE WHEN m.sender = :user THEN m.receper ELSE m.sender END as otherUser')
            ->where('m.sender = :user OR m.receper = :user')
            ->setParameter('user', $user);
        $result = $qb->getQuery()->getResult();
        return array_column($result, 'otherUser');
    }

    /**
     * Récupère tous les messages entre deux utilisateurs
     */
    public function getConversation(Users $user1, Users $user2): array
    {
        $repo = $this->em->getRepository(Messages::class);
        return $repo->createQueryBuilder('m')
            ->where('(m.sender = :user1 AND m.receper = :user2) OR (m.sender = :user2 AND m.receper = :user1)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('m.expeditionDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Envoie un message de user1 à user2
     */
    public function sendMessage(Users $sender, Users $receper, string $content): void
    {
        $message = new Messages();
        $message->setSender($sender);
        $message->setReceper($receper);
        $message->setContent($content);
        $message->setExpeditionDate(new \DateTimeImmutable());
        $message->setStatus('envoyé');
        $this->em->persist($message);
        $this->em->flush();
    }
} 