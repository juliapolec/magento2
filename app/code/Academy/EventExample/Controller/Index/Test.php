<?php

namespace Academy\EventExample\Controller\Index;

class Test extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
        $textDisplay = new \Magento\Framework\DataObject(array('text' => 'Academy'));
        $this->_eventManager->dispatch('academy_eventexample_display_text', ['mp_text' => $textDisplay]);
        echo $textDisplay->getText();
        exit;
    }
}
