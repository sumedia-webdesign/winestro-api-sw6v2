<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Controller;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['administration']])]
class ApiTestController extends AbstractController
{
    public function __construct() {
        $this->setContainer(new Container());
    }

    #[Route(path: '/api/sumedia-winestro/check', name: 'sumedia-winestro.checkConnection', methods: ['POST'])]
    public function checkConnection(RequestDataBag $dataBag): JsonResponse
    {
        $message = false;
        try {
            $url = $dataBag->get('url') . '/wbo-API.php';

            $data = [
                'output' => 'json',
                'UID' => $dataBag->get('userId'),
                'apiUSER' => $dataBag->get( 'secretId'),
                'apiCODE' => $dataBag->get('secretCode'),
                'apiShopID' => $dataBag->get('shopId'),
                'apiACTION' => 'getEinstellungen'
            ];

            $client = new Client();
            $response = $client->request('POST', $url, ['form_params' => $data]);

            if ($response->getStatusCode() === 200) {
                $json = json_decode($response->getBody()->getContents(), true);
                return new JsonResponse(['success' => true, 'winestroShopName' => $json['name']]);
            }
        } catch(\Exception $e) {
            $message = $e->getMessage();
        }
        return new JsonResponse(['success' => false, 'message' => $message]);
    }
}
