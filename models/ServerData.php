<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "server_data".
 *
 * @property integer $id
 * @property string $hostname
 * @property string $ip
 * @property double $cpu_load
 * @property double $mem_usage
 * @property string $disk
 * @property double $disk_usage
 * @property double $timestamp
 * @property string $disk_info
 * @property string $ps_info
 */
class ServerData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'server_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hostname', 'ip', 'cpu_load', 'mem_usage', 'disk', 'disk_usage', 'timestamp', 'disk_info', 'ps_info'], 'required'],
            [['cpu_load', 'mem_usage', 'disk_usage', 'timestamp'], 'number'],
            [['disk_info', 'ps_info'], 'string'],
            [['hostname', 'ip'], 'string', 'max' => 20],
            [['disk'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hostname' => 'Hostname',
            'ip' => 'Ip',
            'cpu_load' => 'Cpu Load',
            'mem_usage' => 'Mem Usage',
            'disk' => 'Disk',
            'disk_usage' => 'Disk Usage',
            'timestamp' => 'Timestamp',
            'disk_info' => 'Disk Info',
            'ps_info' => 'Ps Info',
        ];
    }
}

