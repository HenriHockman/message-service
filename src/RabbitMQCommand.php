<?php
namespace Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\RabbitMQ\MessageConsumer;


/**
 * Class RabbitMQCommand
 */
class RabbitMQCommand extends Command
{

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * CliCommand constructor.
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        // Configure command
        $this
            ->setName('utils:rabbitmq')
            ->setDescription('Console command to use RabbitMQ');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \LogicException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        // Create output decorator helpers for the Symfony Style Guide.
        $this->io = new SymfonyStyle($input, $output);

        // Set title
        $this->io->title($this->getDescription());

        $this->process();

        $this->io->success('All done - have a nice day!');
    }

    private function process(): void
    {
        $commands = [
            'Consume SMS',
            'Exit'
        ];
        $question = new ChoiceQuestion('What do you want to do?', $commands);
        $question->setAutocompleterValues($commands);
        try {
            switch ($this->io->askQuestion($question)) {
                case 'Consume SMS':
                    $this->consume();
                    break;
                case 'Exit':
                    return;
                    break;
                default:
                    break;
            }
        } catch (\Exception $exception) {
            $this->io->error($exception->getMessage());
        }
        $this->process();
    }

    /**
     * Start listening for messages in the RabbitMQ channel
     */
    private function consume() {

      $consumer = new MessageConsumer();

      $consumer->listen();

      $this->io->success('Process ended by user');

      return null;
    }
}
