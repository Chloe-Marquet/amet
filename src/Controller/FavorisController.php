<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Form\FavorisType;
use App\Repository\FavorisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Albums;
use App\Form\AlbumsType;
use App\Entity\Images;
use App\Repository\AlbumsRepository;
use DateTime;;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;


/**
 * @Route("/favoris")
 */
class FavorisController extends AbstractController
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
     * @Route("/", name="favoris_index", methods={"GET"})
     */
    public function index(FavorisRepository $favorisRepository): Response
    {
        return $this->render('favoris/index.html.twig', [
            'favoris' => $favorisRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="favoris_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $favori = new Favoris();
        $form = $this->createForm(FavorisType::class, $favori);
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
                $favori->addImage($img);
                $user = $this->security->getUser();
                $img->setUser($user);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($favori);
            $entityManager->flush();

            return $this->redirectToRoute('favoris_index');
        }

        return $this->render('favoris/new.html.twig', [
            'favori' => $favori,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="favoris_show", methods={"GET"})
     */
    public function show(Favoris $favori): Response
    {
        return $this->render('favoris/show.html.twig', [
            'favori' => $favori,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="favoris_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Favoris $favori): Response
    {
        $form = $this->createForm(FavorisType::class, $favori);
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
                $favori->addImage($img);
                $user = $this->security->getUser();
                $img->setUser($user);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('favoris_index');
        }

        return $this->render('favoris/edit.html.twig', [
            'favori' => $favori,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="favoris_delete", methods={"POST"})
     */
    public function delete(Request $request, Favoris $favori): Response
    {
        if ($this->isCsrfTokenValid('delete'.$favori->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($favori);
            $entityManager->flush();
        }

        return $this->redirectToRoute('favoris_index');
    }


    /**
     * @Route("/supprime/image/{id}", name ="favoris_delete_image", methods={"DELETE"})
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
