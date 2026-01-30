<?php

use yii\db\Migration;

/**
 * Example migration for system_settings table
 * 
 * Copy this to your application's migrations folder and adjust as needed.
 */
class m000000_000000_create_system_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%system_settings}}', [
            'id' => $this->primaryKey(),
            'key_name' => $this->string(100)->notNull()->unique(),
            'value' => $this->text(),
            'description' => $this->string(500),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->null(),
        ], $tableOptions);

        $this->createIndex('idx-system_settings-key', '{{%system_settings}}', 'key_name');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%system_settings}}');
    }
}
