<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\WinestroApi\Winestro\Cli;

use Shopware\Core\Framework\Log\Package;
use Sumedia\WinestroApi\Winestro\TaskManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sumedia:winestro:execute-task',
    description: 'Execute task',
)]
#[Package('administration')]
class ExecuteTask extends Command
{
    public function __construct(
        private TaskManagerInterface $taskManager
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('task', InputArgument::REQUIRED, 'Task ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $task = $input->getArgument('task');
        if (preg_match('/^[a-z0-9]{32}/i', $task)) {
            $taskId = $task;
        } else {
            $taskName = $task;
        }

        if (isset($taskName)) {
            $tasks = $this->taskManager->getTasksByName($taskName);
        } else {
            $tasks = [$this->taskManager->getTask($taskId)];
        }

        foreach ($tasks as $task) {
            $task->execute();
        }

        return 0;
    }
}
