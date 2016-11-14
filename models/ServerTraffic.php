<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "server_traffic".
 *
 * @property string $server_ip
 * @property string $interface
 * @property double $Inbound
 * @property double $Outbound
 * @property string $timestamp
 */
class ServerTraffic extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'server_traffic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['server_ip', 'interface', 'Inbound', 'Outbound', 'timestamp'], 'required'],
            [['Inbound', 'Outbound'], 'number'],
            [['server_ip'], 'string', 'max' => 32],
            [['interface', 'timestamp'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'server_ip' => 'Server Ip',
            'interface' => 'Interface',
            'Inbound' => 'Inbound',
            'Outbound' => 'Outbound',
            'timestamp' => 'Timestamp',
        ];
    }
}
