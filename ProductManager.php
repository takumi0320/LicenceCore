<?php
require_once (dirname(__FILE__) . "/../InformationClass/Product.php");
require_once (dirname(__FILE__) . "/../ManagerClass/DatabaseManager.php");
class ProductManager {

    //システム登録
    public function RegisterProduct($ProductName,$ProductKana){
        $DatabaseManager = new DatabaseManager();
        $Product = new Product();
        $Product->ProductName = $ProductName;
        $Product->ProductKana = $ProductKana;
        $DatabaseManager->InsertProduct($Product);
    }
    //システム情報取得
    public function GetProduct(){
        $DatabaseManager = new DatabaseManager();
        $productResult=$DatabaseManager->GetProduct();
        return $productResult;
    }
    //システム詳細情報取得
    public function GetDetailsProduct($productId){
        $DatabaseManager = new DatabaseManager();
        $productResult = $DatabaseManager->GetDetailsProduct($productId);
        return $productResult;
    }
    //システム削除
    public function DeleteProduct($ProductID){
        $DatabaseManager = new DatabaseManager();
        $productDeleteFlg = $DatabaseManager->DeleteProduct($ProductID);
        return $productDeleteFlg;
    }
    //システム編集
    public function EditProduct($Product){
        $DatabaseManager = new DatabaseManager();
        $DatabaseManager->UpdateProduct($Product);
    }

}
