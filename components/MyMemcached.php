<?php
namespace app\components;
 
use Yii;
use yii\base\Component;

class MyMemcached extends Component
{
	private $isEnable = false;
	private $myCache = NULL;

	function MyMemcached($host = "127.0.0.1", $port = 11211)
	{
		if (class_exists("Memcache"))
		{
			$this->myCache = new Memcache();
			$this->isEnable = true;
			/*if (!$this->myCache->connect($host,$port))
			{
				$this->myCache = NULL;
				$this->isEnable = false;
			}
			else 
			{
				$this->myCache->setCompressThreshold(20000, 0.2);
			}*/
			try{
				$this->myCache->connect($host,$port);
				//$this->myCache->setCompressThreshold(20000, 0.2);
			}catch(Exception $ex){
				$this->myCache = NULL;
				$this->isEnable = false;
			}
		}
	}
	
	public function increData($key, $val = 1)
	{
		if (!$this->isValidKey($key))
		{
			return false;
		}
		try{
			return $this->myCache->increment($key, $val);
		}
		catch(Exception $ex)
		{
			echo "Error: ".$ex->getCode().' '.$ex->getMessage();
		}
	}
	
	/**
	 * 
	 * Get data from cache server
	 * @param (string or array) $key
	 */
	public function getData($key)
	{
		if (!$this->isValidKey($key))
			return null;
		try {
			$data = $this->myCache->get($key);
			return $data === FALSE ? null : $data;
		}
		catch (Exception $ex)
		{
			echo "Error: ".$ex->getCode().' '.$ex->getMessage();
		}
	}
	
	/**
	 * 
	 * set data to server
	 * @param string $key
	 * @param mixed $data
	 * @param int $compress
	 * @param int $expire
	 */
	public function setData($key, $data, $compress = 0, $expire = 0)
	{
		if (!$this->isValidKey($key))
		{
			return false;
		}
		try{
			return $this->myCache->set($key, $data, $compress, $expire); 
		}
		catch(Exception $ex)
		{
			echo "Error: ".$ex->getCode().' '.$ex->getMessage();
		}
	}
	
	/**
	 * 
	 * Delete data from cache
	 * @param string $key
	 */
	public function deleteData($key)
	{
		if (!$this->isValidKey($key))
			return;
		return $this->myCache->delete($key);
	}
	
	//check key exist
	public function isExistKey($key)
	{
		if ($this->getData($key) == null)
			return false;
		else 
			return true;
	}
	
	private static function isValidKey($key)
	{
		if (empty($key))
			return false;
		return true;
	}
    
    /**
     * 
     * Delete all data is cached from server
     */
	public function deleteAllData()
    {
	    return $this->myCache->flush();
    }
}

