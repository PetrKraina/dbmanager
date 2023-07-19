<?php

/*
 * Author: Petr Kraina
 * Copyright (c): Petr Kraina
 */

namespace Mavi\DBManager;

class DBManager implements DBManagerInterface
{
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
    public function __construct(string $host='', string $dbname='', string $user='', string $password='')
    {
        // In case of composite structure are those values empty and connection is not needed.
        if(($host === '' || $dbname === '' || $user === '') === false) {
            $this->database = new \PDO('mysql:host=' . $host . ';dbname=' . $dbname, $user, $password);
        }

        $this->queryProperty = new QueryProperties;
    }

    /**
     * Enables query and result cache by setting maximum number of cached results.
     * Default cached queries limit is 20.
     */
    public function enableCashing(int $maxCachedQueries = 20): void {
        $this->maxCachedQueries = $maxCachedQueries;
    }

    /**
     * @param string $table Name of database table.
     */
    public function table(string $table): DBManager {
        $this->queryProperty->setTable($table);
        return $this;
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

            $this->queryProperty->setSelect($selectString);

            return $this;
        }

        $this->queryProperty->setSelect($select);

        return $this;
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

        $this->queryProperty->setWhereArgs($args);

        if(is_array($where)) {

            $whereString = '';

            foreach ($where as $key => $cond) {
                $whereString .= $cond . 'AND ';
            }

            $whereString = rtrim($whereString, 'AND ');

            $this->queryProperty->setWhere($whereString);

            return $this;
        }

        $this->queryProperty->setWhere($where);

        return $this;
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

            $this->queryProperty->setOrderBy($orderByString);

            return $this;
        }

        $this->queryProperty->setOrderBy($orderBy);

        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset (optional)
     * Specifies limit and offset for selecting data rows.
     */
    public function limit(int $limit, int $offset = 0): DBManager {
        $this->queryProperty->setLimit($limit);
        $this->queryProperty->setOffset($offset);
        return $this;
    }

    /*
     * Fetching
     */

    /**
     * @param int $index Specifies index of column, that should be returned
     * Returns a single value of a row.
     */
    public function fetchSingle(int $index = 0): string|int {

        if($this->database === null) {
            throw new \Exception('Database connection has not been set.');
        }

        $this->queryProperty->setFetchType($this->queryProperty::FETCH_SINGLE);

        $cachedResult = $this->checkQueriesCache($this->queryProperty);
        if ($cachedResult !== null) {
            $this->cacheAndResetQueryProperties();
            return $cachedResult;
        }

        $task = $this->createTask();
        $result = $task->fetchColumn($index);
        $this->cacheAndResetQueryProperties($result);
        return $result;
    }

    /**
     * Returns single row as array.
     */
    public function fetch(): array {

        if($this->database === null) {
            throw new \Exception('Database connection has not been set.');
        }

        $this->queryProperty->setFetchType($this->queryProperty::FETCH);

        $cachedResult = $this->checkQueriesCache($this->queryProperty);
        if ($cachedResult !== null) {
            $this->cacheAndResetQueryProperties();
            return $cachedResult;
        }

        $task = $this->createTask();
        $result = $task->fetch(\PDO::FETCH_ASSOC);
        $this->cacheAndResetQueryProperties($result);
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

        if($this->database === null) {
            throw new \Exception('Database connection has not been set.');
        }

        $this->queryProperty->setFetchType($this->queryProperty::FETCH_ALL);

        $cachedResult = $this->checkQueriesCache($this->queryProperty);
        if ($cachedResult !== null) {
            $this->cacheAndResetQueryProperties();
            return $cachedResult;
        }

        $task = $this->createTask();
        $result = $task->fetchAll(\PDO::FETCH_ASSOC);
        $this->cacheAndResetQueryProperties($result);
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

        if($this->database === null) {
            throw new \Exception('Database connection has not been set.');
        }

        $this->queryProperty->setFetchType($this->queryProperty::FETCH_PAIRS);

        $cachedResult = $this->checkQueriesCache($this->queryProperty);
        if ($cachedResult !== null) {
            $this->cacheAndResetQueryProperties();
            return $cachedResult;
        }

        $task = $this->createTask();
        $result = $task->fetchAll(\PDO::FETCH_ASSOC | \PDO::FETCH_UNIQUE);
        $this->cacheAndResetQueryProperties($result);
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
        $this->queryProperty->setJoinOn($on);
        return $this;
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
        $this->queryProperty->setComposite($composite);
        $this->queryProperty->getComposite()->queryProperty->setJoin('INNER JOIN');
        $this->queryProperty->getComposite()->queryProperty->setTable($table);
        return $this->queryProperty->getComposite();
    }

    /**
     * @param string $table Specifies table to join with.
     * Performs SQL LEFT OUTER JOIN.
     */
    public function leftJoin(string $table): DBManager {
        $composite = new DBManager();
        $this->queryProperty->setComposite($composite);
        $this->queryProperty->getComposite()->queryProperty->setJoin('LEFT JOIN');
        $this->queryProperty->getComposite()->queryProperty->setTable($table);
        return $this->queryProperty->getComposite();
    }

    /**
     * @param string $table Specifies table to join with.
     * Performs SQL RIGHT OUTER JOIN.
     */
    public function rightJoin(string $table): DBManager {
        $composite = new DBManager();
        $this->queryProperty->setComposite($composite);
        $this->queryProperty->getComposite()->queryProperty->setJoin('RIGHT JOIN');
        $this->queryProperty->getComposite()->queryProperty->setTable($table);
        return $this->queryProperty->getComposite();
    }

    /**
     * @param string $table Specifies table to join with.
     * Performs SQL LFULL OUTER JOIN.
     */
    public function fullJoin(string $table): DBManager {
        $composite = new DBManager();
        $this->queryProperty->setComposite($composite);
        $this->queryProperty->getComposite()->queryProperty->setJoin('FULL JOIN');
        $this->queryProperty->getComposite()->queryProperty->setTable($table);
        return $this->queryProperty->getComposite();
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

        $task = $this->database->prepare('INSERT INTO ' . $this->queryProperty->getTable() . ' (' . $cols . ') VALUES(' . $valuesMask . ')');

        $task->execute($bindedValues);

        return $this;
    }

    /**
     * Returns ID of last added row.
     */
    public function getId(): int {
        return $this->database->lastInsertId();
    }

    /*
     * Updating
     */

    /**
     * @param array $data
     * Example: $data = ['name' => 'Albert', 'age' = '16', date%sql => 'NOW()']
     */
    public function update(array $data): bool {

        if ($this->queryProperty->getTable() === null) {
            throw new \Exception('Specify table condition $db->table(...) before updating.');
        }

        if ($this->queryProperty->getWhere() === null) {
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
        $whereConditions = explode('?', $this->queryProperty->getWhere());

        foreach ($this->queryProperty->getWhereArgs() as $key => $arg) {
            $whereMask .= $whereConditions[$key] . ' :' . $key .' ';
            $bindedValues[':' . $key] = $arg;
        }

        $task = $this->database->prepare('UPDATE ' . $this->queryProperty->getTable() . ' SET ' . $mask . ' WHERE ' . $whereMask);

        return $task->execute($bindedValues);
    }

    /*
     * Transactions
     */

    public function beginTransaction(): void {
        $this->database->beginTransaction();
    }

    public function commit(): void {
        $this->database->commit();
    }

    public function rollback(): void {
        $this->database->rollback();
    }

    /*
     * Private functions
     */

    /**
     * Creates SQL/PDO task to execute nad runs it.
     */
    private function createTask() {
        $query = $this->createQuery();

		echo $query;

        $task = $this->database->prepare($query);
        $task->execute($this->queryProperty->getWhereArgs());
        return $task;
    }

    /**
     * Builds SQL query.
     */
    private function createQuery() {

        if ($this->queryProperty->getTable() === null) {
            throw new \Exception('Specify table condition $db->table(...).');
        }

        if ($this->queryProperty->getSelect() === null) {
            throw new \Exception('Specify which data you want to select $db->table(...)->select(...).');
        }

        $select = 'SELECT ';
        $from = '';
        $join = '';
        $where = '';
        $order = '';
        $limit = '';
        $offset = '';

        $select .= $this->queryProperty->getSelect();

        $from .= ' FROM ' . $this->queryProperty->getTable() . ' ';

        if($this->queryProperty->getComposite() instanceof DBManager) {
            $queryParts = $this->createJoinQueriesPart($this->queryProperty->getComposite());

			print_r($queryParts);

            $join .= $queryParts['join'];

            if (empty($select) === false) {
                $select .= ', ';
            }

            $select .= $queryParts['select'];

        }

        if ($this->queryProperty->getWhere() !== null) {
            $where .= 'WHERE ' . $this->queryProperty->getWhere() . ' ';
        }

        if ($this->queryProperty->getOrderBy() !== null) {
            $order .= 'ORDER BY ' . $this->queryProperty->getOrderBy() . ' ';
        }

        if ($this->queryProperty->getLimit() !== null) {
            $limit .= 'LIMIT ' . $this->queryProperty->getLimit() . ' ';
        }

        if ($this->queryProperty->getOffset() !== null) {
            $offset .= 'OFFSET ' . $this->queryProperty->getOffset();
        }

        $query = $select . $from . $join . $where . $order . $limit . $offset;

        return $query;
    }

    /**
     * Creates SQL query part for JOIN.
     */
    private function createJoinQueriesPart($composite): array {

        $join = '';
        $select = '';

        $join .= $composite->queryProperty->getJoin() . ' ';
        $join .= $composite->queryProperty->getTable() . ' ';
        $join .= 'ON ' . $composite->queryProperty->getJoinOn() . ' ';

        $select .= $composite->queryProperty->getSelect();

        if($composite->queryProperty->getComposite() instanceof DBManager) {
            $queryPart = $composite->createJoinQueriesPart($composite->queryProperty->getComposite());
			$join .= ' ' . $queryPart['join'];
			$select .= ', ' . $queryPart['select'];
        }

        return ['join' => $join, 'select' => $select];
    }

    /**
     * Cehck, if previous queries was the same and return previous result.
     */
    private function checkQueriesCache(QueryProperties $queryProperties): array|null {

        $hash = $queryProperties->getHash();

        foreach ($this->queriesCache as $cachedQuery) {

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

        if ($result !== null && $this->maxCachedQueries > 0) {

            if (count($this->queriesCache) === $this->maxCachedQueries) {
                array_shift($this->queriesCache);
            }

            $this->queryProperty->setResult($result);
            $this->queryProperty->getHash(); // Generates query hash, so the cashed query can not be changed

            $this->queriesCache[] = $this->queryProperty;
        }

        $this->queryProperty = new QueryProperties;
    }

}

