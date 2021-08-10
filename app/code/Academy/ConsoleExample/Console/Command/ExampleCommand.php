<?php
    declare(strict_types=1);

    namespace Academy\ConsoleExample\Console\Command;

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Magento\Framework\Filesystem\Driver\File;
    use Magento\Framework\Module\Dir\Reader;


    class ExampleCommand extends Command
    {
        const FILE = 'file';
//                    public const FILE_NAME_ARGUMENT = 'file';
//                    public const IMPORT_DIR = '/import/';
        protected function configure()

        {

            parent::configure();
            $this->setName('academy:example:command');
            $this->setDescription('Academy training console example.');
            //$this->addArgument('product_id', InputArgument::REQUIRED, 'Product id');
//        $this->addArgument('product_ids', InputArgument::REQUIRED| InputArgument::IS_ARRAY, 'Product ids');
            $this->addOption(
                self::FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'file'
            );
            parent::configure();
        }


        protected $reader;
        protected $file;
        protected $fileName = null;


        public function __construct(

            Reader        $reader,
            File          $file

        )

        {
            parent::__construct();
            $this->reader = $reader;
            $this->file = $file;

        }


        /**
         * @throws \Magento\Framework\Exception\FileSystemException
         */
        protected function execute(InputInterface $input, OutputInterface $output)
        {
                $fileName = $input->getOption(self::FILE);

                $PathDirectory = $this->reader->getModuleDir(
                    \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                    'Academy_TurboModule');
                $FullPathDirectory = $PathDirectory. '/'. $fileName;

                $output->writeln( $fileName );
                $output->writeln( $FullPathDirectory );
//                $output->writeln($this->file->fileGetContents($PathDirectory));

                if($this->file->isExists($FullPathDirectory)){
                    $output->writeln("exist ". $fileName);
                    $output->writeln($this->file->fileGetContents($FullPathDirectory));
                } else{
                    $output->writeln("not found");
                }

        }
    }
//        formatted text
//        $output->writeln('<info>Hello world!</info>');
//        $output->writeln('<comment>Hello world!</comment>');
//        $output->writeln('<error>Hello world!</error>');
//        $output->writeln('<question>Hello world!</question>');
//        $output->writeln('Hello world!');

//        id
//        $productid = $input->getArgument('product_id');
//        $output->writeln($productid);



//        $productids = $input->getArgument('product_ids');
//        $output->writeln($productids);
//    }
//}
