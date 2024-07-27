<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Response;

use Sumedia\WinestroApi\Winestro\DataMapper\WinestroArticleDataMapper;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;

class GetArticlesFromWinestroResponse extends AbstractResponse
{
    public function __construct(private LogManagerInterface $logManager){}

    public function populate(array $data): void
    {
        if (isset($data['items'])) {
            $data = $data['items'];
        }
        $this->data = $this->mapData($data);
    }

    private function mapData(array $data): array
    {
        $dataMapper = new WinestroArticleDataMapper();
        $newData = [];
        foreach ($data as $id => $item) {
            foreach ($item as $itemKey => $itemData) {
                $strangeArticleNumber = urldecode($itemData['artikel_nr']);
                foreach ($itemData as $key => $value) {
                    $value = $this->filterStrangeValue($key, $value);
                    $value = $this->filterValues($key, $value);
                    $value = $this->filterCommaSeparatedValue($key, $value);
                    $newKey = $dataMapper->mapKey((string) $key);
                    $newData[$strangeArticleNumber][$newKey] = $value;
                }
            }
        }
        return $newData;
    }

    private function filterCommaSeparatedValue(string $key, mixed $value): mixed
    {
        switch ($key) {
            case 'artikel_zutaten':
            case 'artikel_allergene':
                if (is_string($value)) {
                    $return = [];
                    foreach (explode(',', $value) as $part) {
                        $return[] = trim($part);
                    }
                    return $return;
                }
                return $value;
        }
        return $value;
    }

    private function filterValues(string $key, mixed $value): mixed
    {
        switch ($key) {
            case 'artikel_versandzahl': $value = (int) $value; break;
            case 'artikel_warengruppen':
                $value = is_array($value) && isset($value['warengruppe']) ? $value['warengruppe'] : null;
                break;
        }
        return $value;
    }

    private function filterStrangeValue(string $key, mixed $value): mixed
    {
        $value = $this->ohLookNeedUnserialize($value);
        $value = $this->removeUsefulArrayResponse($value);

        switch ($key) {
            case 'artikel_nr': $value = urldecode($value); break;
            case 'artikel_nuancen': $value = null !== $value ? array_keys($value) : null; break;
            case 'artikel_kategorie': $value = 'k.A.' === $value ? null : $value; break;
            case 'artikel_erzeuger_name': $value = ',  , DE-, ' === $value ? null : $value; break;
            case 'artikel_warengruppen':
                if (is_array($value) && isset($value['warengruppe'])) {
                    foreach ($value['warengruppe'] as $i => $warengruppe) {
                        $value['warengruppe'][$i] = html_entity_decode($warengruppe);
                    }
                }
                break;
        }
        return $value;
    }

    private function ohLookNeedUnserialize(mixed $value): mixed
    {
        try {
            $this->logManager->setVoidErrorHandler();
            if (is_string($value)) {
                $check = @unserialize($value);
                if (false !== $check) {
                    $this->logManager->resetVoidErrorHandler();
                    return $check;
                }
            }
        } catch (\Exception $e) {
            return $value;
        } finally {
            $this->logManager->resetVoidErrorHandler();
        }
        return $value;
    }

    private function removeUsefulArrayResponse(mixed $value): mixed
    {
        return is_array($value) && !count($value) ? null : $value;
    }
}
