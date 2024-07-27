<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Task\ProductImageUpdate;

use DateTime;
use Shopware\Core\Content\Media\File\FileFetcher;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\WinestroApi\RepositoryManagerInterface;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\Task\TaskInterface;
use Symfony\Component\HttpFoundation\Request;

class ImageUpdater
{
    private ?string $tempDirPath = null;

    public function __construct(
        private RepositoryManagerInterface $repositoryManager,
        private LogManagerInterface $logManager,
        private MediaService $mediaService,
        private FileFetcher $fileFetcher,
        private Context $context
    ){}

    public function updateImages(TaskInterface $task, ProductCollection $products, array $articles)
    {
        $this->getTempDirPath();
        $medias = $this->getMedias($task, $products, $articles);
        $this->updateProductsWithMedias($products, $medias);
        $this->removeTempDir();
    }

    private function getMedias(TaskInterface $task, ProductCollection $products, array $articles): array
    {
        $images = $this->listImages($products, $articles);
        $medias = [];
        foreach ($images as $articleNumber => $_images) {
            foreach ($_images as $index => $imageUrl) {
                $medias[$articleNumber][$index] =
                    $this->getMedia($imageUrl, $task['mediaFolder'], $task['maxWidth'], $task['maxHeight']);
            }
        }
        return $medias;
    }

    private function listImages(ProductCollection $products, array $articles): array
    {
        $images = [];
        foreach ($products as $product) {
            if (!($articleNumber = $product->getCustomFieldsValue('sumedia_winestro_product_details_article_number'))){
                $this->logManager->debug('could not fetch article number on product ' . $product->getId());
                continue;
            }
            if (!($article = $this->fetchArticle($articles, $articleNumber))) {
                $this->logManager->debug('could not fetch article on product ' . $product->getId());
                continue;
            }

            foreach (range(1,4) as $index) {
                $imageSrc = $article['imageBig' . $index] ?: $article['image' . $index];
                if (null !== $imageSrc) {
                    $images[$articleNumber][$index] = $imageSrc;
                }
            }
        }
        return $images;
    }

    private function fetchArticle(array $articles, string $articleNumber): ?array
    {
        foreach ($articles as $article) {
            if ($article['articleNumber'] === $articleNumber) {
                return $article;
            }
        }
        return null;
    }

    private function getMedia(string $imageUrl, string $mediaFolderId, int $maxWidth, int $maxHeight): MediaEntity
    {
        if (!($media = $this->getMediaEntityByImageUrl($imageUrl, $mediaFolderId))) {
            $media = $this->createMediaByImageUrl($imageUrl, $mediaFolderId, $maxWidth, $maxHeight);
        } else {
            $mediaDate = $media->getUpdatedAt();
            $imageDate = $this->getFileModifiedDateFromImageUrl($imageUrl);
            if ($mediaDate < $imageDate) {
                $this->updateMediaByImageUrl($media, $imageUrl, $maxWidth, $maxHeight);
            }
        }
        return $media;
    }

    private function getMediaEntityByImageUrl(string $imageUrl, string $mediaFolderId): ?MediaEntity
    {
        $fileName = $this->getImageFileNameFromImageUrl($imageUrl);
        $mediaFileName = $this->getMediaFileName($fileName);
        return $this->getMediaEntityByFileName($mediaFileName, $mediaFolderId);
    }

    private function getMediaEntityByFileName(string $mediaFileName, string $mediaFolderId) : ?MediaEntity
    {
        $searchCriteria = new Criteria();
        $searchCriteria->addAssociation('mediaFolder');
        $searchCriteria->addFilter(new EqualsFilter('fileName', $mediaFileName));
        $searchCriteria->addFilter(new EqualsFilter('mediaFolderId', $mediaFolderId));
        $searchMedia = $this->repositoryManager->search('media', $searchCriteria, $this->context);
        if (!$searchMedia->count()) {
            return null;
        }

        return $searchMedia->first();
    }

    private function createMediaByImageUrl(string $imageUrl, string $mediaFolderId, int $maxWidth, int $maxHeight): MediaEntity
    {
        $tmpFilePath = $this->createTempFileFromUrl($imageUrl);
        $this->resizeImage($tmpFilePath, $maxWidth, $maxHeight);
        return $this->createMediaImage($tmpFilePath, $mediaFolderId);
    }

    private function updateMediaByImageUrl(MediaEntity $media, string $imageUrl, int $maxWidth, int $maxHeight): void
    {
        $tmpFilePath = $this->createTempFileFromUrl($imageUrl);
        $this->resizeImage($tmpFilePath, $maxWidth, $maxHeight);
        $this->updateMediaImage($media, $tmpFilePath);
    }

    protected function updateMediaImage(MediaEntity $media, string $tempFilePath): void {
        $mediaFileName = $this->getMediaFileName(basename($tempFilePath));
        $mediaFile = new MediaFile(
            $tempFilePath,
            $this->getMimeType(basename($tempFilePath)),
            $this->getExtension(basename($tempFilePath)),
            filesize($tempFilePath),
            md5_file($tempFilePath)
        );

        $this->mediaService->saveMediaFile(
            $mediaFile,
            $mediaFileName,
            $this->context,
            $media->getMediaFolderId(),
            $media->getId()
        );
    }

    private function createMediaImage(string $tempFilePath, string $mediaFolderId): ?MediaEntity
    {
        $mediaId = Uuid::randomHex();
        $this->repositoryManager->create('media', [[
            'id' => $mediaId,
            'private' => false,
            'mediaFolderId' => $mediaFolderId
        ]], $this->context);
        $media = $this->repositoryManager->search('media', new Criteria([$mediaId]), $this->context)->first();
        $this->updateMediaImage($media, $tempFilePath);
        return $media;
    }

    private function getTempDirPath(): string
    {
        if ($this->tempDirPath == null) {
            $this->tempDirPath = sys_get_temp_dir() . '/sumedia_winestro_temp_dir_' . md5(uniqid((string) time()));
            if (!is_dir($this->tempDirPath)) {
                mkdir($this->tempDirPath, 0750, true);
            }
        }
        return $this->tempDirPath;
    }

    private function getFileModifiedDateFromImageUrl(string $imageUrl): ?DateTime
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'follow_location' => true,
                'ignore_errors' => true
            ]
        ]);
        $headers = get_headers($imageUrl, PHP_MAJOR_VERSION == 8 ? true : 1, $context);

        if (isset($headers['Last-Modified'])) {
            $fmt = 'D, d M Y H:i:s O+';
            return DateTime::createFromFormat($fmt, $headers['Last-Modified']);
        }
        return null;
    }

    private function getImageFileNameFromImageUrl(string $imageUrl): string
    {
        $prefix = 'winestro_';
        if (basename(basename($imageUrl)) === 'big') {
            $prefix .= 'big_';
        }
        return  $prefix . basename($imageUrl);
    }

    protected function getMediaFileName(string $fileName): string
    {
        return substr($fileName, 0, strrpos($fileName, '.'));
    }

    protected function getExtension(string $fileName): string
    {
        return substr($fileName, strrpos($fileName, '.')+1);
    }

    private function createTempFileFromUrl(string $imageUrl): ?string
    {
        $ext        = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $request    = new Request(['extension' => $ext], ['url' => $imageUrl]);
        $tempFile   = $this->tempDirPath . DIRECTORY_SEPARATOR . $this->getImageFileNameFromImageUrl($imageUrl);

        try {
            $this->fileFetcher->fetchFileFromURL($request, $tempFile);
            return $tempFile;
        } catch(\Exception $e) {
            return null;
        }
    }

    private function updateProductsWithMedias(ProductCollection $products, array $_medias): void
    {
        foreach ($products as $product) {
            if (!($articleNumber = $product->getCustomFieldsValue('sumedia_winestro_product_details_article_number'))){
                continue;
            }
            if (null !== $_medias && !($medias = $this->getMediasByArticleNumber($_medias, $articleNumber))) {
                continue;
            }

            $productMediaData = [];
            foreach ($medias as $media) {
                $productMediaData[] = [
                    'id' => Uuid::randomHex(),
                    'productId' => $product->getId(),
                    'mediaId' => $media->getId()
                ];
            }

            $productMediaIds = [];
            $removeMedia = [];
            foreach ($product->getMedia()->getElements() as $productMedia) {
                $productMediaIds[] = $productMedia->getId();
                $media = $productMedia->getMedia();
                if (str_contains($media->getFileName(), 'winestro_')) {
                    $found = false;
                    foreach ($medias as $_media) {
                        if ($_media->getId() === $media->getId()) {
                            $found = true;
                        }
                    }
                    if (!$found) {
                        $removeMedia[] = $media;
                    }
                    continue;
                }
                $productMediaData[] = [
                    'id' => $productMedia->getId(),
                    'productId' => $product->getId(),
                    'mediaId' => $media->getId()
                ];
            }

            $remove = array_map(function ($id) { return ['id' => $id]; }, $productMediaIds);
            $this->repositoryManager->delete('product_media', $remove, $this->context);
            $this->repositoryManager->create('product_media', $productMediaData, $this->context);

            $removeMediaIds = [];
            foreach ($removeMedia as $media) {
                $path = $media->getPath();
                // ... unlink
                $removeMediaIds[] = $media->getId();
            }

            $remove = array_map(function ($id) { return ['id' => $id]; }, $removeMediaIds);
            $this->repositoryManager->delete('media', $remove, $this->context);

            $coverId = null;
            foreach ($productMediaData as $mediaData) {
                if ($mediaData['id'] == $product->getCoverId()) {
                    $coverId = $mediaData['id'];
                }
            }
            if (null === $coverId && isset($productMediaData[0])) {
                $coverId = $productMediaData[0]['id'] ?? null;
            }

            if (null !== $coverId) {
                $this->repositoryManager->update('product', [[
                    'id' => $product->getId(),
                    'coverId' => $coverId
                ]]);
            }
        }
    }

    private function getMediasByArticleNumber(array $medias, string $articleNumber): ?array
    {
        foreach ($medias as $number => $mediaData) {
            if ($number == $articleNumber) {
                return $mediaData;
            }
        }
        return null;
    }

    private function getMetaData(string $filepath): array
    {
        $imagesizeData = getimagesize($filepath);
        return [
            'type'      => $imagesizeData[2],
            'width'     => $imagesizeData[0],
            'height'    => $imagesizeData[1]
        ];
    }

    private function getMediaType(string $filepath): ImageType
    {
        $metaData   = $this->getMetaData($filepath);
        $mediaType  = new ImageType();

        if (in_array($metaData['type'], [1, 3])) {
            $mediaType->addFlag('transparent');
        }

        return $mediaType;
    }


    private function resizeImage(string $imageFilePath, int $maxWidth, int $maxHeight): void
    {
        list($originalWidth, $originalHeight, $imageType) = getimagesize($imageFilePath);

        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return;
        }

        if ($originalWidth >= $originalHeight) {
            $scaleRatio = $originalWidth / $maxHeight;
        } else {
            $scaleRatio = $originalHeight / $maxWidth;
        }

        $newWidth = (int) round($originalWidth / $scaleRatio);
        $newHeight = (int) round($originalHeight / $scaleRatio);

        $imageCreateMethod = $this->getImageCreateMethodByImageType($imageType);
        if (null === $imageCreateMethod) {
            $this->errorLogger->warning('Could not GD create image');
            return;
        }

        $isTransparent = $this->getImageIsTransparentFromImageType($imageType);
        $originalImage = $imageCreateMethod($imageFilePath);
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        if ($isTransparent) {
            imagealphablending($newImage, true);
            $transparency = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparency);
            imagesavealpha($newImage, true);
        }
        imagecopyresized($newImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        $imageSaveMethod = $this->getImageSaveMethodByImageType($imageType);
        $imageSaveMethod($newImage, $imageFilePath);
    }

    private function getImageCreateMethodByImageType(int $imageType): ?string
    {
        switch ($imageType) {
            case 1: return 'imagecreatefromgif';
            case 2: return 'imagecreatefromjpeg';
            case 3: return 'imagecreatefrompng';
            case 6: return 'imagecreatefrombmp';
            case 15: return 'imagecreatefromwbmp';
        }
        return null;
    }

    private function getImageIsTransparentFromImageType(int $imageType): bool
    {
        return in_array($imageType, [1, 3]);
    }

    private function getImageSaveMethodByImageType(int $imageType): ?string
    {
        switch ($imageType) {
            case 1: return 'imagegif';
            case 2: return 'imagejpeg';
            case 3: return 'imagepng';
            case 6: return 'imagejpeg';
            case 15: return 'imagejpeg';
        }
        return null;
    }

    private function getMimeType(string $fileName): ?string
    {
        $ext = substr($fileName, strrpos($fileName, '.')+1);
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'gif':
                return 'image/gif';
            case'png':
                return 'image/png';
        }
        return null;
    }

    private function removeTempDir(): void
    {
        $dir = $this->tempDirPath;

        clearstatcache();
        if (!is_dir($dir)) {
            return;
        }
        $dh = opendir($dir);
        if (!$dh) {
            return;
        }
        while(false !== $file = readdir($dh)) {
            if ('.' == $file || '..' == $file) {
                continue;
            }
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_file($filePath)) {
                unlink($filePath);
            } elseif (is_dir($filePath)) {
                $this->removeTempDir($filePath);
            }
        }
        closedir($dh);
        rmdir($dir);
    }

}
