<?php
require_once (dirname(__FILE__) . "/../ManagerClass/DatabaseManager.php");
require_once (dirname(__FILE__) . "/../InformationClass/Licence.php");
require_once (dirname(__FILE__) . "/../InformationClass/LicenceOption.php");

class LicenceManager {

    // ライセンス登録
    public function RegisterLicence ($licenceList) {
        $DatabaseManager = new DatabaseManager();
        $DatabaseManager->InsertLicence($licenceList);
    }

    //重複したライセンスを取得
    public function GetOverLapLicence($licenceList){
        $DatabaseManager = new DatabaseManager();
        $overLapLicenceList = array();
        //重複しているライセンスを取得
        foreach ($licenceList as $value) {
            $result = $DatabaseManager->GetOverLapLicence($value->UserId);
            if($result != false){
                array_push($overLapLicenceList,$result);
            }
        }

        return $overLapLicenceList;
    }

    //ライセンスに含まれるシステムが存在するかを検索
    public function GetNotExisitsProduct($licenceList){
        $DatabaseManager = new DatabaseManager();
        $notExisitsProduct = array();
        //存在しているシステムIDを取得
        foreach ($licenceList as $value){
            $productId = $DatabaseManager->GetNotExisitsProduct($value->ProductId);
            //システムIDが存在していない場合
            if($productId == false){
                array_push($notExisitsProduct,$value);
            }
        }
        return $notExisitsProduct;
    }

    //ライセンスに含まれる顧客IDが存在するかを検索
    public function GetNotExisitsCustomer($licenceList){
        $DatabaseManager = new DatabaseManager();
        $notExisitsCustomer = array();
        //存在している顧客IDを取得
        foreach ($licenceList as $value){
            $customerId = $DatabaseManager->GetCustomer($value->CustomerId);
            //顧客IDが存在していない場合
            if($customerId == false){
                array_push($notExisitsCustomer,$value);
            }
        }
        return $notExisitsCustomer;
    }

    //ライセンスの終了日が開始日より前でないかチェック
    public function VerifyLicenceDate($licenceList){
        //ライセンスの終了日が開始日よりも早いか確認
        $mistakeLicenceDate = array();
        foreach ($licenceList as $key => $value){
            $licenceBeginDate = date($value->BeginDate);
            $licenceEndDate = date($value->EndDate);
            if(strtotime($licenceBeginDate) > strtotime($licenceEndDate)){
                array_push($mistakeLicenceDate,$value);
            }
        }
        return $mistakeLicenceDate;
    }

    //顧客IDに当てはまるライセンス一覧取得
    public function GetLicence($CustomerId){
        $DatabaseManager = new DatabaseManager();
        $licenceResult = $DatabaseManager->GetLicence($CustomerId);
        $customerResult = $DatabaseManager->GetCustomer($CustomerId);
        $retList=array($customerResult,$licenceResult);
        return $retList;
    }

    //ライセンスのエクスポート
    public function ExportLicence(){
        try {
            $DatabaseManager =  new DatabaseManager();
            $ExportLicence = $DatabaseManager->GetExportLicence();//出力するライセンスの情報
            //タイトル行
            $csv_header = array(
                'user_id','customer_id','customer_name','product_id','product_name','user_password','number_of_contract_licence','number_of_current_authentication_licence',
                'licence_begin_date','licence_end_date','product_option_id','product_option_name','option_begin_date','option_begin_date'
            );
            $file_path = dirname(__FILE__) . '/../../kanrisya_front/tmp/Licence.csv';
            //SplFileObjectオブジェクト作成
            $fileObject = new SplFileObject($file_path, "w");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        //ヘッダーを追加
        mb_convert_variables('SJIS-win','UTF-8',$csv_header);
        $fileObject->fputcsv($csv_header);
        //データを追加
        foreach($ExportLicence as $row){
            mb_convert_variables('SJIS-win','UTF-8',$row);
            $fileObject->fputcsv($row);//一行ずつ追加
        }
    }

    //一致するユーザIDを取得
    public function GetUserIdFlag ($userId) {
        $DatabaseManager = new DatabaseManager();
        $userIdFlag = $DatabaseManager->SelectUserIdFlag($userId);
        return $userIdFlag;

    }

    //ライセンス詳細情報取得
    public function GetDetailsLicence($userID, $customerId){
        $DatabaseManager = new DatabaseManager();
        $licenceResult = array();
        $customerList  = $DatabaseManager->GetCustomer($customerId);
        $licenceResult["customerName"] = $customerList[0]->CustomerName;
        $licenceResult["productName"] = $DatabaseManager->GetProductName($userID);
        $licenceResult["licenceInformation"] = $DatabaseManager->GetDetailsLicence($userID);
        return $licenceResult;
    }

    //ライセンス削除
    public function DeleteLicence($userID){
        $DatabaseManager = new DatabaseManager();
        $resultDeleteLicence = $DatabaseManager->DeleteLicence($userID);
        return $resultDeleteLicence;
    }

    //ライセンス編集
    public function EditLicence($licenceList){
        $DatabaseManager = new DatabaseManager();
        $DatabaseManager->UpdateLicence($licenceList);
    }
}
