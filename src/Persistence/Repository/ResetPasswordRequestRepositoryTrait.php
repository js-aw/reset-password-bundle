<?php

/*
 * This file is part of the SymfonyCasts ResetPasswordBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\ResetPassword\Persistence\Repository;

use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;

/**
 * Trait can be added to a Doctrine ORM repository to help implement
 * ResetPasswordRequestRepositoryInterface.
 *
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
trait ResetPasswordRequestRepositoryTrait
{
    // public function getUserIdentifier(object $user): string
    // {
    //     return (string) $this->getEntityManager()
    //         ->getUnitOfWork()
    //         ->getSingleIdentifierValue($user)
    //     ;
    // }

    public function persistResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->getEntityManager()->persist($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    public function findResetPasswordRequest(string $selector): ?ResetPasswordRequestInterface
    {
        return $this->findOneBy(['selector' => $selector]);
    }

    public function getMostRecentNonExpiredRequestDate(object $user): ?\DateTimeInterface
    {
        // Normally there is only 1 max request per use, but written to be flexible
        /** @var ResetPasswordRequestInterface $resetPasswordRequest */
        $resetPasswordRequest = $this->createQueryBuilder('t')
            ->where('t.user_id = :user')
            ->setParameter('user', $user->getId())
            ->orderBy('t.requestedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null !== $resetPasswordRequest && !$resetPasswordRequest->isExpired()) {
            return $resetPasswordRequest->getRequestedAt();
        }

        return null;
    }

    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->where('t.user_id = :user')
            ->setParameter('user', $resetPasswordRequest->getUserId())
            ->getQuery()
            ->execute()
        ;
    }

    public function removeExpiredResetPasswordRequests(): int
    {
        $time = new \DateTimeImmutable('-1 week');
        $query = $this->createQueryBuilder('t')
            ->delete()
            ->where('t.expiresAt <= :time')
            ->setParameter('time', $time)
            ->getQuery()
        ;

        return $query->execute();
    }

    /**
     * Remove a users ResetPasswordRequest objects from persistence.
     *
     * Warning - This is a destructive operation. Calling this method
     * may have undesired consequences for users who have valid
     * ResetPasswordRequests but have not "checked their email" yet.
     *
     * @see https://github.com/SymfonyCasts/reset-password-bundle?tab=readme-ov-file#advanced-usage
     */
    public function removeRequests(object $user): void
    {
        $query = $this->createQueryBuilder('t')
            ->delete()
            ->where('t.user_id = :user')
            ->setParameter('user', $user->getId())
        ;

        $query->getQuery()->execute();
    }
}
