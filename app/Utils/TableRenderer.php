<?php

namespace CryptoTrade\Utils;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class TableRenderer
{
    public static function render(
        array $headers,
        array $rows
    ): void
    {
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();
    }
}
