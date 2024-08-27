<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class VideoService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $video, string $folder =''){
        // on donne un nouveau nom a la video
        $fichier = md5(uniqid(rand(),true)). '.mp4';

        // on recupere les infos de la video 

        $video_infos = filesize($video);

        if($video_infos == false){
            throw new Exception('Format de video incorrect');
        }

        $path = $this->params->get('videos_directory') . $folder;

        // on cree le dossier de destination s'il n'existe pas

        if(!file_exists($path . '/videos/')){
            mkdir($path.'/videos/',0755,true);
        }

        $video->move($path .'/',$fichier);

        return $fichier;

    }

    public function delete(string $fichier, ?string $folder = '')
    {
        if($fichier !== 'default.mp4'){
            $success = false;
            $path = $this->params->get('videos_directory') . $folder;

            $original = $path . '/' . $fichier;

            if(file_exists($original)){
                unlink($original);
                $success = true;
            }
            return $success;
        }
        return false;
    }
}