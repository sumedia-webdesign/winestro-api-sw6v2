<?php

namespace Sumedia\WinestroAPI\Controller;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['administration']])]
class SumediaWboApiTestController extends AbstractController
{
    public function __construct() {
        // ... what the hack ...
        $this->setContainer(new Container());
    }

    #[Route(path: '/api/sumedia-wbo/checkConnection', name: 'sumedia-wbo.checkConnection', methods: ['POST'])]
    public function checkConnection(RequestDataBag $dataBag): JsonResponse
    {
        $message = false;
        try {
            $domain = 'SumediaWinestroAPI.config.';
            $url = rtrim($dataBag->get($domain . 'apiUrl'), '/') . '/wbo-API.php';

            $data = [
                'UID' => $dataBag->get($domain . 'userId'),
                'apiUSER' => $dataBag->get($domain . 'clientId'),
                'apiCODE' => $dataBag->get($domain . 'clientSecret'),
                'apiShopID' => $dataBag->get($domain . 'shopId'),
                'apiACTION' => 'getEinstellungen'
            ];

            $client = new Client();
            $response = $client->request('POST', $url, ['form_params' => $data]);

            if ($response->getStatusCode() === 200 && !str_contains($response->getBody(), '<fehler>')) {
                return new JsonResponse(['success' => true]);
            }
        } catch(\Exception $e) {
            $message = $e->getMessage();
        }
        return new JsonResponse(['success' => false, 'message' => $message]);
    }
}
