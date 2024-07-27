<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task\OrderExport;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Sumedia\WinestroApi\ConfigInterface;
use Sumedia\WinestroApi\RepositoryManagerInterface;
use Sumedia\WinestroApi\Winestro\DataMapper\PaymentConfigMapper;
use Sumedia\WinestroApi\Winestro\Task\TaskInterface;

class OrderExportBuilder
{
    private string $winestroConnectionId;
    private array $winestroConnectionIds;
    private array $winestroConnections;
    private array $salesChannels;
    private array $countryIso;
    private array $salutationNames;

    public function __construct(
        private ConfigInterface $config,
        private RepositoryManagerInterface $repositoryManager,
    ){}

    public function build(TaskInterface $task, OrderCollection $orders): array
    {
        $this->winestroConnectionId = $task['winestroConnectionId'];
        $this->winestroConnectionIds = $task['productsFromWinestroConnectionIds'];
        $this->winestroConnections = $this->config->get('winestroConnections');
        $this->salesChannels = $this->config->get('salesChannels');

        $orderItems = [];
        foreach ($orders as $order) {
            $orderData = [
                ... $this->getBillingAddressData($order),
                ... $this->getShippingAddressData($order),
                ... $this->getPaymentDetails($order),
                ... $this->getShippingDetails($order),
                ... $this->getWinestroOrderLineItems($order),
                'referenz' => $order->getCustomerComment() ? substr($order->getCustomerComment(), 0, 255) : ''
            ];
            if (!$task['enabled']['sendWinestroEmail']) {
                $orderData['keine_mail'] = 1;
            }
            $orderItems[] = [
                'order' => $order,
                'orderData' => $orderData
            ];
        }
        return $orderItems;
    }

    private function getWinestroOrderLineItems(OrderEntity $order): array
    {
        $winestroOrderLineItems = $this->getWinestroOrderLineItemsFromOrder($order);
        /** @var $winestroOrderLineItem \Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity*/
        $i = 1;
        $discountItems = [];
        $items = [];
        foreach ($winestroOrderLineItems as $winestroOrderLineItem) {
            $product = $winestroOrderLineItem->getProduct();
            if ($this->isArticleDiscount($winestroOrderLineItem)) {
                $discountItems[] = [
                    'artikel_sonderpreis' => $winestroOrderLineItem->getTotalPrice(),
                    'wein_anzahl' => $winestroOrderLineItem->getQuantity(),
                    'wein_id' => 'rabatt'
                ];
            } else {
                if ($winestroOrderLineItem->getPriceDefinition()->getPrice() < $winestroOrderLineItem->getPriceDefinition()->getListPrice()) {
                    $items[] = [
                        'artikel_sonderpreis' => $winestroOrderLineItem->getPriceDefinition()->getPrice(),
                        'wein_anzahl' => $winestroOrderLineItem->getQuantity(),
                        'wein_id' => $product->getCustomFieldsValue('sumedia_winestro_product_details_article_number')
                    ];
                } else {
                    $items[] = [
                        'wein_anzahl' => $winestroOrderLineItem->getQuantity(),
                        'wein_id' => $product->getCustomFieldsValue('sumedia_winestro_product_details_article_number')
                    ];
                }

            }
        }

        $i = 1;
        foreach (array_merge($items, $discountItems) as $item) {
            if (isset($item['artikel_sonderpreis'])) {
                $orderItems[] = [
                    "artikel_sonderpreis$i" => $item['artikel_sonderpreis'],
                    "wein_anzahl$i" => $item['wein_anzahl'],
                    "wein_id$i" => $item['wein_id']
                ];
            } else {
                $orderItems[] = [
                    "wein_anzahl$i" => $item['wein_anzahl'],
                    "wein_id$i" => $item['wein_id']
                ];
            }
            $i++;
        }

        $orderData = ['positionen' => count($orderItems)];
        foreach ($orderItems as $orderItem) {
            foreach ($orderItem as $key => $value) {
                $orderData[$key] = $value;
            }
        }
        return $orderData;
    }

    private function isArticleDiscount(OrderLineItemEntity $orderLineItem): bool
    {
        return $orderLineItem->getType() == LineItem::PROMOTION_LINE_ITEM_TYPE;
    }

    private function getWinestroOrderLineItemsFromOrder(OrderEntity $order): array
    {
        $items = [];
        foreach ($order->getLineItems() as $lineItem) {
            $product = $lineItem->getProduct();
            if (
                $product->getCustomFieldsValue('sumedia_winestro_product_details_article_number') !== null &&
                in_array(
                    $product->getCustomFieldsValue('sumedia_winestro_product_details_winestro_connection_id'),
                    $this->winestroConnectionIds
                )
            ) {
                $items[] = $lineItem;
            }
        }
        return $items;
    }

    private function getAddress(OrderEntity $order, string $addressId): ?OrderAddressEntity
    {
        foreach ($order->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return null;
    }

    private function getBillingAddressData(OrderEntity $order): array
    {
        $address = $this->getAddress($order, $order->getBillingAddressId());
        return [
            'titel' => $address->getTitle(),
            'anrede' => $this->getSalutationName($address->getSalutationId()),
            'firma' => $address->getCompany(),
            'name' => $address->getFirstName(),
            'nname' => $address->getLastName(),
            'strasse' => $this->splitStreet($address->getStreet(), 'street'),
            'hnummer' => $this->splitStreet($address->getStreet(), 'number'),
            'plz' => $address->getZipcode(),
            'ort' => $address->getCity(),
            'land' => $this->getCountryIso($address->getCountryId()),
            'telefon' => $address->getPhoneNumber(),
            'email' => $order->getOrderCustomer()->getEmail()
        ];
    }

    private function getSalutationName(string $salutationId): string
    {
        if (!isset($this->salutationNames[$salutationId])) {
            $this->salutationNames[$salutationId] = $this->repositoryManager->search('salutation',
                (new Criteria([$salutationId]))
            )->first()->getTranslated()['displayName'];
        }
        return $this->salutationNames[$salutationId];
    }

    private function getShippingAddressData(OrderEntity $order): array
    {
        /** @var \Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity $delivery */
        $address = $this->getAddress($order, $order->getDeliveries()->first()->getShippingOrderAddressId());
        return [
            'l_firma' => $address->getCompany(),
            'l_name' => $address->getFirstName(),
            'l_nname' => $address->getLastName(),
            'l_strasse' => $this->splitStreet($address->getStreet(), 'street'),
            'l_hnummer' => $this->splitStreet($address->getStreet(), 'number'),
            'l_plz' => $address->getZipcode(),
            'l_ort' => $address->getCity(),
            'l_land' => $this->getCountryIso($address->getCountryId())
        ];
    }

    private function splitStreet(string $string, string $part): string
    {
        if (preg_match('#^([^\d]*?)(\d+.*?)$#', $string, $matches)) {
            if ($part === 'street' && isset($matches[1])) {
                return $matches[1];
            } elseif ($part === 'number' && isset($matches[2])) {
                return $matches[2];
            }
            return $matches[1] . ' ' . ($matches[2] ?? '');
        }
        return $string;
    }

    private function getCountryIso(string $countryId) : string
    {
        if (!isset($this->countryIso[$countryId])) {
            $countryCriteria = new Criteria();
            $countryCriteria->addFilter(new EqualsFilter('id', $countryId));
            $country = $this->repositoryManager->search('country', $countryCriteria)->first();
            $this->countryIso[$countryId] = $country->getIso();
        }
        return $this->countryIso[$countryId];
    }

    private function getPaymentDetails(OrderEntity $order): array
    {
        $paymentDetails = [];
        foreach ($order->getTransactions() as $transaction) {
            $paymentMethodId = $transaction->getPaymentMethodId();
            $winestroPaymentId = $this->getWinestroPaymentIdFromPaymentId($order->getSalesChannelId(), $paymentMethodId);
            if (!isset($paymentDetails['zahlungsart']) && $winestroPaymentId) {
                $paymentDetails['zahlungsart'] = $winestroPaymentId;
            }
            switch ($winestroPaymentId) {
                case PaymentConfigMapper::PAYMENT_DEBIT:
                    // return ... need payment details
                    /*
                     * ktoInh 	Name des Kontoinhabers
                    iban
                    bic
                    gebuehr
                     */
                    $paymentDetails['zahlungsart'] = $this->getWinestroPaymentIdFromPaymentId($order->getSalesChannelId(), $paymentMethodId);
                    break;
                case PaymentConfigMapper::PAYMENT_PAYPAL:
                case PaymentConfigMapper::PAYMENT_PAYPAL_INVOICE:
                    // woo_transaktions_code
                    $paymentDetails['zahlungsart'] = $this->getWinestroPaymentIdFromPaymentId($order->getSalesChannelId(), $paymentMethodId);
                    break;
            }
        }
        if (!isset($paymentDetails['zahlungsart'])) {
            throw new \RuntimeException('could not get winestro payment id');
        }
        $paymentDetails['zahlungsart'] = $winestroPaymentId;
        return $paymentDetails;
    }

    private function getShippingDetails(OrderEntity $order): array
    {
        $shippingDetails = [];
        foreach ($order->getDeliveries() as $delivery) {
            if (!isset($shippingDetails['id_lieferart'])) {
                $shippingDetails['id_lieferart'] = $this->getWinestroShippingIdFromShippingId(
                    $order->getSalesChannelId(),
                    $delivery->getShippingMethodId()
                );
                break;
            }
        }

        if (!isset($shippingDetails['id_lieferart'])) {
            throw new \RuntimeException('could not get winestro shipping id');
        }

        $shippingDetails['versandkosten'] = $order->getShippingCosts()->getTotalPrice();
        return $shippingDetails;
    }

    private function getWinestroPaymentIdFromPaymentId(string $salesChannelId, string $paymentId): ?string
    {
        return $this->salesChannels[$salesChannelId]['winestroConnections']
            [$this->winestroConnectionId]['paymentMapping'][$paymentId] ?? null;
    }

    private function getWinestroShippingIdFromShippingId(string $salesChannelId, string $shippingId): ?string
    {
        return $this->salesChannels[$salesChannelId]['winestroConnections']
            [$this->winestroConnectionId]['shippingMapping'][$shippingId] ?? null;
    }
}