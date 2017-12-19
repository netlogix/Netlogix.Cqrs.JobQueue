<?php
namespace Netlogix\Cqrs\JobQueue\Command;

/*
 * This file is part of the Netlogix.Cqrs.JobQueue package.
 */

use Neos\Flow\Property\PropertyMapper;
use Neos\Utility\TypeHandling;
use Netlogix\Cqrs\Command\AbstractCommand;
use Netlogix\Cqrs\Command\AsynchronousCommandInterface;
use Netlogix\Cqrs\Command\CommandHandlerInterface;
use Netlogix\Cqrs\Command\CommandInterface;
use Neos\Flow\Annotations as Flow;
use Flowpack\JobQueue\Common\Annotations as Job;

/**
 * Handles commands and executes them asynchronously
 * @Flow\Scope("singleton")
 */
class AsyncJobQueueCommandHandler implements CommandHandlerInterface {

	/**
	 * @Flow\Inject
	 * @var PropertyMapper
	 */
	protected $propertyMapper;

	/**
	 * Check whether a command handler can handle a given command
	 *
	 * @param CommandInterface $command
	 * @return boolean
	 */
	public function canHandle(CommandInterface $command) {
		return $command instanceof AsynchronousCommandInterface;
	}

	/**
	 * @param CommandInterface $command
	 */
	public function handle(CommandInterface $command) {
		if (!($command instanceof AsynchronousCommandInterface)) {
			throw new \InvalidArgumentException('$command must implement AsynchronousCommandInterface to be handled', 1513616140);
		}
		if (!($command instanceof AbstractCommand)) {
			throw new \InvalidArgumentException('$command must be of type AbstractCommand', 1513616340);
		}

		$this->handleAsync($command->getCommandId(), TypeHandling::getTypeForValue($command));
	}

	/**
	 * @Job\Defer(queueName="commands")
	 * @param string $commandId
	 * @param string $type
	 */
	public function handleAsync($commandId, $type) {
		$command = $this->propertyMapper->convert(['__identity' => $commandId], $type);
		$command->execute();
	}
}
