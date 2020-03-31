<?php


namespace AlbumBundle\Helper;


use AlbumBundle\Entity\Album;
use AlbumBundle\Entity\AlbumResult;
use AlbumBundle\Entity\Track;

class AlbumHelper
{
    /** @var string */
    const TRACK_TIME_FORMAT = '%02d:%02d';

    private function __construct() {
        // helper class
    }

    /**
     * @param $result
     * @return AlbumResult
     * @throws \Exception
     */
    public static function processAlbum($result) {

        /** @var Album $album */
        $album = new Album();
        $tracks = [];

        if (array_key_exists('album', $result)) {

            if (array_key_exists('name', $result['album'])) {
                $album->setTitle($result['album']['name']);
            }

            if (array_key_exists('artist',$result['album'])) {
                $album->setArtist($result['album']['artist']);
            }

            if (array_key_exists('mbid',$result['album'])) {
                $album->setIsrc($result['album']['mbid']);
            }

            if (array_key_exists('url',$result['album'])) {
                $album->setUrl($result['album']['url']);
            }

            if (array_key_exists('image', $result['album'])) {
                if (!empty($result['album']['image'][2])) {
                    if ($result['album']['image'][2]['#text']) {
                        $album->setImage($result['album']['image'][2]['#text']);
                    }
                }
            }

            if (array_key_exists('wiki', $result['album'])) {
                if (array_key_exists('published', $result['album']['wiki'])) {
                    $album->setPublished($result['album']['wiki']['published']);
                }

                if (array_key_exists('summary', $result['album']['wiki'])) {
                    $album->setSummary($result['album']['wiki']['summary']);
                }
            }

            if (array_key_exists('playcount',$result['album'])) {
                $album->setPlaycount($result['album']['playcount']);
            }

            if (array_key_exists('listeners',$result['album'])) {
                $album->setListeners($result['album']['listeners']);
            }

            if (array_key_exists('tracks', $result['album'])) {

                if (array_key_exists('track', $result['album']['tracks'])) {

                    foreach ($result['album']['tracks']['track'] as $trackEntry) {

                        $track = new Track();

                        if (array_key_exists('name', $trackEntry)) {
                            $track->setTrackName($trackEntry['name']);
                        }

                        if (array_key_exists('duration', $trackEntry)) {
                            $seconds = $trackEntry['duration'];

                            $minutes = round($seconds / 60);
                            $remainMinutes = ($minutes % 60);

                            $track->setDuration((sprintf(self::TRACK_TIME_FORMAT, $minutes, $remainMinutes)));
                        }

                        if (!empty($track)) {
                            $track->setAlbum($album);
                            $tracks[] = $track;
                        }
                    }
                }
            }

            /** @var  $replacedTagData */
            $replacedTagData = AlbumHelper::getAlbumTags($result);
            $album->setTags($replacedTagData);

            $album->setTimestamp(new \DateTime());
        }

        return new AlbumResult($album, $tracks);
    }

    /**
     * @param $albumResult
     * @param Album $album
     * @return void
     */
    public static function populateAlbum($albumResult, $album) {
        try {
            if ($albumResult !== null && array_key_exists('album', $albumResult)) {

                if (array_key_exists('listeners', $albumResult['album'])) {
                    $album->setListeners($albumResult['album']['listeners']);
                }

                if (array_key_exists('playcount', $albumResult['album'])) {
                    $album->setPlaycount($albumResult['album']['playcount']);
                }

                if (array_key_exists('wiki', $albumResult['album'])) {

                    if (array_key_exists('published', $albumResult['album']['wiki'])) {
                        $album->setPublished($albumResult['album']['wiki']['published']);
                    }
                }

                /** @var  $replacedTagData */
                $replacedTagData = AlbumHelper::getAlbumTags($albumResult);
                $album->setTags($replacedTagData);
            }
        } catch (\Exception $e) {
            // fail silently
        }
    }

    public static function getAlbumTags($result)
    {
        $replacedTagData = "";

        try {

            if (array_key_exists('album', $result)) {

                if (array_key_exists('tags', $result['album'])) {
                    if (array_key_exists('tag', $result['album']['tags'])) {
                        $tagData = "";
                        foreach ($result['album']['tags']['tag'] as $tag) {
                            if (array_key_exists('name', $tag)) {
                                ;
                                $tagData .= $tag['name'] . ', ';
                            }
                        }
                        $replacedTagData = rtrim($tagData, ", ");
                    }
                }
            }
            return $replacedTagData;

        } catch (\Exception $e) {
            return $replacedTagData;
        }
    }

    /**
     * @param array $similar
     * @return array
     */
    public static function processSimilarArtistsResults($similar) {

        $similarArtists = [];
        try {

            if (array_key_exists('similarartists', $similar)) {

                if (array_key_exists('artist', $similar['similarartists'])) {

                    $similarArtist = [];
                    foreach ($similar['similarartists']['artist'] as $artistEntry) {

                        if (array_key_exists('name', $artistEntry)) {
                            $similarArtist['name'] = $artistEntry['name'];
                        }

                        if (array_key_exists('url', $artistEntry)) {
                            $similarArtist['url'] = $artistEntry['url'];
                        }

                        if (!empty($similarArtist)) {
                            $similarArtists[] = $similarArtist;
                        }
                    }
                }
            }
            return $similarArtists;

        } catch (\Exception $e) {
            return null;
        }
    }
}