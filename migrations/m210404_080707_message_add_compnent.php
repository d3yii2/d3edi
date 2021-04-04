<?php

use yii\db\Migration;

class m210404_080707_message_add_compnent  extends Migration {

    public function safeUp() { 
        $this->execute('
            ALTER TABLE `edi_message`   
              ADD COLUMN `component` VARCHAR(50) NULL AFTER `id`;
                    
        ');
    }

    public function safeDown() {
        echo "m210404_080707_message_add_compnent cannot be reverted.\n";
        return false;
    }
}
