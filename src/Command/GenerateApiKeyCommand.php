<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-api-key',
    description: 'Generate an API key for a user',
)]
class GenerateApiKeyCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'User email address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $style->error(sprintf('User with email "%s" not found.', $email));
            return Command::FAILURE;
        }

        $apiKey = $this->generateApiKey();
        $prefix = substr($apiKey, 0, 16);
        $hash = hash('sha256', $apiKey);

        $user->setApiKeyHash($hash);
        $user->setApiKeyPrefix($prefix);
        $user->setApiKeyEnabled(true);
        $user->setApiKeyCreatedAt(new DateTimeImmutable());

        $this->entityManager->flush();

        $style->success('API Key generated successfully!');
        $style->section('User Information');
        $style->table(
            ['Field', 'Value'],
            [
                ['Email', $user->getEmail()],
                ['API Key Prefix', $prefix],
                ['API Key Enabled', $user->isApiKeyEnabled() ? 'Yes' : 'No'],
                ['Created At', $user->getApiKeyCreatedAt()?->format('Y-m-d H:i:s')],
            ]
        );

        $style->warning('IMPORTANT: Copy this API key now. You will not be able to see it again!');
        $style->text([
            '',
            'Full API Key:',
            sprintf('<fg=green>%s</>', $apiKey),
            '',
        ]);

        return Command::SUCCESS;
    }

    private function generateApiKey(): string
    {
        $randomBytes = random_bytes(32);
        $hexKey = bin2hex($randomBytes);

        return $hexKey;
    }
}
