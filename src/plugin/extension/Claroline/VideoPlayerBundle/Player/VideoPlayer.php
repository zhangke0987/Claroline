<?php
namespace Claroline\VideoPlayerBundle\Player;

use Claroline\CoreBundle\Library\Player\PlayerInterface;
use Symfony\Component\HttpFoundation\Response;

class VideoPlayer implements PlayerInterface
{
    /*
    public function getExtension()
    {
        return "mp4";
    }*/
    
    public function indexAction($workspaceId)
    {
        return new Response("redéfini openAction pour mp4, l'id de mon workspace est {$workspaceId}");
    }
    
    public function getMime()
    {
        return "video/mp4";
    }
    
    public function getPlayerName()
    {
        return "mp4Player";
    }
}