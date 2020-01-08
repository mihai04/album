<?php

namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Exceptions\AlbumExistsException;
use AlbumBundle\Form\AddAlbumType;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Knp\Component\Pager\Paginator;
use ReviewBundle\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class AlbumController extends Controller
{
//     private $knpUIpsum;
//
//    /**
//     * AlbumController constructor.
//     * @param $knpUIpsum
//     */
//    public function __construct(KnpUIpsum $knpUIpsum)
//    {
//        $this->knpUIpsum = $knpUIpsum;
//    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
//        dump($this->knpUIpsum);die;
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository(Album::class)->getAlbums();


        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $albums = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1)
        );

        $rating = [];

        foreach ($albums as $album) {
            $totalReviews = 0;
            $totalRating = 0;
            $albumRating = 0;

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
     * Generates a unique name of uploaded images.
     *
     * @param FormInterface $file
     * @return string
     */
    private static function hashImageName(FormInterface $file)
    {
        return md5(uniqid()) . '.' . $file->getData()->guessExtension();
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws AlbumExistsException
     */
    public function addAlbumAction(Request $request)
    {
        $album = new Album();
        $form = $this->createForm(AddAlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['image']->getData();

            $originalFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFileName = $originalFileName . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            try {
                $em = $this->getDoctrine()->getManager();
                $album->setImage($newFileName);
                $em->persist($album);
                $em->flush();
            } catch (UniqueConstraintViolationException $e) {
                $message = sprintf('DBALException [%i]: %s'.$e->getMessage(), $e->getCode(), "");
            } catch (TableNotFoundException $e) {
                $message = sprintf('ORMException [%i]: %s', $e->getCode(), $e->getMessage());
            } catch (Exception $e) {
                $message = sprintf('Exception [%i]: %s', $e->getCode(), $e->getMessage());
            }

            if (isset($message)) {
                throw new AlbumExistsException($message);
            }

            // https://symfony.com/doc/current/security/access_denied_handler.html

            $destination = $this->getParameter('uploads_directory');
            $uploadedFile->move($destination, $newFileName);
        }
        return $this->render('AlbumBundle:Default:add_album.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
