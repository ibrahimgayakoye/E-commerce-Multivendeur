<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UsersRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/oubli-pass', name:'forgotten_password')]
    public function forgottePassword(Request $request,
    UsersRepository $userRepository,
    TokenGeneratorInterface $tokenGeneratorInterface,
    EntityManagerInterface $em,
    SendMailService $mail
    ): Response

    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // on va chercher l'utilisateur par son mail
            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // on verifie si on a un utilisateur
            if($user){
                // on genere un token de renitialisation
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                $em->persist($user);
                $em->flush();

                // On genere un lien de reenitialisation du mot de passe
                $url = $this->generateUrl('reset_pass',['token'=>$token],
                UrlGeneratorInterface::ABSOLUTE_PATH
            );

            // On cree les donnees du mail
            $context = compact('url','user');

            // Envoi du mail

            $mail->send(
                'no-reply@e-commerce.fr',
                $user->getEmail(),
                'Reinitialisation du mot de passe',
                'password_reset',
                $context
            );

            $this->addFlash('success','Email envoye avec success');
            return $this->redirectToRoute('app_login');



            }

            $this->addFlash('danger', 'Une erreure est survenue');
            return $this->redirectToRoute('app_login');
        } 

        return $this->render('security/reset_password_request.html.twig',[
            'requestPassForm' => $form->createView(),
           
        ]);
    }

    #[Route('/oublis-pass/{token}', name:'reset_pass')]
    public function resetPass(
    string $token, 
    Request $request,
    UsersRepository $usersRepository,
    EntityManagerInterface $em,
    UserPasswordHasherInterface $userPasswordHasherInterface
    ):Response
    { 
       // on verifie si on a ce token dans la base
       $user = $usersRepository->findOneByResetToken($token);
       $form = $this->createForm(ResetPasswordFormType::class);
       $form->handleRequest($request);


       if($user){
           if($form->isSubmitted() && $form->isValid()){
              // on efface le token
              $user->setResetToken('');
              $user->setPassword(
                 $userPasswordHasherInterface->hashPassword(
                    $user,
                    $form->get('password')->getData()
                 )
                 );
                 $em->persist($user);
                 $em->flush();

            
                 $this->addFlash('primary','Mot de passe modifier avec success');

                 // On redirige vers la page de login
                  return $this->redirectToRoute('app_login');
                 
           }
       }

         
        return $this->render('security/reset_password.html.twig',[
           'passForm' => $form->createView()
        ]);
       


    }
}
