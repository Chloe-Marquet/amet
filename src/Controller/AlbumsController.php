<?php

namespace App\Controller;

use App\Entity\Albums;
use App\Form\AlbumsType;
use App\Entity\Images;
use App\Repository\AlbumsRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/albums")
 */
class AlbumsController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/", name="albums_index", methods={"GET"})
     */
    public function index(AlbumsRepository $albumsRepository): Response
    {
        return $this->render('albums/index.html.twig', [
            'albums' => $albumsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="albums_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $album = new Albums();
        $form = $this->createForm(AlbumsType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $images = $form->get('images')->getData();
            // ON boucle sur les images

            foreach($images as $image){
                //on genere un nouveau nom de fichier
                $fichier = md5(uniqid()).'.'.$image->guessExtension();

                // on copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );

                // on stocke l'image dans la bdd
                $img = new Images();
                $img->setName($fichier);
                $now = new DateTime();
                $now->format('Y-m-d H:i:s');
                $now->getTimestamp();
                $img->setCreatedAt($now);
                $album->addImage($img);
                $user = $this->security->getUser();
                $img->setUser($user);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($album);
            $entityManager->flush();

            return $this->redirectToRoute('albums_index');
        }

        return $this->render('albums/new.html.twig', [
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="albums_show", methods={"GET"})
     */
    public function show(Albums $album): Response
    {
        return $this->render('albums/show.html.twig', [
            'album' => $album,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="albums_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Albums $album): Response
    {
        $form = $this->createForm(AlbumsType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();
            // On boucle sur les images
            foreach($images as $image){
                //on genere un nouveau nom de fichier
                $fichier = md5(uniqid()).'.'.$image->guessExtension();

                // on copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                // on stocke l'image dans la bdd
                $img = new Images();
                $img->setName($fichier);//nom
                $now = new DateTime();
                $now->format('Y-m-d H:i:s');
                $now->getTimestamp();
                $img->setCreatedAt($now); //date
                $album->addImage($img);
                $user = $this->security->getUser();
                $img->setUser($user);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('albums_index');
        }

        return $this->render('albums/edit.html.twig', [
            'album' => $album,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="albums_delete", methods={"POST"})
     */
    public function delete(Request $request, Albums $album): Response
    {
        if ($this->isCsrfTokenValid('delete'.$album->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($album);
            $entityManager->flush();
        }

        return $this->redirectToRoute('albums_index');
    }

    /**
     * @Route("/supprime/image/{id}", name ="albums_delete_image", methods={"DELETE"})
     */
    public function deleteImage(Images $image, Request $request){
        $data = json_decode($request->getContent(), true);
        // on vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])){
            //on récupere le nom de l'image
            $nom = $image->getName();
            //on supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$nom);
            //on supprime l'entrée de la base
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            // on répond en json
            return new JsonResponse( ['success' =>1]);
        }else{
            return new JsonResponse(['error'=> 'Token Invalide'], 400);
        }
    }
}
