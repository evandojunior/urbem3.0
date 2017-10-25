<?php

namespace Application\Migrations;

use Urbem\CoreBundle\Helper\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170706180955 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("DELETE FROM prestacao_contas.fila_relatorio");

        $this->addSql("ALTER TABLE prestacao_contas.fila_relatorio ADD COLUMN relatorio_log TEXT DEFAULT NULL");
        $this->addSql("ALTER TABLE prestacao_contas.fila_relatorio ADD COLUMN nome VARCHAR(100) DEFAULT NULL");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql("ALTER TABLE prestacao_contas.fila_relatorio DROP COLUMN relatorio_log");
        $this->addSql("ALTER TABLE prestacao_contas.fila_relatorio DROP COLUMN nome");
    }
}
