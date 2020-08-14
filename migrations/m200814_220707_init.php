<?php

use yii\db\Migration;

class m200814_220707_init  extends Migration {

    public function safeUp() {

        $this->execute('
            CREATE TABLE `edi_company` (
              `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
              `code` varchar(35) NOT NULL,
              `ref_id` int(10) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ');
        $this->execute('
            CREATE TABLE `edi_message_type` (
              `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
              `code` char(6) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1

        ');
        $this->execute('
            CREATE TABLE `edi_message` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `read_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `type` enum(\'In\',\'Out\') NOT NULL,
              `interchange_sender_company_id` tinyint(5) unsigned DEFAULT NULL,
              `interchange_recipient_company_id` tinyint(5) unsigned DEFAULT NULL,
              `preperation_time` timestamp NULL DEFAULT NULL,
              `messageReferenceNumber` char(14) DEFAULT NULL,
              `message_type_id` tinyint(3) unsigned NOT NULL,
              `messageRelease` char(3) DEFAULT NULL,
              `data` longtext,
              `status` enum(\'New\',\'Processed\',\'Error\') NOT NULL,
              `errror` text,
              PRIMARY KEY (`id`),
              KEY `edi_messages_ibfk_message_type` (`message_type_id`),
              KEY `edi_message_ibfk_sender` (`interchange_sender_company_id`),
              KEY `edi_message_ibfk_recipient` (`interchange_recipient_company_id`),
              CONSTRAINT `edi_message_ibfk_message_type` FOREIGN KEY (`message_type_id`) REFERENCES `edi_message_type` (`id`),
              CONSTRAINT `edi_message_ibfk_recipient` FOREIGN KEY (`interchange_recipient_company_id`) REFERENCES `edi_company` (`id`),
              CONSTRAINT `edi_message_ibfk_sender` FOREIGN KEY (`interchange_sender_company_id`) REFERENCES `edi_company` (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1

        ');
        $this->execute('
            CREATE TABLE `edi_message_ref` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `message_id` int(10) unsigned NOT NULL,
              `sys_model_id` tinyint(3) unsigned NOT NULL,
              `ref_record_id` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `edi_message_ref_message` (`message_id`),
              KEY `edi_message_ref_ibfk_model` (`sys_model_id`),
              CONSTRAINT `edi_message_ref_ibfk_model` FOREIGN KEY (`sys_model_id`) REFERENCES `sys_models` (`id`),
              CONSTRAINT `edi_message_ref_message` FOREIGN KEY (`message_id`) REFERENCES `edi_message` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1

        ');
    }

    public function safeDown() {
        echo "m200814_220707_init cannot be reverted.\n";
        return false;
    }
}
