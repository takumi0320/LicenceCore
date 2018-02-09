<?php
require_once (dirname(__FILE__) . "/../InformationClass/Option.php");
require_once (dirname(__FILE__) . "/../ManagerClass/DatabaseManager.php");
class OptionManager {

    //オプション登録
    public function RegisterOption($OptionName,$OptionKana){
        $DatabaseManager = new DatabaseManager();
        $Option = new Option();
        $Option->OptionName = $OptionName;
        $Option->OptionKana = $OptionKana;
        $DatabaseManager->InsertOption($Option);
    }
    //オプション情報取得
    public function GetOption(){
        $DatabaseManager = new DatabaseManager();
        $Option = new Option();
        $optionResult=$DatabaseManager->GetOption();
        return $optionResult;
    }
    //オプション詳細情報取得
    public function GetDetailsOption($optionId){
        $DatabaseManager = new DatabaseManager();
        $optionResult = $DatabaseManager->GetDetailsOption($optionId);
        return $optionResult;
    }
    //オプション削除
    public function DeleteOption($OptionID){
        $DatabaseManager = new DatabaseManager();
        $optionDeleteFlg = $DatabaseManager->DeleteOption($OptionID);
        return $optionDeleteFlg;
    }
    //オプション編集
    public function EditOption($Option){
        $DatabaseManager = new DatabaseManager();
        $DatabaseManager->UpdateOption($Option);
    }


}
