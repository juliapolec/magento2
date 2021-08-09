<?php
namespace Academy\TurboModule\Block;


class Hello extends \Magento\Framework\View\Element\Template
{
    public function getHelloWorldTxt()
    {
        return 'Hello world! '. date('Y-F-d');

    }
}
