<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Model\Cashbook\Cashbook\CashbookType;
use Model\Payment\IUnitResolver;
use Model\Unit\UnitHasNoParent;

class Version20180122114315 extends AbstractMigration
{
    /** @var IUnitResolver @inject */
    public IUnitResolver $unitResolver;

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE ac_cashbook ADD type VARCHAR(255) NOT NULL COMMENT \'(DC2Type:string_enum)\'');
        $this->addSql('UPDATE ac_cashbook c JOIN ac_object o ON o.id = c.id SET c.type = o.type');
        $this->addSql('ALTER TABLE ac_chits ADD category_operation_type VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:string_enum)\'');
        $this->addSql("
                UPDATE ac_chits c
                    JOIN ac_cashbook cb ON c.eventId = cb.id
                    JOIN ac_chitsCategory ct ON c.category = ct.id
                SET c.category_operation_type = ct.type
                WHERE cb.type IN ('troop', 'unit', 'general') # cashbooks with static categories only
                OR ct.id IN (12, 8) # static categories for camp (undefined income & undefined expense)
        ");
    }

    public function postUp(Schema $schema) : void
    {
        $unitCashbooks = $this->connection->fetchAll(
            "SELECT c.id as id, o.skautisId as unit_id FROM ac_cashbook c JOIN ac_object o ON o.id = c.id WHERE c.type = 'unit'"
        );

        foreach ($unitCashbooks as $cashbook) {
            $cashbook['unit_id'] = (int) $cashbook['unit_id'];
            try {
                $isOfficialUnit = $this->unitResolver->getOfficialUnitId($cashbook['unit_id']) === $cashbook['unit_id'];
                $this->connection->update('ac_cashbook', [
                    'type' => $isOfficialUnit ? CashbookType::OFFICIAL_UNIT : CashbookType::TROOP,
                ], ['id' => $cashbook['id']]);
            } catch (UnitHasNoParent $exc) {
                echo 'ERROR: ' . $exc->getMessage();
            }
        }
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE ac_cashbook DROP type');
    }
}
