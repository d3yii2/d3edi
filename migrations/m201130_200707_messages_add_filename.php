<?php

use yii\db\Migration;

class m201130_200707_messages_add_filename  extends Migration {

    public function safeUp() {

        $this->execute('
            ALTER TABLE `edi_message` ADD COLUMN `file_name` VARCHAR(255) NULL AFTER `errror`; 
        ');

    }

    public function safeDown() {
        echo "m201130_200707_messages_add_filename cannot be reverted.\n";
        return false;
    }
}
