<?php

/*
 * Author: Petr Kraina
 * Copyright (c): Petr Kraina
 */

namespace Mavi\DBManager;

class QueryProperties
{
    /** Type of fetching data for building a hash. */
    public const FETCH_SINGLE = 'fetch_single';

    /** Type of fetching data for building a hash. */
    public const FETCH = 'fetch';

    /** Type of fetching data for building a hash. */
    public const FETCH_ALL = 'fetch_all';

    /** Type of fetching data for building a hash. */
    public const FETCH_PAIRS = 'fetch_pairs';

    /** 
     * @param string Table to work with. 
     */
    private $table = null;

    /** 
     * @param string Columns to select. Example: ['id', 'name'].
     */
    private $select = null;

    /** 
     * @param string Columns to search for. 
     * Example: 'id = ? AND name = ?' 
     */
    private $where = null;

    /** 
     * @param array Values to search for in columns $where. 
     */
    private $whereArgs = null;

    /** 
     * @param string Specifies column/s and sorting direction/s. 
     */
    private $orderBy = null;

    /** 
     * @param int Specifies maximum number of results. 
     */
    private $limit = null;

    /** @param int Specifies starting number for selecting. 
     * Example: $offest = 3 means, that program will start selecting data from row 3. 
     */    
    private $offset = null;

    /**
     * @param string Beares SQL string for join tables.
     * Examples: INNER JOIN, LEFT JOIN, RIGHT JOIN, FULL JOIN
     */
    private $join = null;

    /**
     * @param string Specifies table columns for joining tables
     * Example: users.id = orders.user_id
     */
    private $joinOn = null;

    /** 
     * @param DBManager Object for composite structure of joining tables. 
     */
    private $composite = null;

    /** 
     * @param string Type of fetching data. 
     * Defined by FETCH constants above.
     */
    private $fetchType = null;

    /**
     * @param string Hash to compare with other query, if the set of properties are the same and cashed result can be reurned without tasking DB again.
     */
    private $hash = null;

    /**
     * @param string|array Result of DB query task.
     */
    private $result = null;

    public function getHash(): string {

        if ($this->hash === null) {
            $this->hash = hash('SHA256', $this->table . $this->select . $this->where . json_encode($this->whereArgs) . $this->orderBy . $this->limit . $this->offset . $this->join . $this->joinOn . json_encode($this->composite) . $this->fetchType);
        }

        return $this->hash;
    }

    public function getTable(): string|null {
        return $this->table;
    }

    public function getSelect(): string|null {
        return $this->select;
    }

    public function getWhere(): string|null {
        return $this->where;
    }

    public function getWhereArgs(): array|null {
        return $this->whereArgs;
    }

    public function getOrderBy(): string|null {
        return $this->orderBy;
    }

    public function getLimit(): int|null {
        return $this->limit;
    }

    public function getOffset(): int|null {
        return $this->offset;
    }

    public function getJoin(): string|null {
        return $this->join;
    }

    public function getJoinOn(): string|null {
        return $this->joinOn;
    }

    public function getComposite(): DBManager|null {
        return $this->composite;
    }

    public function getFetchType(): string|null {
        return $this->fetchType;
    }

    public function getResult(): array|null {
        return $this->result;
    }

    public function setTable(string $table): void {

        if ($this->table !== null && $this->hash !== null) {
            throw new \Exception('Param $table has already been set or $hash has already been created.');
        }

        $this->table = $table;
    }

    public function setSelect(string $select): void {

        if ($this->select !== null && $this->hash !== null) {
            throw new \Exception('Param $select has already been set or $hash has already been created.');
        }
        
        $this->select = $select;
    }

    public function setWhere(string $where): void {

        if ($this->where !== null && $this->hash !== null) {
            throw new \Exception('Param $where has already been set or $hash has already been created.');
        }
        
        $this->where = $where;
    }

    public function setWhereArgs(array $whereArgs): void {

        if ($this->whereArgs !== null && $this->hash !== null) {
            throw new \Exception('Param $whereArgs has already been set or $hash has already been created.');
        }
        
        $this->whereArgs = $whereArgs;
    }

    public function setOrderBy(string $orderBy): void {

        if ($this->orderBy !== null && $this->hash !== null) {
            throw new \Exception('Param $orderBy has already been set or $hash has already been created.');
        }

        $this->orderBy = $orderBy;
    }

    public function setLimit(int $limit): void {

        if ($this->limit !== null && $this->hash !== null) {
            throw new \Exception('Param $limit has already been set or $hash has already been created.');
        }

        $this->limit = $limit;
    }

    public function setOffset(int $offset): void {

        if ($this->offset !== null && $this->hash !== null) {
            throw new \Exception('Param $offset has already been set or $hash has already been created.');
        }

        $this->offset = $offset;
    }

    public function setJoin(string $join): void {

        if ($this->join !== null && $this->hash !== null) {
            throw new \Exception('Param $join has already been set or $hash has already been created.');
        }

        $this->join = $join;
    }

    public function setJoinOn(string $joinOn): void {

        if ($this->joinOn !== null && $this->hash !== null) {
            throw new \Exception('Param $joinOn has already been set or $hash has already been created.');
        }

        $this->joinOn = $joinOn;
    }

    public function setComposite(DBManager $composite): void {

        if ($this->composite !== null && $this->hash !== null) {
            throw new \Exception('Param $composite has already been set or $hash has already been created.');
        }

        $this->composite = $composite;
    }

    public function setFetchType(string $fetchType): void {

        if ($this->fetchType !== null && $this->hash !== null) {
            throw new \Exception('Param $fetchType has already been set or $hash has already been created.');
        }

        $this->fetchTypee = $fetchType;
    }

    public function setResult(array $result): void {
        
        if($this->result !== null) {
            throw new \Exception('Result ($result) has already been set.');
        }

        $this->result = $result;
    }

 }