# DBManager
Manager for repository tables.<br />
Manager is using PDO (PDO is enabled in PHP by default).<br />
Using PHP 8.1.<br />

## Init
```
$dbManager = new DBManager('host', 'db', 'user', 'password');
```
## Selecting data
```
$result = $dbManager->table('users')
                    ->select('users.id, users.name, users.phone')
              // or ->select(['users.id', 'users.name', 'users.phone'])
                    ->where('users.name LIKE ? AND users.id > ?', 'Ja%', 1)
              // or ->where(['users.name LIKE ?', 'users.id > ?'], 'Ja%', 1)
                    ->orderBy('users.name DESC, users.id ASC')
              // or ->orderBy(['users.name DESC', 'users.id ASC'])
                    ->limit(2, 0)
                    ->rightJoin('orders')
                        ->on('users.id = orders.user_id')
                        ->select('price, date')
                        ->endJoin($dbManager)
                    ->fetchAll();
```
### Joining tables

_Tables can be joined composittely:_ <br />
```
->innerJoin('orders')
    ->on('user.id = orders.id_uzivatele')
    ->select('orders.products, orders.date')
    ->innerJoin('products')
        ->on('order.product_id = product.id')
        ->select('price, vat')
->endJoin($dbManager)
```
**Join Funcions:** <br />
```
->innerJoin(...)
->leftJoin(...)
->rightJoin(...)
->fullJoin(...)
```

## Fetching data <br />
```
->fetchSingle(); // Returns single value

$result = $dbManager->table('users')->select('id')->where(...)->fetchSingle(); // Returns 'id'
$result = $dbManager->table('users')->select('id, name, phone')->where(...)->fetchSingle(); // Returns 'id'
$result = $dbManager->table('users')->select('id, name, phone')->where(...)->fetchSingle(1); // Returns 'name'
$result = $dbManager->table('users')->select('id, name, phone')->where(...)->fetchSingle(2); // Returns 'phone'

->fetch(); // Returns single row as array
->fetchAll(); // Returns array of rows with values as associative array
->fetchPairs(); // Returns rows associated with unique key, values in a rows are associative array
    /*
     * fetchPairs example:
     * [
     *   2 => [id => 2, name => ... ]
     *   5 => [id => 5, name => ...]
     * ]
     */
```

## Cashing results <br />

Results can be cached. If the same queries are executed, DBManager will load cached results.<br />
```
$dbManager->enableCashing($numberOfMaxCachedResults);<br />
```
- _Default maximum of cached results is 20._<br />
- _If the limit is exausted, first cached result is removed and results shifted._<br />
- _Cashing is effective for tables with many records._<br />

## Inserting data
```
$dbManager->beginTransaction();

try {
   $id = $dbManager->table('users')->insert(['name' => 'Karl', 'email' => 'karl@example.com'])->getId();
   $dbManager->table('orders')->insert(['user_id' => $id, 'price' => 1000, 'currency' => 'CZK', 'date%sql' => 'NOW()']);
   $dbManager->commit();

} catch (\Exception $e) {
   $dbManager->rollback();
}
```

## Updating data

Updates phone for user which his name begins with Kar* and date of birth is 3.2.1991.<br />
```
$dbManager->table('users')->where('name LIKE ? AND birth_date = ?', 'Kar%', '1991-02-03')->update(['phone' => '+420 555 555 555']);
```
- 'Kar%' - string that begins with 'Kar'.
- '%arl' - string that ends with 'arl'.
- '%ar%' - string that contains 'ar'.
