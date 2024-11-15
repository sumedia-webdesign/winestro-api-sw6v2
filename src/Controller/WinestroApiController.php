<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Controller;

use GuzzleHttp\Client;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Sumedia\WinestroApi\Winestro\DataMapper\CustomFieldsMapper;
use Sumedia\WinestroApi\Winestro\DataMapper\PaymentConfigMapper;
use Sumedia\WinestroApi\Winestro\DataMapper\PropertyMapper;
use Sumedia\WinestroApi\Winestro\DataMapper\ShippingConfigMapper;
use Sumedia\WinestroApi\Winestro\DataMapper\TaskMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['administration']])]
class WinestroApiController extends AbstractController
{
    #[Route(path: '/api/sumedia-winestro/check-connection', name: 'sumedia-winestro.check-connection', methods: ['POST'])]
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

    #[Route(path: 'api/sumedia-winestro/mapping', name: 'sumedia-winestro.payment-mapping', methods: ['POST'])]
    public function getMapping(RequestDataBag $dataBag): JsonResponse
    {
        $mapper = null;
        switch ($dataBag->get('mapper')) {
            case 'PaymentConfigMapper': $mapper = $this->getMapper(PaymentConfigMapper::class); break;
            case 'ShippingConfigMapper': $mapper = $this->getMapper(ShippingConfigMapper::class); break;
            case 'CustomFieldsMapper': $mapper = $this->getMapper(CustomFieldsMapper::class); break;
            case 'PropertyMapper': $mapper = $this->getMapper(PropertyMapper::class); break;
            case 'TaskMapper': $mapper = $this->getMapper(TaskMapper::class); break;
        }
        if ($mapper === null) {
            return new JsonResponse(['success' => false, 'message' => 'Could not fetch mapper']);
        }

        return new JsonResponse(['success' => true, 'mapping' => $mapper->toArray()]);
    }

    #[Route(path: 'api/sumedia-winestro/processlog', name: 'sumedia-winestro.task-log', methods: ['POST'])]
    public function processLog(RequestDataBag $dataBag): JsonResponse
    {
        $fileList = glob("../var/log/sumedia_winestro_process_*.log");
        sort($fileList);
        $fileList = array_slice(array_reverse($fileList), 0, 2);
        $lines = [];
        $count = 0;
        $limit = (int) $dataBag->get('limit') ?? 0;
        $max = $limit <= 1000 && $limit >= 150 ? $limit : 150;
        foreach ($fileList as $file) {
            $fileLines = array_map(function($item) { return trim($item); },
                array_slice(array_reverse(file($file)), 0, $max - $count > 0 ? $max - $count : 0)
            );
            $count += count($fileLines);
            $lines = array_merge($lines, $fileLines);
        }
        return new JsonResponse(['success' => true, 'lines' => $lines]);
    }

    #[Route(path: 'api/sumedia-winestro/logdownload/get', name: 'sumedia-winestro.log-download', methods: ['POST'])]
    public function logDownloadGet(): Response
    {
        $fileList = glob("../files/sumedia-winestro/logdownload/*/sumedia_winestro_*.zip");
        if (count($fileList)) {
            uasort($fileList, function($a, $b) { return basename($a) > basename($b); });

            return new JsonResponse(['success' => true, 'filelist' => array_reverse($fileList)]);
        }
        return new JsonResponse(['success' => false]);
    }

    #[Route(path: 'api/sumedia-winestro/logdownload/create', name: 'sumedia-winestro.log-download', methods: ['POST'])]
    public function logDownloadCreate(): Response
    {
        $fileList = glob("../var/log/sumedia_winestro_*.log");
        if (count($fileList)) {
            $token = md5(uniqid((string) time()));
            $zipname = '../files/sumedia-winestro/logdownload/' . $token . '/sumedia_winestro_log_download-' . date('Y-m-d_H:i:s') . '.zip';
            if (!is_dir(dirname($zipname))) {
                mkdir(dirname($zipname), 0777, true);
            }
            touch($zipname);
            clearstatcache();
            $zip = new \ZipArchive();
            $zip->open($zipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            foreach ($fileList as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();

            return new JsonResponse(['success' => true, 'token' => $token]);
        }
        return new JsonResponse(['success' => false]);
    }

    #[Route(path: 'api/sumedia-winestro/logdownload/download', name: 'sumedia-winestro.log-download', methods: ['GET'], defaults: ['_routeScope' => ['storefront']])]
    public function logDownloadDownload(RequestDataBag $dataBag): Response
    {
        $token = $dataBag->get('token');
        if (!preg_match('#[a-z9-0]{32}#', $token)) {
            throw new \RuntimeException('Received wrong token format');
        }
        $fileList = glob("../files/sumedia-winestro/$token/*");
        if (count($fileList)) {
            $file = $fileList[0];
            return new BinaryFileResponse($file);
        }
        return new JsonResponse(['success' => false]);
    }

    private function getMapper(string $class)
    {
        return $this->container->get($class);
    }
}
