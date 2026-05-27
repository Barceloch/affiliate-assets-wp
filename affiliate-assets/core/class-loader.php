<?php
/**
 * The loader that registers all hooks and filters for the plugin.
 * Inspired by SliceWP's hook management architecture.
 *
 * @package AffiliateAssets\Core
 */

namespace AffiliateAssets\Core;

class Class_Loader {
    
    /**
     * Array of actions to be registered with WordPress.
     *
     * @var array
     */
    protected $actions;
    
    /**
     * Array of filters to be registered with WordPress.
     *
     * @var array
     */
    protected $filters;
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }
    
    /**
     * Add a new action to be registered.
     *
     * @param string   $hook          The name of the action.
     * @param callable $component     The class/method to execute.
     * @param int      $priority      Priority of the action.
     * @param int      $accepted_args Number of arguments accepted.
     */
    public function add_action($hook, $component, $method, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $method, $priority, $accepted_args);
    }
    
    /**
     * Add a new filter to be registered.
     *
     * @param string   $hook          The name of the filter.
     * @param callable $component     The class/method to execute.
     * @param int      $priority      Priority of the filter.
     * @param int      $accepted_args Number of arguments accepted.
     */
    public function add_filter($hook, $component, $method, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $method, $priority, $accepted_args);
    }
    
    /**
     * Helper function to add hooks.
     *
     * @param array    $hooks         Existing hooks.
     * @param string   $hook          The name of the hook.
     * @param callable $component     The class/method to execute.
     * @param int      $priority      Priority of the hook.
     * @param int      $accepted_args Number of arguments accepted.
     * @return array
     */
    private function add($hooks, $hook, $component, $method, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'method'        => $method,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );
        return $hooks;
    }
    
    /**
     * Register all filters and actions with WordPress.
     */
    public function run() {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['method']), $hook['priority'], $hook['accepted_args']);
        }
        
        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['method']), $hook['priority'], $hook['accepted_args']);
        }
    }
}
