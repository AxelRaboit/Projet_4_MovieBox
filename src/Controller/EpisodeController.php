<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Form\EpisodeType;
use App\Repository\EpisodeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/episode")
 */
class EpisodeController extends AbstractController
{
    /**
     * @Route("/", name="episode_index", methods={"GET"})
     */
    public function index(EpisodeRepository $episodeRepository): Response
    {
        return $this->render('episode/index.html.twig', [
            'episodes' => $episodeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin", name="episode_admin")
     */
    public function admin(Request $request, EpisodeRepository $episodeRepository, PaginatorInterface $paginatorInterface): Response
    {
        if(!$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('program_index');
        }

        $data = $episodeRepository->findAll();

        $episodes = $paginatorInterface->paginate(
            $data,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/crud/episode.html.twig', [
            'episodes' => $episodes,
        ]);
    }

    /**
     * @Route("/new", name="episode_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        if(!$this->isGranted('ROLE_ADMIN'))
        {
            return $this->redirectToRoute('home_index');
        }

        $episode = new Episode();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($episode);
            $entityManager->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('episode/new.html.twig', [
            'episode' => $episode,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="episode_show", methods={"GET"})
     */
    public function show(Episode $episode): Response
    {
        return $this->render('episode/show.html.twig', [
            'episode' => $episode,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="episode_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Episode $episode): Response
    {
        $user = $this->getUser();

        if($user == $this->isGranted('ROLE_ADMIN')) {

            $form = $this->createForm(EpisodeType::class, $episode);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
    
                return $this->redirectToRoute('program_index');
            }
    
            return $this->render('episode/edit.html.twig', [
                'episode' => $episode,
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
     * @Route("/{id}", name="episode_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Episode $episode): Response
    {
        $user = $this->getUser();

        if($user == $this->isGranted('ROLE_ADMIN')) {

            if ($this->isCsrfTokenValid('delete'.$episode->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($episode);
                $entityManager->flush();
    
                if ($episode->getImage() == true) {
                    $fileToDelete = __DIR__ . '/../../public/uploads/' . $episode->getImage();
                    if (file_exists($fileToDelete)) {
                        unlink($fileToDelete);
                    }
                }
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
