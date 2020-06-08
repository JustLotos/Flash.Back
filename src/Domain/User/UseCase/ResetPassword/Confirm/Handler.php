<?php

declare(strict_types=1);

namespace App\Domain\User\UseCase\ResetPassword\Confirm;

use App\Domain\User\Entity\User;
use App\Domain\User\UserRepository;
use App\Domain\User\Service\PasswordEncoder;
use App\Service\FlushService;
use App\Service\ValidateService;
use DateTimeImmutable;
use DomainException;

class Handler
{
    /** @var UserRepository */
    private $repository;
    /** @var Flusher */
    private $flusher;
    /** @var ValidateService */
    private $validator;

    public function __construct(
        UserRepository $repository,
        ValidateService $validator,
        FlushService $flusher
    ) {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->validator = $validator;
    }

    public function handle(Command $command): void
    {
        $this->validator->validate($command);

        /** @var User $user */
        if (!$user = $this->repository->findByConfirmToken($command->token)) {
            throw new DomainException('Incorrect or confirmed token.');
        }

        $user->confirmResetPassword(new DateTimeImmutable());
        var_dump($user);
        $this->flusher->flush();
    }
}