<?php

namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Form\AddAlbumType;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Knp\Component\Pager\Paginator;
use ReviewBundle\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TrackBundle\Entity\Track;

/**
 * Class AlbumController
 * @package AlbumBundle\Controller
 */
class AlbumController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository(Album::class)->getAlbums();


        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $albums = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), $this->getParameter('page_limit')
        );

        $rating = [];

        /** @var Album $album $album */
        foreach ($albums as $album) {
            $totalReviews = 0;
            $totalRating = 0;
            $albumRating = 0;

            $tracks = $em->getRepository(Track::class)
                ->getTracksByAlbumID($album)
                ->getResult();

            $album->setAlbumTracks($tracks);

            $query = $em->getRepository(Review::class)
                ->getReviewsByAlbumID($album);

            $reviews = $query->getResult();

            /* @var Review $review */
            foreach ($reviews as $review) {
                $totalReviews++;
                $totalRating  += $review->getRating();
            }

            if ($totalRating !== 0) {
                $albumRating = $totalRating / $totalReviews;
            }

            /* @var Album $album */
            $rating[$album->getId()] = $albumRating;
        }

        return $this->render('AlbumBundle:Default:index.html.twig', [
            "albums" => $albums,
            "rating" => $rating
        ]);
    }

    /**
     * @param string $originalFileName
     * @param File $uploadedFile
     * @return string
     */
    private static function hashImageName($originalFileName, File $uploadedFile)
    {
        return $originalFileName . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function addAlbumAction(Request $request)
    {
        $album = new Album();
        $form = $this->createForm(AddAlbumType::class, $album);
        // only handles data on POST requests
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['image']->getData();

            $originalFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFileName = $this->hashImageName($originalFileName, $uploadedFile);

            try {
                $em = $this->getDoctrine()->getManager();
                $album->setImage($newFileName);
                $tracks = $form['albumTracks']->getData();

                /**
                 * @var Track $track
                 */
                foreach ($tracks as $track) {
                    $track->setAlbum($album);
                }

                $em->persist($album);
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $message = sprintf('DBALException [%i]: %s'.$e->getMessage(), $e->getCode());
            } catch (TableNotFoundException $e) {
                $message = sprintf('ORMException [%i]: %s', $e->getCode(), $e->getMessage());
            } catch (Exception $e) {
                $message = sprintf('Exception [%i]: %s', $e->getCode(), $e->getMessage());
            }

            if (isset($message)) {
                $this->addFlash('error', 'Failed to create album! Try again.');
                return $this->redirect($this->generateUrl('add_album'));
            }
            else {
                $this->addFlash('success', 'Album created');
            }

            // save image
            $destination = $this->getParameter('uploads_directory');
            $uploadedFile->move($destination, $newFileName);
        }


        return $this->render('AlbumBundle:Default:add_album.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Deletes album (only admins allowed).
     *
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAlbumAction(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        try {
            $entry = $entityManager->getRepository(Album::class)
                ->find($id);

            $entityManager->remove($entry);
            $entityManager->flush();

            $this->addFlash('success', 'Album created');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to delete album!');
        }

        return $this->redirect($this->generateUrl('album_homepage'));
    }
}
