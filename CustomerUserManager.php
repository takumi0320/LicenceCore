<?php
require_once (dirname(__FILE__) . "/../ManagerClass/DatabaseManager.php");
class CustomerUserManager {
    // ユーザー情報登録
    public function RegisterCustomerUser ($customerUserList) {
        $DatabaseManager = new DatabaseManager();
        $customerId = $DatabaseManager->InsertCustomerUser($customerUserList);
        return $customerId;
    }

    //ユーザー情報取得
    public function GetCustomerUser(){
        $DatabaseManager = new DatabaseManager();
        $userResult = $DatabaseManager->GetCustomerUser();
        return $userResult;
    }
    //ユーザー検索情報取得
    public function SearchCustomerUser($customerName){
        $DatabaseManager = new DatabaseManager();
        $userResult = $DatabaseManager->SelectCustomerUser($customerName);
        return $userResult;
    }
}
