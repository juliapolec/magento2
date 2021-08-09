<?php
    declare(strict_types=1);

    namespace Academy\ConsoleExample\Console\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;


    class ExampleCommand extends Command
{

    protected function configure()

    {

        parent::configure();
        $this->setName('academy:example:command');
        $this->setDescription('Academy training console example.');
        //$this->addArgument('product_id', InputArgument::REQUIRED, 'Product id');
        $this->addArgument('product_ids', InputArgument::REQUIRED| InputArgument::IS_ARRAY, 'Product ids');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        formatted text
//        $output->writeln('<info>Hello world!</info>');
//        $output->writeln('<comment>Hello world!</comment>');
//        $output->writeln('<error>Hello world!</error>');
//        $output->writeln('<question>Hello world!</question>');
//        $output->writeln('Hello world!');

//        id
//        $productid = $input->getArgument('product_id');
//        $output->writeln($productid);


        $productids = $input->getArgument('product_ids');
        $output->writeln($productids);
    }
}
