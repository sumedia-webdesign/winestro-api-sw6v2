<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Tests\Winestro\Task;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\WinestroApi\Tests\TestSuiteHelper;

class OrderExportTaskTest extends TestCase
{
    use IntegrationTestBehaviour;
    use KernelTestBehaviour;

    private $testSuiteHelper = null;
    private $customerId = null;
    private $billingAddressId = null;
    private $shippingAddressId = null;
    private $productId = null;
    private $defaultSalesChannelId = null;
    private $context = null;

    public function setUp(): void
    {
        $this->context = $this->getContainer()->get('Sumedia\WinestroApi\DefaultContext');
        $this->testSuiteHelper = new TestSuiteHelper($this->getContainer(), $this->context);
        $this->testSuiteHelper->setupPlugin();
        $this->customerId = $this->testSuiteHelper->createCustomer();
        $this->billingAddressId = $this->testSuiteHelper->createBillingAddress([], $this->customerId);
        $this->shippingAddressId = $this->testSuiteHelper->createShippingAddress([], $this->customerId);
        $this->productId = $this->testSuiteHelper->createProduct();

        $salesChannelRepository = $this->getContainer()->get('sales_channel.repository');
        $this->defaultSalesChannelId = $salesChannelRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', 'Default Sales Channel'))
            , $this->context
        )->first()->getId();
    }

    public function testGetOrdersCollection(): void
    {
        $pluginRepository = $this->getContainer()->get('plugin.repository');
        /** @var PluginEntity $plugin */
        $plugin = $pluginRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', 'SumediaWinestroApi'))
            , $this->context
        )->first();
        /** @var Connection $db */
        $db = $this->getContainer()->get('Doctrine\DBAL\Connection');

        $order1Id = $this->testSuiteHelper->createOrder([], $this->customerId, $this->billingAddressId, $this->productId);
        $order2Id = $this->testSuiteHelper->createOrder([], $this->customerId, $this->billingAddressId, $this->productId);
        $order3Id = $this->testSuiteHelper->createOrder([], $this->customerId, $this->billingAddressId, $this->productId);
        $order4Id = $this->testSuiteHelper->createOrder([], $this->customerId, $this->billingAddressId, $this->productId);

        $orderExportTask = $this->getContainer()->get('Sumedia\WinestroApi\Winestro\Task\OrderExportTask');
        $method = $this->getReflectedPublicMethod($orderExportTask, 'getOrdersCollection');

        $ordersCollection = $method->invoke($orderExportTask, [$this->defaultSalesChannelId]);
        $this->assertCount(4, $ordersCollection);

        /** @var \DateTime $beforePluginDate */
        $beforePluginDate = new \DateTime($plugin->getInstalledAt()->format('Y-m-d H:i:s'));
        $beforePluginDate->sub(\DateInterval::createFromDateString('1 day'));

        $db->update('`order`', [
            'created_at' => $beforePluginDate->format('Y-m-d H:i:s'),
        ], ['id' => Uuid::fromHexToBytes($order4Id)]);
        $ordersCollection = $method->invoke($orderExportTask, [$this->defaultSalesChannelId]);
        $this->assertCount(3, $ordersCollection);

        $modifiedPluginInstallationDate = new \DateTime($plugin->getInstalledAt()->format('Y-m-d H:i:s'));
        $modifiedPluginInstallationDate->sub(\DateInterval::createFromDateString('10 days'));
        $pluginRepository->upsert([[
            'id' => $plugin->getId(),
            'installedAt' => $modifiedPluginInstallationDate->format('Y-m-d H:i:s'),
        ]], $this->context);
        $beforeMaxDays = (new \DateTime())->sub(\DateInterval::createFromDateString('6 days'));
        $db->update('`order`', [
            'created_at' => $beforeMaxDays->format('Y-m-d H:i:s'),
        ], ['id' => Uuid::fromHexToBytes($order3Id)]);
        $db->update('`order`', [
            'created_at' => $beforeMaxDays->format('Y-m-d H:i:s'),
        ], ['id' => Uuid::fromHexToBytes($order4Id)]);
        $ordersCollection = $method->invoke($orderExportTask, [$this->defaultSalesChannelId]);
        $this->assertCount(2, $ordersCollection);

        $this->testSuiteHelper->updateOrder([
            'id' => $order2Id,
            'customFields' => [
                'sumedia_winestro_order_details_export_tries' => 4
            ]
        ]);

        $ordersCollection = $method->invoke($orderExportTask, [$this->defaultSalesChannelId]);
        $this->assertCount(1, $ordersCollection);

        $this->testSuiteHelper->updateOrder([
            'id' => $order1Id,
            'customFields' => [
                'sumedia_winestro_order_details_order_number' => 'notsetted'
            ]
        ]);

        $ordersCollection = $method->invoke($orderExportTask, [$this->defaultSalesChannelId]);
        $this->assertCount(0, $ordersCollection);

        $this->testSuiteHelper->deleteOrder($order1Id);
        $this->testSuiteHelper->deleteOrder($order2Id);
        $this->testSuiteHelper->deleteOrder($order3Id);
        $this->testSuiteHelper->deleteOrder($order4Id);
    }

    public function testLoginTriesDate(): void
    {
        $orderRepository = $this->getContainer()->get('order.repository');
        $orderId = $this->testSuiteHelper->createOrder([], $this->customerId, $this->billingAddressId, $this->productId);
        $order = $orderRepository->search(new Criteria([$orderId]), $this->context)->first();
        $orderExportTask = $this->getContainer()->get('Sumedia\WinestroApi\Winestro\Task\OrderExportTask');
        $method = $this->getReflectedPublicMethod($orderExportTask, 'getLoginTriesDate');

        $testDate = (new \DateTime())->sub(\DateInterval::createFromDateString('0 hours'));
        $date = $method->invoke($orderExportTask, $order);
        $this->assertEquals($testDate->format('Y-m-d H'), $date->format('Y-m-d H'));

        $this->testSuiteHelper->updateOrder([
            'id' => $orderId,
            'customFields' => [
                'sumedia_winestro_order_details_export_tries' => 1
            ]
        ]);
        $order = $orderRepository->search(new Criteria([$orderId]), $this->context)->first();
        $testDate = (new \DateTime())->sub(\DateInterval::createFromDateString('6 hours'));
        $date = $method->invoke($orderExportTask, $order);
        $this->assertEquals($testDate->format('Y-m-d H'), $date->format('Y-m-d H'));

        $this->testSuiteHelper->updateOrder([
            'id' => $orderId,
            'customFields' => [
                'sumedia_winestro_order_details_export_tries' => 2
            ]
        ]);
        $order = $orderRepository->search(new Criteria([$orderId]), $this->context)->first();
        $testDate = (new \DateTime())->sub(\DateInterval::createFromDateString('12 hours'));
        $date = $method->invoke($orderExportTask, $order);
        $this->assertEquals($testDate->format('Y-m-d H'), $date->format('Y-m-d H'));

        $this->testSuiteHelper->updateOrder([
            'id' => $orderId,
            'customFields' => [
                'sumedia_winestro_order_details_export_tries' => 3
            ]
        ]);
        $order = $orderRepository->search(new Criteria([$orderId]), $this->context)->first();
        $testDate = (new \DateTime())->sub(\DateInterval::createFromDateString('18 hours'));
        $date = $method->invoke($orderExportTask, $order);
        $this->assertEquals($testDate->format('Y-m-d H'), $date->format('Y-m-d H'));

        $this->testSuiteHelper->deleteOrder($orderId);
    }

    public function testLoginTriesUpdate(): void
    {
        $orderRepository = $this->getContainer()->get('order.repository');
        $orderId = $this->testSuiteHelper->createOrder([], $this->customerId, $this->billingAddressId, $this->productId);
        $orderExportTask = $this->getContainer()->get('Sumedia\WinestroApi\Winestro\Task\OrderExportTask');
        $method = $this->getReflectedPublicMethod($orderExportTask, 'incrementLoginTries');

        /** @var OrderEntity $order */
        $order = $orderRepository->search(new Criteria([$orderId]), $this->context)->first();
        $this->assertNull($order->getCustomFieldsValue('sumedia_winestro_order_details_export_tries'));

        $date = (new \DateTime())->add(\DateInterval::createFromDateString('2 minutes'));
        $method->invoke($orderExportTask, $order, $date);

        $order = $orderRepository->search(new Criteria([$orderId]), $this->context)->first();
        $this->assertEquals(1, $order->getCustomFieldsValue('sumedia_winestro_order_details_export_tries'));

        $method->invoke($orderExportTask, $order, $date);

        $order = $orderRepository->search(new Criteria([$orderId]), $this->context)->first();
        $this->assertEquals(2, $order->getCustomFieldsValue('sumedia_winestro_order_details_export_tries'));

        $method->invoke($orderExportTask, $order, $date);

        $order = $orderRepository->search(new Criteria([$orderId]), $this->context)->first();
        $this->assertEquals(3, $order->getCustomFieldsValue('sumedia_winestro_order_details_export_tries'));
    }

    private function getReflectedPublicMethod(object $object, $method): ReflectionMethod
    {
        $method = new ReflectionMethod($object, $method);
        $method->setAccessible(true);
        return $method;
    }
}