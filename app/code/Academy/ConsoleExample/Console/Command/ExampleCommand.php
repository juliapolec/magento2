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
    use Magento\Framework\Serialize\Serializer\Json;


    class ExampleCommand extends Command
    {
        const FILE = 'file';
        const SERIALIZED = 'serialized';
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
            $this->addOption(
                self::SERIALIZED,
                null,
                InputOption::VALUE_NONE,
                'serialized'
            );

            parent::configure();
        }


        protected $reader;
        protected $file;
        protected $json;
        protected $fileName = null;


        public function __construct(

            Json          $json,
            Reader        $reader,
            File          $file


        )

        {
            parent::__construct();
            $this->reader = $reader;
            $this->file = $file;
            $this->json = $json;

        }


        /**
         * @throws \Magento\Framework\Exception\FileSystemException
         */
        protected function execute(InputInterface $input, OutputInterface $output)
        {
                $fileName = $input->getOption(self::FILE);

                $PathDirectory = $this->reader->getModuleDir(
                    \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                    'Academy_ConsoleExample'). '/'. $fileName;
                $JsonPath = $this->file->fileGetContents($PathDirectory);
                $product = $this->json->unserialize($JsonPath);

//                $output->writeln( $fileName );
//                $output->writeln( $PathDirectory );


                if($this->file->isExists($PathDirectory)){
                    $output->writeln("<info>exist </info>". $fileName);
//                    $output->writeln($this->file->fileGetContents($PathDirectory));
                    $output->writeln($product['product']);
                } else{
                    $output->writeln("<error>not found</error>");
                }


                $serialized = $input->getOption(self::SERIALIZED);
                if($serialized == true){
                    $output->writeln('success');
                }
                else{
                    $output->writeln('no access');

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
