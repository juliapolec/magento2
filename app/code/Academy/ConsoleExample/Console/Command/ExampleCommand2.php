<?php
    declare(strict_types=1);

    namespace Academy\ConsoleExample\Console\Command;



    use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Filesystem\Driver\File;


use \Magento\Framework\App\Area;



    class ExampleCommand2 extends Command
    {
        const FILE = 'file';

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



        protected $file;
        protected $json;
        protected $fileName = null;


    public function __construct(

        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItem,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel,
        \Magento\InventoryApi\Api\Data\SourceItemInterface $sourceItemsSaveInterface,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,

        File          $file


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

    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('hello');
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $this->storeManager->setCurrentStore($this->storeManager->getDefaultStoreView()->getWebsiteId());

        $product = $this->productFactory->create();

        try{

        $product->setName('Test Product');
        $product->setTypeId('simple');
        $product->setAttributeSetId(4);
        $product->setSku('test-SKU');
        $product->setWebsiteIds(array(1));
        $product->setVisibility(4);
        $product->setPrice(array(1));
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
        }
    }



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


            parent::configure();
        }

}
