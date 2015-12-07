<?php

namespace Bolt\Extension\pygillier\IftttMaker;

use Bolt\Application;
use Bolt\BaseExtension;
use Guzzle\Http\Client as HTTPClient;

class Extension extends BaseExtension
{
    private $http_client;
    private $old_status;

    public function initialize()
    {
        // Guzzle Client
        $this->http_client = new HTTPClient($this->config['channel_url']);
        
        // PreSave Event
        $this->app['dispatcher']->addListener(\Bolt\Events\StorageEvents::PRE_SAVE, array($this, 'preSaveCallback'));
        // PostSave Event
        $this->app['dispatcher']->addListener(\Bolt\Events\StorageEvents::POST_SAVE, array($this, 'postSaveCallback'));
        
        // Timed publication event (direct access to method as it is a publishing event)
        $this->app['dispatcher']->addListener(\Bolt\Events\StorageEvents::TIMED_PUBLISH, array($this, 'dispatchToChannel'));
    }

    public function getName()
    {
        return "IftttMaker";
    }
    
    /**
     * Stores status of previous version of object.
     */
    public function preSaveCallback(\Bolt\Events\StorageEvent $event)
    {
        $this->old_status = $this->app['storage']->getContentObject(
            $event->getContentType(), 
            array(
                'id' => $event->getId()
            )
        )->get('status');
    }

    /**
     * Publish on edition if status is valid
     *
     *  Using stored value ($old_status) during preSave callback, this method will dispatch the event to the channel
     * if current status is published and old one wasn't published.
     */
    public function postSaveCallback(\Bolt\Events\StorageEvent $event)
    {
        // Discard non published contents
        if($event->getContent()->get('status') == 'published' && $this->old_status != 'published')
        {
            $this->dispatchToChannel($event);
        }
    }
    
    /**
     * Dispatch the event to IFTTT channel
     *
     * Only control if the content belongs to allowed content-types to broadcast.
     */
    public function dispatchToChannel(\Bolt\Events\StorageEvent $event)
    {
        $id = $event->getId();
        $contenttype = $event->getContentType();
        $content = $event->getContent();
        
        // Only allowed content types in published state.
        if( !in_array($contenttype, $this->config['content_types'])) 
        {
            return;
        }

        // Payload 
        $payload = array(
            'value1' => $content->get('title'),
            'value2' => $this->app['resources']->getUrl('hosturl').$content->link()
        );
        
        $event_name = $this->config['event_name'];
        
        try {
            $this->sendRequest($event_name, $payload);
            $this->log("Channel notified with event '${event_name}' for ${contenttype}#${id}");
        }
        catch(\Guzzle\Http\Exception\BadResponseException $e)
        {
            $code = $e->getResponse()->getStatusCode();
            $message = $e->getMessage();
            $this->log("Error : HTTP/${code} - ${message}", "error");
        }
    }
    
    private function sendRequest($event_name, $payload)
    {
        // Define called URI
        $uri = sprintf('/trigger/%s/with/key/%s', $event_name, $this->config['channel_key']);
        
        $request = $this->http_client->post($uri, [], $payload);
        
        $request->send();
    }
    
    /**
     * Utility logging method
     * 
     * @param mixed $message The message to send to syslog
     * @param string $level Logging level (default "info")
     * @return void
     */
    private function log($message, $level="info")
    {
        call_user_func(
            array($this->app['logger.system'], $level), 
            "IFTTT Maker: ".$message, 
            array('event' => 'extension')
        );

    }
}
