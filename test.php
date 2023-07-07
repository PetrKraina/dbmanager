<?php

require_once 'DBManager/QueryProperties.php';
require_once 'DBManager/DBManagerInterface.php';
require_once 'DBManager/DBManager.php';

use Mavi\DBManager\DBManagerInterface;
use Mavi\DBManager\DBManager;
use Mavi\DBManager\QueryProperties;

$dbManager = new DBManager(
    'localhost',
    'testovaci_data',
    'root',
    'root'
);

// Selektování dat

$dbManager->enableCashing();

$result = $dbManager->table('uzivatele')
                    //->select('uzivatele.id, uzivatele.name, uzivatele.phone')
                    ->select(['uzivatele.id', 'uzivatele.name', 'uzivatele.phone'])
                    ->where('uzivatele.name LIKE ? AND uzivatele.id > ?', 'Ja%', 1) // Vyber uživatele, jehož jméno začíná na 'Ja' a jehož ID je větší než 1.
                    ->orderBy('uzivatele.name DESC, uzivatele.id ASC')
                    ->limit(2, 0)
                    ->leftJoin('objednavky')
                        ->on('uzivatele.id = objednavky.id_uzivatele')
                        ->select('cena, datum')
                        ->endJoin($dbManager)
                    ->fetchAll();

print_r($result);
echo '<hr>';

$result2 = $dbManager->table('objednavky')->select('*')->fetchAll();

print_r($result2);


// Vkládání dat
/*
$dbManager->beginTransaction();

try {
    $id = $dbManager->table('uzivatele')->insert(['name' => 'Karel', 'email' => 'petr@petr.cz'])
                    ->getId();
    
    $id = $dbManager->table('objednavky')->insert(['id_uzivatele' => $id, 'cena' => 1000, 'mena' => 'CZK', 'datum%sql' => 'NOW()'])->getId();
        
    $dbManager->commit();

} catch (\Exception $e) {
    $dbManager->rollback();
    echo 'Chyba při provedení dotazu: ' . $e->getMessage();
}

*/
//$dbManager->where('uzivatele.name LIKE', 'Ja%')->update(['phone%sql' => 'NOW()']);

?>