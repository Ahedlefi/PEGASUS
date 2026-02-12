<?php

namespace App\Controller\Frontoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ArtisteType;
use App\Form\NormalUserType;
use App\Form\SponsorType;
use App\Form\AdminType;
use App\Entity\Artiste;
use App\Entity\NormalUser;
use App\Entity\Sponsor;
use App\Entity\Admin;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserController extends AbstractController
{
    #[Route('/auth/sign-up', name: 'frontoffice_signup_role')]
    public function chooseRole(Request $request, SessionInterface $session): Response
    {
        // Clear any previous role
        $session->remove('signup_role');

        return $this->render('frontoffice/user/choose_role.html.twig');
    }

    #[Route('/auth/signup', name: 'frontoffice_signup_role_legacy')]
    public function chooseRoleLegacy(): Response
    {
        return $this->redirectToRoute('frontoffice_signup_role');
    }

    #[Route('/auth/sign-in', name: 'frontoffice_signin')]
    public function signIn(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('frontoffice/user/signin.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/auth/signin', name: 'frontoffice_signin_legacy')]
    public function signInLegacy(): Response
    {
        return $this->redirectToRoute('frontoffice_signin');
    }

    #[Route('/auth/forgot-password', name: 'frontoffice_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        if ($request->isMethod('POST')) {
            $emailInput = trim((string) $request->request->get('email'));
            $user = $userRepository->findOneBy(['email' => $emailInput]);

            if ($user !== null) {
                $token = bin2hex(random_bytes(32));
                $user->setResetPasswordToken($token);
                $user->setResetPasswordExpiresAt(new \DateTimeImmutable('+1 hour'));
                $em->flush();

                $resetUrl = $this->generateUrl(
                    'frontoffice_reset_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $message = (new Email())
                    ->from('hmimidahmed44@gmail.com')
                    ->to($user->getEmail() ?? '')
                    ->subject('Reset your password')
                    ->text("Use this link to reset your password:\n\n".$resetUrl."\n\nThis link expires in 1 hour.");

                $mailer->send($message);
            }

            $this->addFlash('success', 'If the email exists, a reset link has been sent.');

            return $this->redirectToRoute('frontoffice_signin');
        }

        return $this->render('frontoffice/user/forgot_password.html.twig');
    }

    #[Route('/auth/reset-password/{token}', name: 'frontoffice_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        Request $request,
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $userRepository->findOneBy(['resetPasswordToken' => $token]);
        $isValidToken = $user !== null
            && $user->getResetPasswordExpiresAt() !== null
            && $user->getResetPasswordExpiresAt() > new \DateTimeImmutable();

        if (!$isValidToken) {
            $this->addFlash('error', 'This reset link is invalid or expired.');

            return $this->redirectToRoute('frontoffice_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password');
            $confirmPassword = (string) $request->request->get('confirm_password');

            if (strlen($password) < 6) {
                $this->addFlash('error', 'Password must contain at least 6 characters.');
            } elseif ($password !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match.');
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $user->setResetPasswordToken(null);
                $user->setResetPasswordExpiresAt(null);
                $em->flush();

                $this->addFlash('success', 'Password updated successfully. You can now sign in.');

                return $this->redirectToRoute('frontoffice_signin');
            }
        }

        return $this->render('frontoffice/user/reset_password.html.twig', [
            'token' => $token,
        ]);
    }

    #[Route('/auth/sign-up/{role}', name: 'frontoffice_signup_form')]
    public function signup(
        Request $request,
        string $role,
        SessionInterface $session,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $session->set('signup_role', $role);
        $form = null;
        $entity = null;
        switch ($role) {
            case 'artiste':
                $entity = new Artiste();
                $form = $this->createForm(ArtisteType::class, $entity);
                break;
            case 'normal':
                $entity = new NormalUser();
                $form = $this->createForm(NormalUserType::class, $entity);
                break;
            case 'sponsor':
                $entity = new Sponsor();
                $form = $this->createForm(SponsorType::class, $entity);
                break;
            case 'admin':
                $entity = new Admin();
                $form = $this->createForm(AdminType::class, $entity);
                break;
            default:
                return $this->redirectToRoute('frontoffice_signup_role');
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity->setPassword($passwordHasher->hashPassword($entity, $entity->getPassword()));
            $em->persist($entity);
            $em->flush();
            $session->remove('signup_role');

            return $this->redirectToRoute('app_frontoffice_home');
        }

        return $this->render('frontoffice/user/signup.html.twig', [
            'form' => $form->createView(),
            'role' => $role,
        ]);
    }

    #[Route('/auth/signup/{role}', name: 'frontoffice_signup_form_legacy')]
    public function signupLegacy(string $role): Response
    {
        return $this->redirectToRoute('frontoffice_signup_form', ['role' => $role]);
    }
}
