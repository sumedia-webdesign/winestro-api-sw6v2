<?php declare(strict_types=1);

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> */

namespace Sumedia\WinestroApi\Winestro\Task;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\WinestroApi\Config;
use Sumedia\WinestroApi\RepositoryManager;
use Sumedia\WinestroApi\Winestro\LogManagerInterface;
use Sumedia\WinestroApi\Winestro\RequestManager;
use Sumedia\WinestroApi\Winestro\TaskManager;
use Symfony\Component\DependencyInjection\Container;

class NewsletterReceiverImportTask extends AbstractTask
{
    private $salutationIds;

    public function __construct(
        Container $container,
        Config $config,
        TaskManager $taskManager,
        RepositoryManager $repositoryManager,
        RequestManager $requestManager,
        LogManagerInterface $logManager,
        private Context $context
    ) {
        parent::__construct($container, $config, $taskManager, $repositoryManager, $requestManager, $logManager);
    }

    public function execute(TaskInterface $parentTask = null): void
    {
        $this->_execute($parentTask, [$this, 'newsletterReceiverImport']);

    }

    public function newsletterReceiverImport(): void
    {
        $connection = $this->getWinestroConnection();
        $request = $this->requestManager->createRequest(RequestManager::GET_CUSTOMER_GROUPS_FROM_WINESTRO_REQUEST);
        $response = $connection->executeRequest($request);
        $customerGroups = $response->toArray();

        $done = [];
        $groups = [];
        foreach ($customerGroups as $customerGroup) {
            $connection = $this->getWinestroConnection();
            $request = $this->requestManager->createRequest(RequestManager::GET_CUSTOMERS_FROM_WINESTRO_REQUEST);
            $request->setParameter('id_grp', $customerGroup['id']);
            $response = $connection->executeRequest($request);
            $customers = $response->toArray();

            $receivers = [];
            foreach ($customers as $customer) {
                $active = (bool) $customer['isNewsletterActive'];
                $email = $customer['email'];
                $receiver = $this->repositoryManager->search('newsletter_recipient',
                    (new Criteria())->addFilter(new EqualsFilter('email', $email))
                )->first();
                //if (null === $receiver || ($active && ($active ? 'optIn' : 'optOut') !== $receiver->getStatus())) {
                    $street = $customer['street'] . ' ' . $customer['streetNumber'];
                    $groups[$customer['email']][] = $customerGroup['name'];
                    if (!in_array($customer['email'], $done)) {
                        $receivers[] = [
                            'id' => null === $receiver ? Uuid::randomHex() : $receiver->getId(),
                            'firstName' => null === $receiver || empty($receiver->getFirstName())
                                ? $customer['firstname'] : $receiver->getFirstName(),
                            'lastName' => null === $receiver || empty($receiver->getLastName())
                                ? $customer['lastname'] : $receiver->getLastName(),
                            'email' => $email,
                            'zipCode' => null === $receiver || empty($receiver->getZipCode())
                                ? $customer['zipcode'] : $receiver->getZipCode(),
                            'city' => null === $receiver || empty($receiver->getCity())
                                ? $customer['firstname'] : $receiver->getCity(),
                            'street' => null === $receiver || empty($receiver->getStreet())
                                ? $street : $receiver->getStreet(),
                            'status' => $active ? 'optIn' : 'optOut',
                            'hash' => null === $receiver || empty($receiver->getHash())
                                ? Uuid::randomHex() : $receiver->getHash(),
                            'salutationId' => null === $receiver || empty($receiver->getSalutationId())
                                ? $this->getSalutationIdBySalutationText($customer['salutation']) : $receiver->getSalutationId(),
                            'languageId' => null === $receiver || empty($receiver->getLanguageId())
                                ? current($this->context->getLanguageIdChain()) : $receiver->getLanguageId(),
                            'salesChannelId' => null === $receiver || empty($receiver->getSalesChannelId())
                                ? $this['salesChannelId'] : $receiver->getSalesChannelId(),
                            'confirmedAt' => null === $receiver || empty($receiver->getConfirmedAt())
                                ? date('Y-m-d H:i:s') : $receiver->getConfirmedAt(),
                        ];
                    }
                    $done[] = $customer['email'];
                //}
            }

            if (count($receivers)) {
                foreach ($receivers as &$receiver) {
                    if (isset($groups[$receiver['email']])) {
                        $receiver['customFields'] = [
                            'sumedia_winestro_newsletter_recipient_details_groups' =>
                                implode(',', $groups[$receiver['email']])
                        ];
                    }
                }
                $this->repositoryManager->upsert('newsletter_recipient', $receivers);
            }

        }
    }

    private function getSalutationIdBySalutationText($salutationText): ?string
    {
        if (!isset($this->salutationIds[$salutationText])) {
            $this->salutationIds[$salutationText] = null;
            $salutation = $this->repositoryManager->search('salutation',
                (new Criteria())->addFilter(new EqualsFilter('displayName', $salutationText))
            )->first();
            if ($salutation) {
                $this->salutationIds[$salutationText] = $salutation->getId();
            }
        }
        return $this->salutationIds[$salutationText];
    }
}