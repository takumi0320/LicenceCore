<?php
require_once (dirname(__FILE__) . "/../InformationClass/AdministratorUser.php");
require_once (dirname(__FILE__) . "/../ManagerClass/DatabaseManager.php");
class AdministratorUserManager {
    /*ログイン機能*/
    public function LoginVerifyAdministratorUser($administratorId,$administratorPassword){
        $DatabaseManager = new DatabaseManager();
        $AdministratorUser = $DatabaseManager->SelectAdministratorUser($administratorId);
        if(!empty($AdministratorUser)){
            // 値がある場合
            if (password_verify($administratorPassword,$AdministratorUser[0]->AdministratorPassword)) {
                 // パスワードが一致した場合
                session_start();
                session_regenerate_id();
                $_SESSION["administratorId"] = $administratorId;
                header("Location: ./index.php");
                exit();
            }else{
                // パスワードが一致しない場合
                return false;
            }
        }else{
            // 値が入ってない場合
            return false;
        }
    }

    //管理者アカウント重複確認
    public function DuplicationAdministratorUser($AdministratorId){
        $DatabaseManager = new DatabaseManager();
        $administratorUserResult = $DatabaseManager->DuplicationAdministratorUser($AdministratorId);
        return $administratorUserResult;
    }

    //管理者アカウント登録
    public function RegisterAdministratorUser($AdministratorId,$password){
        $DatabaseManager = new DatabaseManager();
        $AdministratorUser = new AdministratorUser();
        $AdministratorUser->AdministratorId = $AdministratorId;
        $AdministratorUser->AdministratorPassword = $password;
        $DatabaseManager->InsertAdministratorUser($AdministratorUser);
    }
    //管理者アカウント情報取得
    public function GetAdministratorUser(){
        $DatabaseManager = new DatabaseManager();
        $administratorUserResult = $DatabaseManager->GetAdministratorUser();
        return $administratorUserResult;
    }

    //管理者アカウントパスワード取得
    public function GetAdministratorUserPassword($administratorId){
        $DatabaseManager = new DatabaseManager();
        $administratorUserResult = $DatabaseManager->GetAdministratorUserPassword($administratorId);
        $currentPassword = $administratorUserResult[0]->AdministratorPassword;
        return $currentPassword;
    }

    //管理者アカウントパスワード一致
    public function EditVerifyAdministratorUser($inputCurrentPassword,$currentPassword){
        if (password_verify($inputCurrentPassword,$currentPassword)) {
            return true;
        }else{
            return false;
        }
    }

    //管理者アカウント削除
    public function DeleteAdministratorUser($AdministratorID){
        $DatabaseManager = new DatabaseManager();
        $DatabaseManager->DeleteAdministratorUser($AdministratorID);
    }

    //管理者アカウント編集
    public function EditAdministratorUser($AdministratorUser){
        $DatabaseManager = new DatabaseManager();
        //ハッシュ化
        password_hash($AdministratorUser->AdministratorPassword);
        $DatabaseManager->UpdateAdministratorUser($AdministratorUser);
    }

}
?>
