<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UpdatePasswordType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin", name="user_admin")
     */
    public function admin(UserRepository $userRepository): Response
    {
        if(!$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('program_index');
        }

        return $this->render('admin/crud/user.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if(!$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('program_index');
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        $currentUser = $this->getUser();

        if($currentUser == $user) {
            return $this->render('user/show.html.twig', [
                'user' => $user,
            ]);
        } else {
            if($this->isGranted('ROLE_USER')) {
                return $this->redirectToRoute('home_index');
            } else {
                return $this->redirectToRoute('app_login');
            }
        }
    }

    /**
     * @Route("/profile/{id}", name="user_profile", methods={"GET"})
     */
    public function myProfile(User $user): Response
    {
        $currentUser = $this->getUser();

        if($currentUser == $user) {
            return $this->render('user/profile.html.twig', [
                'user' => $user,
            ]);

        } else {

            if($this->isGranted('ROLE_USER')) {
                return $this->redirectToRoute('home_index');
            } else {
                return $this->redirectToRoute('app_login');
            }
        }
    }

    /**
     * @Route("/update_password/{id}", name="user_update_password")
     */
    public function updatePassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        User $user
    ): Response {

        $currentUser = $this->getUser();

        if($currentUser == $user) {

            $form = $this->createForm(UpdatePasswordType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($passwordEncoder->isPasswordValid($user, $form->get('oldPassword')->getData())) {
                    $user->setPassword(
                        $passwordEncoder->encodePassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    );
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->addFlash('success', 'Votre mot de passe à bien été changé !');
                    return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
                } else {
                    $form->addError(new FormError('Votre ancien mot de passe est incorrect'));
                }
            }
    
            return $this->render('user/update_password.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
            ])
            ;

        } else {

            if($this->isGranted('ROLE_USER')) {
                return $this->redirectToRoute('home_index');
            } else {
                return $this->redirectToRoute('app_login');
            }
        }
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $currentUser = $this->getUser();

        if($currentUser == $user) {

            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
    
                return $this->redirectToRoute('user_index');
            }
    
            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
            ]);

        } else {

            if($this->isGranted('ROLE_USER')) {
                return $this->redirectToRoute('home_index');
            } else {
                return $this->redirectToRoute('app_login');
            }
        }
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        $currentUser = $this->getUser();

        if($currentUser == $user) {

            if(!$this->isGranted('ROLE_USER'))
            {
                return $this->redirectToRoute('program_index');
            }
    
            if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($user);
                $entityManager->flush();
            }
            return $this->redirectToRoute('user_index');

        } else {

            if($this->isGranted('ROLE_USER')) {
                return $this->redirectToRoute('home_index');
            } else {
                return $this->redirectToRoute('app_login');
            }
        }
    }
}
