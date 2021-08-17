<?php
    declare(strict_types=1);

    namespace Academy\ConsoleExample\Console\Command;



    use Magento\Framework\Module\Dir\Reader;
    use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use \Magento\Framework\App\Area;



    class ExampleCommand2 extends Command
    {

        const FILE = 'file';
//        const ARRAY = 'array';


        protected function configure()
        {


            $this->setName('academy:example2:command');
            $this->setDescription('Academy training console example.');
            $this->addOption(
                self::FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'file'
            );
//            $this->addOption(
//                self::ARRAY,
//                null,
//                InputOption::VALUE_REQUIRED,
//                'array'
//            );



            parent::configure();
        }

        /**
         * @var \Magento\Catalog\Model\ProductFactory
         */
        protected $productFactory;
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product
         */
        protected $resourceModel;
        /**
         * @var \Magento\InventoryApi\Api\Data\SourceItemInterface
         */
        protected $sourceItemsSaveInterface;
        /**
         * @var \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory
         */
        protected $sourceItem;
        /**
         * @var \Magento\Framework\App\State
         */
        protected $state;
        /**
         * @var \Magento\Store\Model\StoreManagerInterface
         */
        protected $storeManager;
        /**
         * @var \Magento\Framework\Module\Dir\Reader
         */

        protected $reader;
        protected $file;
        protected $json;
        protected $fileName = null;


        public function __construct(

            \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItem,
            \Magento\Catalog\Model\ProductFactory                     $productFactory,
            \Magento\Catalog\Model\ResourceModel\Product              $resourceModel,
            \Magento\InventoryApi\Api\Data\SourceItemInterface        $sourceItemsSaveInterface,
            \Magento\Framework\App\State                              $state,
            \Magento\Store\Model\StoreManagerInterface                $storeManager,

            File                                                      $file,
            \Magento\Framework\Module\Dir\Reader                      $reader,
            Json                                                      $json

        )

        {
            parent::__construct();
            $this->productFactory = $productFactory;
            $this->resourceModel = $resourceModel;
            $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
            $this->sourceItem = $sourceItem;
            $this->state = $state;
            $this->storeManager = $storeManager;

            $this->file = $file;
            $this->reader = $reader;
            $this->json = $json;

        }



        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $fileName = $input->getOption(self::FILE);

            $PathDirectory = $this->reader->getModuleDir(
                    \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                    'Academy_ConsoleExample') . '/' . $fileName;
            $JsonPath = $this->file->fileGetContents($PathDirectory);
            $myarray = $this->json->unserialize($JsonPath);
            $output->writeln(print_r(array_values($myarray['product'])));
            $output->writeln('hello');


            $this->state->setAreaCode(Area::AREA_ADMINHTML);
            $this->storeManager->setCurrentStore($this->storeManager->getDefaultStoreView()->getWebsiteId());

//            $addproduct = $input->getOption(self::ARRAY);


            foreach($myarray as $all) {
            $product = $this->productFactory->create();
        try{


                $product->setSku($all['sku']);
                $product->setName($all['name']);
                $product->setAttributeSetId($all['attribute_set_id']);
                $product->setStatus($all['status']);
                $product->setPrice($all['price']);
                $product->setVisibility($all['visibility']);
                $product->setTypeId($all['type_id']);
                $product->setWeight($all['weight']);
                $product->addImageToMediaGallery($all['image'],
                    ['image', 'small_image', 'thumbnail'],
                    false, false);
                $product->setCategoryIds([20, 21, 25]);
                $product->setWebsiteIds(array(1));
                $product->setStockData(array(
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'min_sale_qty' => 1,
                        'max_sale_qty' => 2,
                        'is_in_stock' => 1,
                        'qty' => 100
                    )
                );


            $product->save();


        } catch (\Exception $e) {
            echo $e->getMessage();
        } }
    }
 }






