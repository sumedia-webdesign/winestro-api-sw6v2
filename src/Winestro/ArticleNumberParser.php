<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro;

class ArticleNumberParser
{
    public function WinestroArticleNumberToShopwareProductNumber(
        string $winestroArticleNumber,
        string $articleNumberFormat,
        string $yearSeparator,
        string $bottlingSeparator
    ) {
        if ($articleNumberFormat === '[articlenumber+year+bottling]') {
            return $winestroArticleNumber;
        } elseif ($articleNumberFormat === '[articlenumber+year]') {
            if ($this->hasNoBottling($winestroArticleNumber)) {
                return $winestroArticleNumber;
            }

            $productNumber = '';
            $nextIsBottling = false;
            for ($i = 0; $i < strlen($winestroArticleNumber); $i++) {
                $char = $winestroArticleNumber[$i];
                if ($yearSeparator === $bottlingSeparator) {
                    if ($char !== $bottlingSeparator) {
                        $productNumber .= $char;
                    } else {
                        if ($nextIsBottling) {
                            break;
                        }
                        $productNumber .= $char;
                        $nextIsBottling = true;
                    }
                } else {
                    if ($char !== $bottlingSeparator) {
                        $productNumber .= $char;
                    } else {
                        break;
                    }
                }
            }
            return $productNumber;
        } elseif ($articleNumberFormat === '[articlenumber]') {
            $productNumber = '';
            for ($i = 0; $i < strlen($winestroArticleNumber); $i++) {
                $char = $winestroArticleNumber[$i];
                if ($char !== $yearSeparator) {
                    $productNumber .= $char;
                } else {
                    break;
                }
            }
            return $productNumber;
        }
        throw new \RuntimeException('could not parse article number format');
    }

    private function hasNoBottling(string $winestroArticleNumber): bool
    {
        return (bool) preg_match('#^\d+\W+?\d+?$#', $winestroArticleNumber);
    }
}
