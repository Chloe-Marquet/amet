<?php

namespace App\Controller;

use App\Entity\Images;
use App\Form\ImagesType;
use App\Repository\ImagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Albums;
use App\Form\AlbumsType;
use App\Repository\AlbumsRepository;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/images")
 */
class ImagesController extends AbstractController
{
    /**
     * @Route("/", name="images_index", methods={"GET"})
     */
    public function index(ImagesRepository $imagesRepository): Response
    {
        return $this->render('images/index.html.twig', [
            'images' => $imagesRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="images_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $myimage = new Images();
        $form = $this->createForm(ImagesType::class, $myimage);
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
                $myimage->setName($fichier);
                $now = new DateTime();
                $now->format('Y-m-d H:i:s');
                $now->getTimestamp();
                $myimage->setCreatedAt($now);

            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($myimage);
            $entityManager->flush();

            return $this->redirectToRoute('images_index');
        }

        return $this->render('images/new.html.twig', [
            'image' => $myimage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="images_show", methods={"GET"})
     */
    public function show(Images $image): Response
    {
        return $this->render('images/show.html.twig', [
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="images_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Images $myimage): Response
    {
        $form = $this->createForm(ImagesType::class, $myimage);
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
                $myimage->setName($fichier);
                $now = new DateTime();
                $now->format('Y-m-d H:i:s');
                $now->getTimestamp();
                $myimage->setCreatedAt($now);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('images_index');
        }

        return $this->render('images/edit.html.twig', [
            'image' => $myimage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="images_delete", methods={"POST"})
     */
    public function delete(Request $request, Images $image): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
        }

        return $this->redirectToRoute('images_index');
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
