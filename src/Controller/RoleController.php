<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/role")
 */
class RoleController extends AbstractController
{
    /**
     * @Route("/", name="role_index", methods={"GET"})
     */
    public function index(RoleRepository $roleRepository): Response
    {
        return $this->render('role/index.html.twig', [
            'roles' => $roleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin", name="role_admin")
     */
    public function admin(RoleRepository $roleRepository): Response
    {
        if(!$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('program_index');
        }

        return $this->render('admin/crud/role.html.twig', [
            'roles' => $roleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="role_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($role);
            $entityManager->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('role/new.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="role_show", methods={"GET"})
     */
    public function show(Role $role): Response
    {
        return $this->render('role/show.html.twig', [
            'role' => $role,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="role_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Role $role): Response
    {
        $user = $this->getUser();

        if($user == $this->isGranted('ROLE_ADMIN')) {

            $form = $this->createForm(RoleType::class, $role);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
    
                return $this->redirectToRoute('program_index');
            }
    
            return $this->render('role/edit.html.twig', [
                'role' => $role,
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
     * @Route("/{id}", name="role_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Role $role): Response
    {
        $user = $this->getUser();

        if($user == $this->isGranted('ROLE_ADMIN')) {
            if ($this->isCsrfTokenValid('delete'.$role->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($role);
                $entityManager->flush();
            }
            return $this->redirectToRoute('program_index');
      
        } else {
            if($this->isGranted('ROLE_USER')) {
                return $this->redirectToRoute('home_index');
            } else {
                return $this->redirectToRoute('app_login');
            }
        }
    }
}