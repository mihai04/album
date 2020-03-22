<?php

namespace AlbumBundle\Controller;

use AlbumBundle\Entity\Album;
use AlbumBundle\Form\AddAlbumType;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\Paginator;
use AlbumBundle\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AlbumBundle\Entity\Track;

/**
 * Class AlbumController
 * @package AlbumBundle\Controller
 */
class AlbumController extends Controller
{
    const INDICES = 'indices';
    const POPULATE_SEARCH_ENTITIES = 'populate:search:entities';

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

            if(!$uploadedFile) {
                $this->addFlash('warning', 'You forgot to add an album image.');
                return $this->redirect($this->generateUrl('add_album'));
            }

            $originalFileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFileName = $this->hashImageName($originalFileName, $uploadedFile);

            try {
                $em = $this->getDoctrine()->getManager();
                $album->setImage($newFileName);
                $tracks = $form['albumTracks']->getData();

                if (count($tracks) === 0) {
                    $this->addFlash('warning', 'You forgot to add tracks.');
                    return $this->redirect($this->generateUrl('add_album'));
                }

                /**
                 * @var Track $track
                 */
                foreach ($tracks as $track) {
                    $track->setAlbum($album);
                }

                $em->persist($album);
                $em->flush();

                $this->updateEntitiesCommand();

            } catch (UniqueConstraintViolationException $e) {
                $message = 'DBALException [%i]: %s'.$e->getMessage();
            } catch (TableNotFoundException $e) {
                $message = 'ORMException [%i]: %s'. $e->getMessage();
            } catch (Exception $e) {
                $message = 'Exception [%i]: %s'. $e->getMessage();
            }

            if (isset($message)) {
                $this->addFlash('error', 'Failed to create album! Try again.' . $message);
                return $this->redirect($this->generateUrl('add_album'));
            }
            else {
                $this->addFlash('success', 'Album '. $album->getTitle() .' was successfully created.');

                // save image
                $destination = $this->getParameter('uploads_directory');
                $uploadedFile->move($destination, $newFileName);

                return $this->redirect($this->generateUrl('album_homepage'));
            }
        }

        return $this->render('AlbumBundle:Default:add_album.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return RedirectResponse|Response
     */
    public function editAlbumAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $album = $em->getRepository(Album::class)
            ->find($id);

        if (!$album) {
            $this->addFlash('warning', 'Album not found!.');
            return $this->redirect($this->generateUrl('album_homepage'));
        }

        if (!$this->container->get('security.authorization_checker')
                ->isGranted('ROLE_ADMIN'))
        {
            $this->addFlash('error', 'You are not allowed to edit this album as you are not an admin!');
            return $this->redirect($this->generateUrl('album_homepage'));
        }
        else {
            $form = $this->createForm(AddAlbumType::class, $album, [
                'action' => $request->getUri()
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em->persist($album);
                $em->flush();

                $this->addFlash("success", "The album was updated.");
                return $this->redirect($this->generateUrl(
                    'add_album',
                    [
                        'id' => $album->getId()
                    ]
                ));
            }

            return $this->render('AlbumBundle:Default:add_album.html.twig', [
                'form' => $form->createView()
            ]);
        }
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
        try {

            $entityManager = $this->getDoctrine()->getManager();

            $album = $entityManager->getRepository(Album::class)
                ->find($id);

            if (!$album) {
                $this->addFlash('warning', 'Album does not exist!');
            }

            $reviews = $entityManager->getRepository(Review::class)
                ->getReviewsByAlbumID($id);

            /** @var Review $review */
            foreach ($reviews as $review) {
                $entityManager->remove($review);
                $entityManager->flush();
            }

            $entityManager->remove($album);
            $entityManager->flush();

            $this->addFlash('success', 'Album deleted');
        } catch (\Exception $e) {
            if ($this->getUser() !== null) {
                $this->addFlash('error', 'Failed to delete album!' . $e->getMessage());
            }
        }

        return $this->redirect($this->generateUrl('album_homepage'));
    }

    /**
     * Generated indices for searching the newly added album.
     */
    public function updateEntitiesCommand() {

        $kernel = $this->get('kernel');
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => '' . self::POPULATE_SEARCH_ENTITIES . '',
            'tableName' => self::INDICES,
        ));

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL);
        try {
            $output->writeln('<fg=green;options=bold>Generating indexes...');
            $application->run($input, $output);
        } catch (Exception $e) {
            $output->writeln('<fg=red;options=bold>Command for updating search indices failed!');
        }
    }
}
