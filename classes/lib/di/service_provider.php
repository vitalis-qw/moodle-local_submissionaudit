<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

declare(strict_types=1);

namespace local_submissionaudit\lib\di;

/**
 * Base class for a plugin service provider.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\hook\di_configuration;

/**
 * Registers a plugin's container bindings through Moodle's {@see di_configuration} hook.
 *
 * A plugin subclasses this, implements {@see register()} with bind()/factory()/alias()
 * calls, and lists the provider in its db/hooks.php:
 *
 *     ['hook' => \core\hook\di_configuration::class,
 *      'callback' => \local_example\service_provider::class . '::inject']
 *
 * Anything not bound here is autowired by class name, so only interfaces and services
 * with non-trivial construction need an entry. Fetch services with
 * \core\di::get(some_interface::class); never inject the container itself.
 */
abstract class service_provider {
    /** @var di_configuration The hook the definitions are added to. */
    private di_configuration $hook;

    /**
     * Hook callback entry point. Moodle calls it once while the container is built.
     *
     * @param di_configuration $hook
     */
    final public static function inject(di_configuration $hook): void {
        $provider = new static();
        $provider->hook = $hook;
        $provider->register();
    }

    /**
     * Add the plugin's definitions with bind(), factory() and alias().
     */
    abstract protected function register(): void;

    /**
     * Bind an interface (or abstract class) to an autowired implementation.
     *
     * @param class-string $abstract
     * @param class-string $concrete
     * @return static
     */
    final protected function bind(string $abstract, string $concrete): static {
        $this->hook->add_definition($abstract, \DI\autowire($concrete));
        return $this;
    }

    /**
     * Define an entry with a factory. The factory's parameters are resolved by the container.
     *
     * @param string $id
     * @param callable $factory
     * @return static
     */
    final protected function factory(string $id, callable $factory): static {
        $this->hook->add_definition($id, $factory);
        return $this;
    }

    /**
     * Make one id resolve to the entry of another.
     *
     * @param string $id
     * @param string $target
     * @return static
     */
    final protected function alias(string $id, string $target): static {
        $this->hook->add_definition($id, \DI\get($target));
        return $this;
    }
}
