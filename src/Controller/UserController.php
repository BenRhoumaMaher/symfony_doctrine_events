<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/register', name: 'user_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $data = $request->toArray();

        $user = new User();
        $user->setEmail($data['email']);

        $user->setPlainPassword($data['password']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(
            [
            'message' => 'The User was created successfully',
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'stored_password' => $user->getPassword()
            ]
        );
    }
}
