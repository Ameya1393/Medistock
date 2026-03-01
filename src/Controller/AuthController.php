<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If user is already logged in, redirect to dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class);

        return $this->render('auth/login.html.twig', [
            'loginForm' => $form,
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // If user is already logged in, redirect to dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the plain password
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Check if this is the first user (no users exist)
            $existingUsers = $entityManager->getRepository(User::class)->count([]);
            $isFirstUser = $existingUsers === 0;
            
            // Check admin registration options
            $registerAsAdmin = $form->get('registerAsAdmin')->getData();
            $adminCode = trim($form->get('adminCode')->getData() ?? '');
            $validAdminCode = $adminCode === 'ADMIN2024';
            
            // Set role: Admin if first user OR checkbox checked OR valid admin code
            if ($isFirstUser || $registerAsAdmin || $validAdminCode) {
                $user->setRoles(['ROLE_ADMIN']);
                if ($isFirstUser) {
                    $this->addFlash('success', 'Registration successful! You are the first user and have been registered as an Admin. Please login with your credentials.');
                } else {
                    $this->addFlash('success', 'Registration successful! You have been registered as an Admin. Please login with your credentials.');
                }
            } else {
                $user->setRoles(['ROLE_USER']);
                $this->addFlash('success', 'Registration successful! Please login with your credentials.');
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

