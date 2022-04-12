<?php declare(strict_types=1);

namespace Inhere\Kite\Concern;

use InvalidArgumentException;
use function count;
use function in_array;
use function preg_match;

/**
 * Class SimpleEventStaticTrait
 *
 * @package Inhere\Console\Concern
 */
trait SimpleEventAwareTrait
{
    /**
     * set the supported events, if you need.
     *  if it is empty, will allow register any event.
     *
     * @var array<string>
     */
    protected array $supportedEvents = [];

    /**
     * registered Events
     *
     * ```php
     * [
     *  'event' => bool, // is once event
     * ]
     * ```
     *
     * @var array
     */
    private array $events = [];

    /**
     * events and handlers
     *
     * ```php
     * [
     *  'event' => callable, // event handler
     * ]
     * ```
     *
     * @var array<string, callable>
     */
    private array $eventHandlers = [];

    /**
     * register a event handler
     *
     * @param string   $event
     * @param callable $handler
     * @param bool     $once
     */
    public function on(string $event, callable $handler, bool $once = false): void
    {
        if (!$this->isSupportedEvent($event)) {
            throw new InvalidArgumentException('register unsupported event: ' . $event);
        }

        $this->eventHandlers[$event][] = $handler;

        if (!isset($this->events[$event])) {
            $this->events[$event] = $once;
        }
    }

    /**
     * register a once event handler
     *
     * @param string   $event
     * @param callable $handler
     */
    public function once(string $event, callable $handler): void
    {
        $this->on($event, $handler, true);
    }

    /**
     * trigger event
     *
     * @param string $event
     * @param mixed  ...$args
     *
     * @return bool
     */
    public function fire(string $event, ...$args): bool
    {
        if (!isset($this->events[$event])) {
            return false;
        }

        // call event handlers of the event.
        /** @var mixed $return */
        $return = true;
        foreach ((array)$this->eventHandlers[$event] as $cb) {
            $return = $cb(...$args);
            // return FALSE to stop go on handle.
            if (false === $return) {
                break;
            }
        }

        // is a once event, remove it
        if ($this->events[$event]) {
            return $this->off($event);
        }

        return (bool)$return;
    }

    /**
     * remove event and it's handlers
     *
     * @param string $event
     *
     * @return bool
     */
    public function off(string $event): bool
    {
        if ($this->hasEvent($event)) {
            unset($this->events[$event], $this->eventHandlers[$event]);
            return true;
        }

        return false;
    }

    /**
     * @param string $event
     *
     * @return bool
     */
    public function hasEvent(string $event): bool
    {
        return isset($this->events[$event]);
    }

    /**
     * @param string $event
     *
     * @return bool
     */
    public function isOnce(string $event): bool
    {
        if ($this->hasEvent($event)) {
            return $this->events[$event];
        }

        return false;
    }

    /**
     * check $name is a supported event name
     *
     * @param string $event
     *
     * @return bool
     */
    public function isSupportedEvent(string $event): bool
    {
        if (!$event || !preg_match('/[a-zA-Z][\w-]+/', $event)) {
            return false;
        }

        if ($ets = $this->supportedEvents) {
            return in_array($event, $ets, true);
        }

        return true;
    }

    /**
     * @return array
     */
    public function getSupportEvents(): array
    {
        return $this->supportedEvents;
    }

    /**
     * @param array $supportedEvents
     */
    public function setSupportEvents(array $supportedEvents): void
    {
        $this->supportedEvents = $supportedEvents;
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @return int
     */
    public function countEvents(): int
    {
        return count($this->events);
    }
}
