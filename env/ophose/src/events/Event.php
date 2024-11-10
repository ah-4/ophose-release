<?php

namespace Ophose\Events;

/**
 * Event class
 */
class Event
{
    protected $data = null;
    protected $cancellable = false;
    protected $cancelled = false;
    public static $listeners = [];

    /**
     * Constructor for Event class
     *
     * @param mixed $data The event data
     * @param boolean $cancellable Whether the event is cancellable
     */
    public function __construct(mixed $data = null, bool $cancellable = false)
    {
        $this->data = $data;
        $this->cancellable = $cancellable;
    }

    /**
     * Check if the event is cancellable
     *
     * @return boolean
     */
    public function isCancellable()
    {
        return $this->cancellable;
    }

    /**
     * Get the event data
     *
     * @return mixed
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Cancel the event
     */
    public function cancel()
    {
        $this->cancelled = true;
    }

    /**
     * Set the cancelled status of the event
     *
     * @param boolean $cancelled
     */
    public function setCancelled(bool $cancelled)
    {
        $this->cancelled = $cancelled;
    }

    /**
     * Check if the event is cancelled
     *
     * @return boolean
     */
    public function isCancelled()
    {
        return $this->cancellable && $this->cancelled;
    }

    /**
     * This method is called after all event listeners have been called (useful for executing
     * code after the event has been handled and checking if the event has been cancelled)
     */
    protected function after() {
        return;
    }

    public function __destruct()
    {
        $this->after();
    }

}

/**
 * Dispatch an event
 *
 * @param Event $event The event to dispatch
 * @return Event The dispatched event
 */
function dispatch($event) {
    if(empty(Event::$listeners)) {
        if(!file_exists(ROOT . 'app/event')) {
            return $event;
        }
        foreach(o_get_files_recursive(ROOT . 'app/event', 'php') as $file) {
            include_once $file;
        }
    }
    if(!is_subclass_of($event, Event::class)) {
        throw new \Exception("Event must be a subclass of Event");
    }
    if(!class_exists($event::class)) {
        $event_name = get_class($event);
        throw new \Exception("Event class $event_name does not exist");
    }
    if(!isset(Event::$listeners[$event::class])) {
        return $event;
    }
    foreach(Event::$listeners[$event::class] as $listener) {
        if($event->isCancellable() && $event->isCancelled()) break;
        $listener($event);
    }
    return $event;
}

/**
 * Listen for an event
 *
 * @param string $event The event to listen for
 * @param callable $callback The callback to execute when the event is dispatched (called with the event as the first argument)
 */
function listen($event, $callback) {
    if(!class_exists($event)) {
        throw new \Exception("Event class $event does not exist");
    }
    if(!is_callable($callback)) {
        throw new \Exception("Callback is not callable");
    }
    if(!is_subclass_of($event, Event::class)) {
        throw new \Exception("Event class $event must be a subclass of Event");
    }
    Event::$listeners[$event][] = $callback;
}