<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use function explode;
use function file_get_contents;
use function trim;
use const PHP_EOL;

class Version20170304182827 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $dump = file_get_contents(__DIR__ . '/initial_schema.sql');

        $statement = '';
        foreach (explode(PHP_EOL, $dump) as $row) {
            if ($row === '') {
                $this->addSql(trim($statement));
                $statement = '';
            } else {
                $statement .= ' ' . trim($row);
            }
        }
    }

    public function down(Schema $schema) : void
    {
    }
}
