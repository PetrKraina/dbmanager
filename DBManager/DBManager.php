<?php

/*
 * Author: Petr Kraina
 * Copyright (c): Petr Kraina
 */

namespace Mavi\DBManager;

class DBManager implements DBManagerInterface
{

	/**
	 * Variable of self-instace.
	 * @var DBManager
	 */
	private static DBManager $singleton;

    /**
     * @param \PDO object.
     */
    private $database = null;

    /**
     * @param QueryProperties object.
     * Object beares parameters of query.
     * When the query is done, object is destroyed or saved to cache with result and created new one.
     */
    private $queryProperty = null;

    /**
     * @param int How many queries with results can be cached.
     */
    private $maxCachedQueries = 0;

    /**
     * @param array of QueryProperties objects.
     * Cashes last queries with results.
     */
    private $queriesCache = [];

	/**
     * @param string $host Host server address.
     * @param string $dbname Database name.
     * @param string $user DB user
     * @param string $password Password to DB.
     */
	public static function create(string $host='', string $dbname='', string $user='', string $password=''): DBManager {

		$this->singleton = new DBManager;
		$this->singleton->database = new \PDO('mysql:host=' . $host . ';dbname=' . $dbname, $user, $password);
		$this->singleton->queryProperty = new QueryProperties;
		return $this->singleton;
	}

    /**
     * Enables query and result cache by setting maximum number of cached results.
     * Default cached queries limit is 20.
     */
    public function enableCashing(int $maxCachedQueries = 20): void {
        $this->singleton->maxCachedQueries = $maxCachedQueries;
    }

    /**
     * @param string $table Name of database table.
     */
    public function table(string $table): DBManager {
        $this->singleton->queryProperty->setTable($table);
        return $this->singleton;
    }

    /**
     * @param string|array $select Table columns to select.
     *
     * When joining tables, define fith table names.
     * Example: users.id, users.name,...
     */
    public function select(string|array $select): DBManager {

        if(is_array($select)) {

            $selectString = '';

            foreach ($select as $column) {
                $selectString .= $column . ' , ';
            }
            $selectString = rtrim($selectString, ', ');

            $this->singleton->queryProperty->setSelect($selectString);

            return $this->singleton;
        }

        $this->singleton->queryProperty->setSelect($select);

        return $this->singleton;
    }

    /** @param string|array $where Specifies condition for selection.
     *  @param any $args Specifies values represented by questionmarks.
     * Example: id = ?
     * Example: [id = ?, name = ?] // Creates 'id = ? AND name = ?'
     * Example: 'id = ? AND name = ?'
     *
     * Usage example:
     * $db->where('id = ? AND name = ?', 1, 'Albert');
     */
    public function where(string|array $where): DBManager {

        $args = func_get_args();
        array_shift($args); // Remove first argument ($where).

        $this->singleton->queryProperty->setWhereArgs($args);

        if(is_array($where)) {

            $whereString = '';

            foreach ($where as $key => $cond) {
                $whereString .= $cond . 'AND ';
            }

            $whereString = rtrim($whereString, 'AND ');

            $this->singleton->queryProperty->setWhere($whereString);

            return $this->singleton;
        }

        $this->singleton->queryProperty->setWhere($where);

        return $this->singleton;
    }

    /**
     * @param string|array $orderBy
     * Example: name DESC, age ASC
     * Example: ['name DESC', 'age ASC']
     */
    public function orderBy(string|array $orderBy): DBManager {

        if(is_array($orderBy)) {

            $orderByString = '';

            foreach($orderBy as $ob) {
                $orderByString += $ob . ', ';
            }
            $orderByString = rtrim($orderByString, ', ');

            $this->singleton->queryProperty->setOrderBy($orderByString);

            return $this->singleton;
        }

        $this->singleton->queryProperty->setOrderBy($orderBy);

        return $this->singleton;
    }

    /**
     * @param int $limit
     * @param int $offset (optional)
     * Specifies limit and offset for selecting data rows.
     */
    public function limit(int $limit, int $offset = 0): DBManager {
        $this->singleton->queryProperty->setLimit($limit);
        $this->singleton->queryProperty->setOffset($offset);
        return $this->singleton;
    }

    /*
     * Fetching
     */

    /**
     * @param int $index Specifies index of column, that should be returned
     * Returns a single value of a row.
     */
    public function fetchSingle(int $index = 0): string|int {

        if($this->singleton->database === null) {
            throw new \Exception('Database connection has not been set.');
        }

        $this->singleton->queryProperty->setFetchType($this->singleton->queryProperty::FETCH_SINGLE);

        $cachedResult = $this->singleton->checkQueriesCache($this->singleton->queryProperty);
        if ($cachedResult !== null) {
            $this->singleton->cacheAndResetQueryProperties();
            return $cachedResult;
        }

        $task = $this->singleton->createTask();
        $result = $task->fetchColumn($index);
        $this->singleton->cacheAndResetQueryProperties($result);
        return $result;
    }

    /**
     * Returns single row as array.
     */
    public function fetch(): array {

        if($this->singleton->database === null) {
            throw new \Exception('Database connection has not been set.');
        }

        $this->singleton->queryProperty->setFetchType($this->singleton->queryProperty::FETCH);

        $cachedResult = $this->singleton->checkQueriesCache($this->singleton->queryProperty);
        if ($cachedResult !== null) {
            $this->singleton->cacheAndResetQueryProperties();
            return $cachedResult;
        }

        $task = $this->singleton->createTask();
        $result = $task->fetch(\PDO::FETCH_ASSOC);
        $this->singleton->cacheAndResetQueryProperties($result);
        return $result;
    }

    /**
     * Returns table with associative array of row values.
     * Example: [
     *      [id => 1, name => Bonifac],
     *      [id => 2, name => Michael]
     * ]
     */
    public function fetchAll(): array {

        if($this->singleton->database === null) {
            throw new \Exception('Database connection has not been set.');
        }

        $this->singleton->queryProperty->setFetchType($this->singleton->queryProperty::FETCH_ALL);

        $cachedResult = $this->singleton->checkQueriesCache($this->singleton->queryProperty);
        if ($cachedResult !== null) {
            $this->singleton->cacheAndResetQueryProperties();
            return $cachedResult;
        }

        $task = $this->singleton->createTask();
        $result = $task->fetchAll(\PDO::FETCH_ASSOC);
        $this->singleton->cacheAndResetQueryProperties($result);
        return $result;
    }

    /**
     * Returns table with rows associated to primary column value.
     * Example: [
     *      x8h2 => [id => x8h2, name => Bonifac],
     *      a1h2 => [id => a1h2, name => Michael]
     * ]
     */
    public function fetchPairs(): array {

        if($this->singleton->database === null) {
            throw new \Exception('Database connection has not been set.');
        }

        $this->singleton->queryProperty->setFetchType($this->singleton->queryProperty::FETCH_PAIRS);

        $cachedResult = $this->singleton->checkQueriesCache($this->singleton->queryProperty);
        if ($cachedResult !== null) {
            $this->singleton->cacheAndResetQueryProperties();
            return $cachedResult;
        }

        $task = $this->singleton->createTask();
        $result = $task->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_UNIQUE);
        $this->singleton->cacheAndResetQueryProperties($result);
        return $result;
    }

    /*
     * Joins
     */

    /**
     * @param string $on Specifies table columns for joining tables
     * Example: users.id = orders.user_id
     */
    public function on(string $on): DBManager {
        $this->singleton->queryProperty->setJoinOn($on);
        return $this->singleton;
    }

    /**
     * @param DBManager $dbmanager First DBManager object in composite structure.
     * So function ->fetch*(); can be used in a row and is applied to original object.
     */
    public function endJoin(DBManager $dbmanager): DBManager {
        return $dbmanager;
    }

    /**
     * @param string $table Specifies table to join with.
     * Performs SQL INNER JOIN.
     */
    public function innerJoin(string $table): DBManager {
        $composite = new DBManager();
        $this->singleton->queryProperty->setComposite($composite);
        $this->singleton->queryProperty->getComposite()->queryProperty->setJoin('INNER JOIN');
        $this->singleton->queryProperty->getComposite()->queryProperty->setTable($table);
        return $this->singleton->queryProperty->getComposite();
    }

    /**
     * @param string $table Specifies table to join with.
     * Performs SQL LEFT OUTER JOIN.
     */
    public function leftJoin(string $table): DBManager {
        $composite = new DBManager();
        $this->singleton->queryProperty->setComposite($composite);
        $this->singleton->queryProperty->getComposite()->queryProperty->setJoin('LEFT JOIN');
        $this->singleton->queryProperty->getComposite()->queryProperty->setTable($table);
        return $this->singleton->queryProperty->getComposite();
    }

    /**
     * @param string $table Specifies table to join with.
     * Performs SQL RIGHT OUTER JOIN.
     */
    public function rightJoin(string $table): DBManager {
        $composite = new DBManager();
        $this->singleton->queryProperty->setComposite($composite);
        $this->singleton->queryProperty->getComposite()->queryProperty->setJoin('RIGHT JOIN');
        $this->singleton->queryProperty->getComposite()->queryProperty->setTable($table);
        return $this->singleton->queryProperty->getComposite();
    }

    /**
     * @param string $table Specifies table to join with.
     * Performs SQL LFULL OUTER JOIN.
     */
    public function fullJoin(string $table): DBManager {
        $composite = new DBManager();
        $this->singleton->queryProperty->setComposite($composite);
        $this->singleton->queryProperty->getComposite()->queryProperty->setJoin('FULL JOIN');
        $this->singleton->queryProperty->getComposite()->queryProperty->setTable($table);
        return $this->singleton->queryProperty->getComposite();
    }

    /*
     * Creating data
     */

    /**
     * @param array $data
     * Example: $data = ['name' => 'Albert', 'age' = '16']
     */
    public function insert(array $data): DBManager {

        $cols = '';
        $valuesMask = '';
        $bindedValues = [];

        foreach ($data as $column => $value) {

            if(str_ends_with($column, '%sql')) {

                $columnName = str_replace('%sql', '', $column);

                $cols .= $columnName . ', ';
                $valuesMask .= $value;

                continue;
            }

            $cols .= $column . ', ';
            $valuesMask .= ':' . $column . ', ';
            $bindedValues[':' . $column] = $value;
        }

        $cols = rtrim($cols, ', ');
        $valuesMask = rtrim($valuesMask, ', ');

        $task = $this->singleton->database->prepare('INSERT INTO ' . $this->singleton->queryProperty->getTable() . ' (' . $cols . ') VALUES(' . $valuesMask . ')');

        $task->execute($bindedValues);

        return $this->singleton;
    }

    /**
     * Returns ID of last added row.
     */
    public function getId(): int {
        return $this->singleton->database->lastInsertId();
    }

    /*
     * Updating
     */

    /**
     * @param array $data
     * Example: $data = ['name' => 'Albert', 'age' = '16', date%sql => 'NOW()']
     */
    public function update(array $data): bool {

        if ($this->singleton->queryProperty->getTable() === null) {
            throw new \Exception('Specify table condition $db->table(...) before updating.');
        }

        if ($this->singleton->queryProperty->getWhere() === null) {
            throw new \Exception('Specify condition for selecting $db->table(...)->where(...) before updating.');
        }

        $mask = '';
        $bindedValues = [];

        foreach ($data as $column => $value) {

            if(str_ends_with($column, '%sql')) {
                $columnName = str_replace('%sql', '', $column);
                $mask .= $columnName . '=' . $value . ', ';
                continue;
            }

            $mask .= $column . '=:' . $column . ', ';
            $bindedValues[':' . $column] = $value;
        }

        $mask = rtrim($mask, ', ');

        $whereMask = '';
        $whereConditions = explode('?', $this->singleton->queryProperty->getWhere());

        foreach ($this->singleton->queryProperty->getWhereArgs() as $key => $arg) {
            $whereMask .= $whereConditions[$key] . ' :' . $key .' ';
            $bindedValues[':' . $key] = $arg;
        }

        $task = $this->singleton->database->prepare('UPDATE ' . $this->singleton->queryProperty->getTable() . ' SET ' . $mask . ' WHERE ' . $whereMask);

        return $task->execute($bindedValues);
    }

    /*
     * Transactions
     */

    public function beginTransaction(): void {
        $this->singleton->database->beginTransaction();
    }

    public function commit(): void {
        $this->singleton->database->commit();
    }

    public function rollback(): void {
        $this->singleton->database->rollback();
    }

    /*
     * Private functions
     */

    /**
     * Creates SQL/PDO task to execute nad runs it.
     */
    private function createTask() {
        $query = $this->singleton->createQuery();
        $task = $this->singleton->database->prepare($query);
        $task->execute($this->singleton->queryProperty->getWhereArgs());
        return $task;
    }

    /**
     * Builds SQL query.
     */
    private function createQuery() {

        if ($this->singleton->queryProperty->getTable() === null) {
            throw new \Exception('Specify table condition $db->table(...).');
        }

        if ($this->singleton->queryProperty->getSelect() === null) {
            throw new \Exception('Specify which data you want to select $db->table(...)->select(...).');
        }

        $select = 'SELECT ';
        $from = '';
        $join = '';
        $where = '';
        $order = '';
        $limit = '';
        $offset = '';

        $select .= $this->singleton->queryProperty->getSelect();

        $from .= ' FROM ' . $this->singleton->queryProperty->getTable() . ' ';

        if($this->singleton->queryProperty->getComposite() instanceof DBManager) {
            $queryParts = $this->singleton->createJoinQueriesPart($this->singleton->queryProperty->getComposite());
            $join .= $queryParts['join'];

            if (empty($select) === false) {
                $select .= ', ';
            }

            $select .= $queryParts['select'];

        }

        if ($this->singleton->queryProperty->getWhere() !== null) {
            $where .= 'WHERE ' . $this->singleton->queryProperty->getWhere() . ' ';
        }

        if ($this->singleton->queryProperty->getOrderBy() !== null) {
            $order .= 'ORDER BY ' . $this->singleton->queryProperty->getOrderBy() . ' ';
        }

        if ($this->singleton->queryProperty->getLimit() !== null) {
            $limit .= 'LIMIT ' . $this->singleton->queryProperty->getLimit() . ' ';
        }

        if ($this->singleton->queryProperty->getOffset() !== null) {
            $offset .= 'OFFSET ' . $this->singleton->queryProperty->getOffset();
        }

        $query = $select . $from . $join . $where . $order . $limit . $offset;

        return $query;
    }

    /**
     * Creates SQL query part for JOIN.
     */
    private function createJoinQueriesPart($composite) {

        $join = '';
        $select = '';

        $join .= $composite->queryProperty->getJoin() . ' ';
        $join .= $composite->queryProperty->getTable() . ' ';
        $join .= 'ON ' . $composite->queryProperty->getJoinOn() . ' ';

        $select .= $composite->queryProperty->getSelect();

        if($composite->queryProperty->getComposite() instanceof DBManager) {
            $queryPart .= $composite->createJoinQueriesPart($composite->queryProperty->getComposite());
        }

        return ['join' => $join, 'select' => $select];
    }

    /**
     * Cehck, if previous queries was the same and return previous result.
     */
    private function checkQueriesCache(QueryProperties $queryProperties): array|null {

        $hash = $queryProperties->getHash();

        foreach ($this->singleton->queriesCache as $cachedQuery) {

            if ($hash === $cachedQuery->getHash()) {
                return $cachedQuery->getResult();
            }
        }

        return null;
    }

    /**
     * Variables reset or cache and reset.
     * Mainly used for reset after query is finished.
     */
    private function cacheAndResetQueryProperties(array|null $result = null): void {

        if ($result !== null && $this->singleton->maxCachedQueries > 0) {

            if (count($this->singleton->queriesCache) === $this->singleton->maxCachedQueries) {
                array_shift($this->singleton->queriesCache);
            }

            $this->singleton->queryProperty->setResult($result);
            $this->singleton->queryProperty->getHash(); // Generates query hash, so the cashed query can not be changed

            $this->singleton->queriesCache[] = $this->singleton->queryProperty;
        }

        $this->singleton->queryProperty = new QueryProperties;
    }

}

