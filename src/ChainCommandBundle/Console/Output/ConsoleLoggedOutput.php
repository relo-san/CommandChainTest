<?php
declare(strict_types = 1);

/**
 * Test project.
 * @license http://www.spdx.org/licenses/MIT    MIT License
 */

namespace ChainCommandBundle\Console\Output;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * CLI output with logging all messages.
 * @author  Mykola Zyk <mykola.zyk@dinecat.com>
 */
class ConsoleLoggedOutput extends ConsoleOutput
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param   LoggerInterface                 $logger
     * @param   int                             $verbosity  Verbosity level.
     * @param   bool|null                       $decorated  Flag for decorate messages.
     * @param   OutputFormatterInterface|null   $formatter
     */
    public function __construct(
        LoggerInterface $logger,
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        $this->logger = $logger;

        parent::__construct($verbosity, $decorated, $formatter);
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $options = self::OUTPUT_NORMAL)
    {
        $messages = (array) $messages;

        foreach ($messages as $message) {
            $this->logger->log(200, strip_tags($message));
        }
        reset($messages);

        parent::write($messages, $newline, $options);
    }
}
