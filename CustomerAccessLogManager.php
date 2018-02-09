<?php
require_once (dirname(__FILE__) . "/../ManagerClass/DatabaseManager.php");
require_once (dirname(__FILE__) . "/../InformationClass/CustomerAccessLog.php");
class CustomerAccessLogManager {

    //アクセスログ一覧を取得する
    public function GetAccessLog(){
        $DatabaseManager = new DatabaseManager();
        $accessLogList = $DatabaseManager->GetAccessLog();
        return $accessLogList;
    }

    //アクセスログエクスポート
    public function ExportAccessLog(){
        try {
            $DatabaseManager =  new DatabaseManager();
            $AccessLogList = $DatabaseManager->GetExportAccessLog();
            $AccessLogList = json_decode(json_encode($AccessLogList), true);//オブジェクトを配列に変換
            $file_path = dirname(__FILE__) . '/../../kanrisya_front/tmp/AccessLog.csv';
             //SplFileObjectオブジェクト作成
            $fileObject = new SplFileObject($file_path, "w");
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        //ヘッダーを追加
        $csv_header = array(
            'access_log_id','user_id','security_id','ip_address','browser_information','url',
            'http_referrer','operation','access_date'
        );
        mb_convert_variables('SJIS-win','UTF-8',$csv_header);
        $fileObject->fputcsv($csv_header);
        //データを追加
        foreach($AccessLogList as $row){
            mb_convert_variables('SJIS-win','UTF-8',$row);
            $fileObject->fputcsv($row);//一行ずつ追加
        }
    }

}
?>
