<?

/**
 * Storage Engine - Redis
 * @package default
 */

class Storage_Redis{
    private $host = "redis";
    private $port = "6379";
    private $db = 1;

    private $link = false;

    public function __construct($db = 15){
        $this->db = $db;
        $this->connect();
    }

    /**
     * Redis :: Create connection to database
     * @return type
     */
    public function connect() {
        $this->link = new Redis();
        $this->link->connect($this->host, $this->port, 0.2);

        if (!$this->link) {
            throw new Exception("Storage Engine is not available", 1);
        }

        $this->link->select($this->db);
    }

    /**
     * Redis :: Disconnect from db
     */
    public function disconnect() {
        $this->link->close();
    }
    
    /*
     *  Insert object's mirror to StorageEngine
     */
    public function insert($key,$value) {
        if ($result = $this->link->set($key,$value)) {
            return $result;
        }
        return false;
    }

    /*
     *  Replace object's mirror on StorageEngine
     */
    public function replace($key,$value) {
        return $this->update($key,$value);
    }

    /*
     *  Update object's mirror on StorageEngine
     */
    public function update($key,$value) {
        if($result = $this->link->set($key,$value)) {
            return $result;
        }
        return false;
    }

    /*
     *  Update object's mirror on StorageEngine
     */
    public function delete($key) {
        if($result = $this->link->del($key)) {
            return $result;
        }
        return false;
    }

    /*
     *  Select object's mirror(s) on StorageEngine
     */
    public function select($key) {
        return $this->link->get($key);
    }
}

?>
