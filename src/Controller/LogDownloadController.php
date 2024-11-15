<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use ZipArchive;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class LogDownloadController extends AbstractController
{
   #[Route(path: '/sumedia-winestro/log-token-download', name: 'sumedia-winestro.log-token-download', methods: ['GET'])]
    public function logTokenDownload(): Response
    {
        $token = $_GET['token'];
        if (preg_match('/[a-z0-9]{32}/i', $token)) {
            $files = glob('../files/sumedia-winestro/logdownload/' . $token . '/*');
            if (count($files)) {
                $file = $files[0];
                return new BinaryFileResponse($file);
            }
        }
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
