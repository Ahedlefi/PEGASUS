<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/admin_page', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/admin/base_admin.html.twig', [
            'controller_name' => 'Admin/DashboardController',
        ]);
    }

    #[Route('/api/users', name: 'api_users_list', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sortBy', 'id');
        $sortOrder = $request->query->get('sortOrder', 'ASC');
        
        // Validate sort parameters
        $allowedSortFields = ['id', 'username', 'email', 'createdAt', 'status'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }
        if (!in_array(strtoupper($sortOrder), ['ASC', 'DESC'])) {
            $sortOrder = 'ASC';
        }
        
        // Build query
        $qb = $userRepository->createQueryBuilder('u');
        
        // Apply search filter
        if (!empty($search)) {
            $qb->where('u.username LIKE :search')
               ->orWhere('u.email LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        // Apply sorting
        $qb->orderBy('u.' . $sortBy, $sortOrder);
        
        $users = $qb->getQuery()->getResult();
        
        $usersData = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
                'avatarUrl' => $user->getAvatarUrl(),
                'roles' => $user->getRoles(),
                'status' => $user->getStatus()->value,
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                'type' => method_exists($user, 'getDiscriminatorValue') ? 
                    $user->getDiscriminatorValue() : 
                    strtolower((new \ReflectionClass($user))->getShortName())
            ];
        }, $users);
        
        return $this->json($usersData);
    }
}
