<?php

declare(strict_types=1);

namespace App\Domain\User\UseCase\Change\Email\Confirm;

use App\Domain\User\Entity\User;
use App\Domain\User\UserRepository;
use App\Domain\User\Service\PasswordEncoder;
use App\Service\FlushService;
use App\Service\MailService\BaseMessage;
use App\Service\MailService\MailBuilderService;
use App\Service\MailService\MailSenderService;
use App\Service\ValidateService;
use DateTimeImmutable;
use DomainException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Handler
{
    private $repository;
    private $flusher;
    private $validator;
    private $sender;
    private $builder;
    private $generator;

    public function __construct(
        UserRepository $repository,
        ValidateService $validator,
        FlushService $flusher,
        MailSenderService $sender,
        MailBuilderService $builder,
        UrlGeneratorInterface $generator
    ) {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->validator = $validator;
        $this->sender = $sender;
        $this->builder = $builder;
        $this->generator = $generator;
    }

    public function handle(Command $command): void
    {
        $this->validator->validate($command);

        /** @var User $user */
        if (!$user = $this->repository->findByConfirmToken($command->token)) {
            throw new DomainException('Invalid or not found token.');
        }

        $user->confirmChangeEmail(new DateTimeImmutable());
        $this->flusher->flush();
        $this->sendConfirmMessage($user);
    }

    public function sendConfirmMessage(User $user): void
    {
        $message = BaseMessage::getDefaultMessage(
            $user->getEmail(),
            'Успешная смена email в приложении Flash',
            'Успешная смена email в приложении Flash',
            $this->builder->build('mail/user/change/email/confirm.html.twig')
        );

        $this->sender->send($message);
    }
}
