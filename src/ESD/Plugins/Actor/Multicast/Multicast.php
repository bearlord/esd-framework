<?php

namespace ESD\Plugins\Actor\Multicast;

use ESD\Core\Memory\CrossProcess\Table;

class Multicast
{

    protected $channel;

    /**
     * Topic constructor.
     * @param Table $topicTable
     */
    public function __construct(Table $topicTable)
    {
        //Read the table first, because the process may restart
        $this->topicTable = $topicTable;
        foreach ($this->topicTable as $value) {
            $this->addSubFormTable($value['topic'], $value['uid']);
        }
    }

    public function publish($message)
    {

    }

    public function subscribe()
    {
        
    }

    public function unsubscribe()
    {

    }

}