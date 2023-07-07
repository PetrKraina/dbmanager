<?php

/*
 * Author: Petr Kraina
 * Copyright (c): Petr Kraina
 */

namespace Mavi\DBManager;

interface DBManagerInterface {

    /*
     * DBManager settings
     */

    public function enableCashing(int $maxCachedQueries = 20): void;

    /*
     * Specifying data selection
     */

    public function table(string $table): DBManager;
    public function select(string|array $select): DBManager;
    public function where(string|array $where): DBManager;
    public function orderBy(string|array $orderBy): DBManager;
    public function limit(int $limit, int $offset): DBManager;

    /*
     * Fetching
     */

    public function fetchSingle(): string|int;
    public function fetch(): array;
    public function fetchAll(): array;
    public function fetchPairs(): array;

    /*
     * Joins
     */

    public function innerJoin(string $table): DBManager;
    public function leftJoin(string $table): DBManager;
    public function rightJoin(string $table): DBManager;
    public function fullJoin(string $table): DBManager;
    public function on(string $on): DBManager;
    public function endJoin(DBManager $dbmanager): DBManager;

    /*
     * Transactions
     */

    public function beginTransaction(): void;
    public function commit(): void;
    public function rollback(): void;

    /*
     * Creating data
     */

     public function insert(array $data): DBManager;
     public function getId(): int;

    /*
     * Updating
     */

     public function update(array $data): bool;
}