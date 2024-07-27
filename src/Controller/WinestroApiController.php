<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Controller;

use Sumedia\WinestroApi\Winestro\DataMapper\PaymentConfigMapper;
use Sumedia\WinestroApi\Winestro\DataMapper\ShippingConfigMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['administration']])]
class WinestroApiController extends AbstractController
{
    public function __construct() {
        $this->setContainer(new Container());
    }

    #[Route(path: '/api/sumedia-winestro/mapping', name: 'sumedia-winestro.payment-mapping', methods: ['POST'])]
    public function checkConnection(): JsonResponse
    {
        $paymentMapper = new PaymentConfigMapper();
        $shippingMapper = new ShippingConfigMapper();

        $mapping = [
            'paymentMapping' => $paymentMapper->toArray(),
            'shippingMapping' => $shippingMapper->toArray()
        ];

        return new JsonResponse(['success' => true, 'mapping' => $mapping]);
    }

    #[Route(path: '/api/sumedia-winestro/tasklog', name: 'sumedia-winestro.task-log', methods: ['POST'])]
    public function taskLog(): JsonResponse
    {
        $fileList = glob("../var/log/sumedia_winestro_tasks_*.log");
        sort($fileList);
        $fileList = array_slice(array_reverse($fileList), 0, 2);
        $lines = [];
        $count = 0;
        $max = 150;
        foreach ($fileList as $file) {
            $fileLines = array_map(function($item) { return trim($item); },
                array_slice(array_reverse(file($file)), 0, $max-$count > 0 ? $max-$count : 0)
            );
            $count += count($fileLines);
            $lines = array_merge($lines, $fileLines);
        }
        return new JsonResponse(['success' => true, 'lines' => $lines]);
    }

    #[Route(path: '/api/sumedia-winestro/cronlog', name: 'sumedia-winestro.cron-log', methods: ['POST'])]
    public function cronLog(): JsonResponse
    {
        $fileList = glob("../var/log/sumedia_winestro_crons_*.log");
        sort($fileList);
        $fileList = array_slice(array_reverse($fileList), 0, 2);
        $lines = [];
        $count = 0;
        $max = 50;
        foreach ($fileList as $file) {
            $fileLines = array_map(function($item) { return trim($item); },
                array_slice(array_reverse(file($file)), 0, $max-$count > 0 ? $max-$count : 0)
            );
            $count += count($fileLines);
            $lines = array_merge($lines, $fileLines);

        }
        return new JsonResponse(['success' => true, 'lines' => $lines]);
    }

    #[Route(path: '/api/sumedia-winestro/logdownload', name: 'sumedia-winestro.log-download', methods: ['POST'])]
    public function logDownload(): Response
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
}
