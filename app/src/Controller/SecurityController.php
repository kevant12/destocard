<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegisterFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Le code ici ne sera jamais exécuté
        // Le composant de sécurité de Symfony intercepte la requête avant
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response {
        // Si l'utilisateur est déjà connecté, rediriger
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new Users();
        $form = $this->createForm(RegisterFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            
            // Définir les rôles par défaut
            $user->setRoles(['ROLE_USER']);
            
            // Créer le token de vérification
            $token = $tokenGenerator->generateToken();
            $user->setVerificationToken($token);
            $user->setVerificationTokenExpiresAt(new \DateTimeImmutable('+24 hours'));
            $user->setIsVerified(false);
            $user->setCreatedAt(new \DateTimeImmutable());
            
            // Sauvegarder l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoyer l'email de vérification
            $this->sendVerificationEmail($user, $mailer);

            $this->addFlash('success', "Un email de confirmation vient de vous être envoyé. Veuillez cliquer sur le lien reçu pour activer votre compte.");

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registerForm' => $form->createView(),
            'error' => null,
        ]);
    }

    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyEmail(string $token, UsersRepository $usersRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $usersRepository->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Token de vérification invalide.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->getVerificationTokenExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Le token de vérification a expiré.');
            return $this->redirectToRoute('app_login');
        }

        // Activer le compte
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $user->setVerificationTokenExpiresAt(null);
        
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/reset-password-request', name: 'app_reset_password_request')]
    public function resetPasswordRequest(
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $usersRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Créer le token de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));
                
                $entityManager->flush();

                // Envoyer l'email de réinitialisation
                $this->sendResetPasswordEmail($user, $mailer);
            }

            // Toujours afficher le même message pour des raisons de sécurité
            $this->addFlash('success', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $usersRepository->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Token de réinitialisation invalide.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Le token de réinitialisation a expiré.');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($hashedPassword);
            
            // Supprimer le token
            $user->setResetToken(null);
            $user->setResetTokenExpiresAt(null);
            
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès !');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    private function sendVerificationEmail(Users $user, MailerInterface $mailer): void
    {
        $email = (new Email())
            ->from('noreply@votre-site.com')
            ->to($user->getEmail())
            ->subject('Vérification de votre compte')
            ->html($this->renderView('emails/verification.html.twig', [
                'user' => $user,
                'verificationUrl' => $this->generateUrl('app_verify_email', [
                    'token' => $user->getVerificationToken()
                ], UrlGeneratorInterface::ABSOLUTE_URL)
            ]));

        $mailer->send($email);
    }

    private function sendResetPasswordEmail(Users $user, MailerInterface $mailer): void
    {
        $email = (new Email())
            ->from('noreply@votre-site.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html($this->renderView('emails/reset_password.html.twig', [
                'user' => $user,
                'resetUrl' => $this->generateUrl('app_reset_password', [
                    'token' => $user->getResetToken()
                ], UrlGeneratorInterface::ABSOLUTE_URL)
            ]));

        $mailer->send($email);
    }
} 