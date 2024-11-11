<?php

namespace App\Logging;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;

class CloudWatchLoggerFactory
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $sdkParams = $config['sdk'];
        $tags = $config['tags'] ?? [ ];
        $name = $config['name'] ?? 'cloudwatch_access';

        // Instantiate CloudWatch Logs client
        $client = new CloudWatchLogsClient($sdkParams);

        $groupName = config('logging.channels.cloudwatch_access.group_name');

        $streamName = config('logging.channels.cloudwatch_access.stream_name');

        // Days to keep logs, 14 by default. Set to `null` to allow indefinite retention.
        $retentionDays = 1;

        // Instantiate handler (tags are optional)
        $handler = new CloudWatch($client, $groupName, $streamName, $retentionDays, 10000, $tags);
        $handler->setFormatter(new JsonFormatter());

        // Create a log channel
        $logger = new Logger($name);

        // Set handler
        $logger->pushHandler($handler);

        return $logger;
    }
}
