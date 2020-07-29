<?php

require_once 'Db.php';

abstract class ActiveRecordEntity
{
    
    protected $id;
    public static $tableName;
    
    public function getId(): int
    {
        return $this->id;
    }
    protected function belongsTo($class,$thisColumn='',$thatColumn='',$table=''){
        $db = Db::getInstance();
        $thisColumn = $thatColumn?$thatColumn:$this->underscoreToCamelCase(strtolower($class).'_id');
        $thatColumn = $thatColumn?$this->underscoreToCamelCase($thisColumn):'id';
        $table = $table?$table:$class::getTableName();

        $entities = $db->query(
            "SELECT * FROM " . $table . " WHERE $thatColumn=:$thatColumn;",
            [':'.$thatColumn => $this->$thisColumn],
            $class
        );
        return $entities ? $entities[0] : null;
    }
    protected function hasOne($class,$thatColumn='',$thisColumn='',$table=''){
        $db = Db::getInstance();
        $table = $table?$table:$class::getTableName();
        $thisColumn = $thisColumn?$this->underscoreToCamelCase($thisColumn):'id';
        $thatColumn = $thatColumn?$thatColumn:strtolower(static::class).'_id';
        $entities = $db->query(
            "SELECT * FROM " . $table . " WHERE $thatColumn=:$thatColumn;",
            [':'.$thatColumn => $this->$thisColumn],
            $class
        );
        return $entities ? $entities[0] : null;
    }
    protected function hasMany($class,$thatColumn='',$thisColumn='',$table=''){
        $db = Db::getInstance();
        $table = $table?$table:$class::getTableName();
        $thisColumn = $thisColumn?$this->underscoreToCamelCase($thisColumn):'id';
        $thatColumn = $thatColumn?$thatColumn:strtolower(static::class).'_id';
        $entities = $db->query(
            "SELECT * FROM " . $table . " WHERE $thatColumn=:$thatColumn;",
            [':'.$thatColumn => $this->$thisColumn],
            $class
        );
        return $entities ? $entities : null;
    }
    protected function belongsToMany($class,$table='',$firstJoinColumn='',$secondJoinColumn='',$comparisonColumn=''){
        $db = Db::getInstance();
        $joinTable = $class::getTableName();
        if($table==''){
            if(substr($class,0,1)<substr(get_class($this),0,1)){
                $table=strtolower($class.'_'.get_class($this));
            } else  $table = strtolower(get_class($this).'_'.$class);
        }
        $firstJoinColumn = $firstJoinColumn?$firstJoinColumn:'id';
        $secondJoinColumn = $secondJoinColumn?$secondJoinColumn:strtolower($class).'_id';
        $comparisonColumn = $comparisonColumn?$comparisonColumn:strtolower(get_class($this)).'_id';

        $entities = $db->query(
            "SELECT * FROM  $joinTable as j JOIN $table as i ON j.$firstJoinColumn = i.$secondJoinColumn WHERE i.$comparisonColumn=:id;",
            [':id'=>$this->id],
            $class
        );
        return $entities ? $entities : null;
    }

    public function getById(int $id): ?self
    {
        $db = Db::getInstance();

        $entities = $db->query(
            'SELECT * FROM `' . static::getTableName() . '` WHERE id=:id;',
            [':id' => $id],
            static::class
        );
        return $entities ? $entities[0] : null;
    }

    public function delete(int $id) : void
    {
        $db = Db::getInstance();
        $db->query(
            'DELETE FROM `' . static::getTableName() . '` WHERE id=:id;',
            [':id' => $id]
        );
    }

    public static function setTableName($tableName){
        static::$tableName = $tableName;
    }
    public function getByColumnName($columnName,$value)
    {
        $db = Db::getInstance();
        $entities = $db->query(
            'SELECT * FROM `' . static::getTableName() . "` WHERE $columnName=:$columnName;",
            [":$columnName" => $value],
            static::class );
        return $entities ? $entities[0] : null;
    }

    private function camelCaseToUnderscore(string $source): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $source));
    }

    private function mapPropertiesToDbFormat(): array
    {
        $reflector = new \ReflectionObject($this);
        $properties = $reflector->getProperties();

        $mappedProperties = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertyNameAsUnderscore = $this->camelCaseToUnderscore($propertyName);
            $mappedProperties[$propertyNameAsUnderscore] = $this->$propertyName;
        }

        return $mappedProperties;
    }

    public function save(){
        $mappedProperties = $this->mapPropertiesToDbFormat();
            stdOut($this);
        if($this->id!==null){
            $this->update($mappedProperties);
        } else $this->insert($mappedProperties);

    }

    private function update($properties){
        $columns2params = [];
        $params2values = [];
        $index = 1;
        foreach ($properties as $column => $value) {
            $param = ':param' . $index; 
            $columns2params[] = $column . ' = ' . $param;
            $params2values[$param] = $value; 
            $index++;
        }
        $sql = 'UPDATE ' . static::getTableName() . ' SET ' . implode(', ', $columns2params) . ' WHERE id = ' . $this->id;
        $db = Db::getInstance();
        $db->query($sql, $params2values, static::class);
    }

    private function insert(array $mappedProperties): void
    {
        $filteredProperties = array_filter($mappedProperties);

        $columns = [];
        $paramsNames = [];
        $params2values = [];
        foreach ($filteredProperties as $columnName => $value) {
            $columns[] = '`' . $columnName. '`';
            $paramName = ':' . $columnName;
            $paramsNames[] = $paramName;
            $params2values[$paramName] = $value;
        }

        $columnsViaSemicolon = implode(', ', $columns);
        $paramsNamesViaSemicolon = implode(', ', $paramsNames);

        $sql = 'INSERT INTO ' . static::getTableName() . ' (' . $columnsViaSemicolon . ') VALUES (' . $paramsNamesViaSemicolon . ');';

        $db = Db::getInstance();
        $db->query($sql, $params2values, static::class);
        $this->id = $db->getLastInsertId();
    }



    public function __set(string $name, $value)
    {
        $camelCaseName = $this->underscoreToCamelCase($name);
        $this->$camelCaseName = $value;
    }

    private function underscoreToCamelCase(string $source): string
    {
        return lcfirst(str_replace('_', '', ucwords($source, '_')));
    }

   
    public static function findAll(): array
    {
        $db = Db::getInstance();
        return $db->query('SELECT * FROM `' . static::getTableName() . '`;', [], static::class);
    }

    public static function qwe(){
        return self::getTableName();
    }
    protected static function getTableName()
    {
        $tableName = strtolower(static::class);
        if(static::$tableName!='') return static::$tableName;
        if(substr($tableName, -1)!='s')
        {
            if(substr($tableName, -1)=='y')
            {
                return substr($tableName,0,-1).'ies';
            }
            return $tableName.'s';
        }
    }
}