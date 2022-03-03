<?php

namespace Codeception\Module\Yii1;

class TransactionWrapper
{
    /**
     * @var \components\db\Connection
     */
    private $db;

    public function __construct(\components\db\Connection $db)
    {
        $this->db = $db;
    }

    public function start()
    {
        $this->db->beginTransaction();
    }

    public function rollback()
    {
        if ($this->db->getPdoInstance()->inTransaction()) {
            try {
                $this->db->getPdoInstance()->rollBack();
            } catch (\Throwable $e) {
                codecept_debug('Rollback failed on PDO transaction.');
            }
        }
        $this->db->getCurrentTransaction();
    }
}
