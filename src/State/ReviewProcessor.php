<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;


class ReviewProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private PersistProcessor $persistProcessor
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ) {
        if ($data instanceof Review && $operation instanceof Post) {
            $user = $this->security->getUser();

            if (!$user) {
                throw new LogicException('User not authenticated');
            }

            $data->setUser($user);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
