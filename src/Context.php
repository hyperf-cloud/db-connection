<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Framework\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Context as RequestContext;
use Hyperf\Contract\ConnectionInterface as PoolConnectionInterface;
use Psr\Container\ContainerInterface;

class Context
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * Get a connection from request context.
     */
    public function connection(string $name): ?ConnectionInterface
    {
        $connections = [];
        if (RequestContext::has('databases')) {
            $connections = RequestContext::get('databases');
        }

        if (isset($connections[$name]) && $connections[$name] instanceof ConnectionInterface) {
            $connection = $connections[$name];
            if (! $connection instanceof PoolConnectionInterface) {
                $this->logger->warning(sprintf(
                    'Connection[] is not instanceof %s',
                    get_class($connection),
                    PoolConnectionInterface::class
                ));
                return $connection;
            }

            return $connection->getConnection();
        }

        return null;
    }

    /**
     * @return ConnectionInterface[]
     */
    public function connections(): array
    {
        $connections = [];
        if (RequestContext::has('databases')) {
            $connections = RequestContext::get('databases');
        }

        return $connections;
    }

    public function set($name, ConnectionInterface $connection): void
    {
        $connections = $this->connections();
        $connections[$name] = $connection;
        RequestContext::set('databases', $connections);
    }
}
