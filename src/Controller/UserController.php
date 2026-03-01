<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRoleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/admin/users', name: 'app_user_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/{id}/edit-role', name: 'app_user_edit_role', methods: ['GET', 'POST'])]
    public function editRole(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Prevent admin from removing their own admin role
        if ($user->getId() === $this->getUser()->getId()) {
            $this->addFlash('warning', 'You cannot change your own role.');
            return $this->redirectToRoute('app_user_index');
        }

        $form = $this->createForm(UserRoleType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRole = $form->get('role')->getData();
            $user->setRoles([$selectedRole]);
            
            $entityManager->flush();

            $this->addFlash('success', 'User role updated successfully!');

            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/edit_role.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}

