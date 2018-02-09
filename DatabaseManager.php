<?php
require_once (dirname(__FILE__) . "/../InformationClass/AdministratorUser.php");
require_once (dirname(__FILE__) . "/../InformationClass/CustomerUser.php");
require_once (dirname(__FILE__) . "/../InformationClass/CustomerAccessLog.php");
require_once (dirname(__FILE__) . "/../InformationClass/Licence.php");
require_once (dirname(__FILE__) . "/../InformationClass/LicenceOption.php");
require_once (dirname(__FILE__) . "/../InformationClass/Option.php");
require_once (dirname(__FILE__) . "/../InformationClass/Product.php");
class DatabaseManager {
    // DB情報
    private $databaseUserId ="LAA0932824";
    private $databasePassword ="Son0320th";
    private $databaseHost ="mysql118.phy.lolipop.lan";
    private $databaseName ="LAA0932824-takumi0320";
    private $myPdo;

    // データベース接続
    private function connectDatabase(){
        try{
            $this->myPdo = new PDO('mysql:host=' .$this->databaseHost. ';dbname=' .$this->databaseName .';charset=utf8', $this->databaseUserId , $this->databasePassword, array(PDO::ATTR_EMULATE_PREPARES => false));
        }catch(PDOException $e){

            print('データベース接続失敗。'.$e->getMessage());
            throw $e;
        }
    }

    // データベース切断
    private function disconnectDatabase() {
        unset($this->myPdo);
    }


    // 顧客情報登録
    public function InsertCustomerUser ($customerUserList) {
        try {
            $this->connectDatabase();
            // 顧客情報を挿入
            $stmt = $this->myPdo->prepare('INSERT INTO customer(customer_name, customer_kana) VALUES (:customerName, :customerKana)');
            $stmt->bindParam(':customerName', $customerUserList->CustomerName, PDO::PARAM_STR);
            $stmt->bindParam(':customerKana', $customerUserList->CustomerKana, PDO::PARAM_STR);
            $stmt->execute();
            // 登録した顧客のidを取得
            $stmt = $this->myPdo->prepare('SELECT customer_id FROM customer WHERE customer_name = :customerName');
            $stmt->bindParam(':customerName', $customerUserList->CustomerName, PDO::PARAM_STR);
            $stmt->execute();
            $customerData = $stmt->fetch(PDO::FETCH_ASSOC);
            $customerId = $customerData['customer_id'];
            $this->disconnectDatabase();
            return $customerId;
        } catch (PDOException $e) {
            print('顧客登録エラー' . $e->getMessage());
            throw $e;
        }
    }


    // ライセンス情報登録
    public function InsertLicence ($licenceList) {
        try {
            $defaultCount = 0;
            $this->connectDatabase();
            $stmt = $this->myPdo->prepare('INSERT INTO licence(user_id, customer_id, product_id, user_password,
                                                    number_of_contract_licence, number_of_current_authentication_licence,
                                                    licence_begin_date, licence_end_date)
                                            VALUES (:userId, :customerId, :productId, :password, :contractLicence,
                                                    :currentAuthenticationLicence, :beginDate, :endDate)');
            $stmt->bindParam(':userId', $licenceList->UserId, PDO::PARAM_STR);
            $stmt->bindParam(':customerId', $licenceList->CustomerId, PDO::PARAM_STR);
            $stmt->bindParam(':productId', $licenceList->ProductId, PDO::PARAM_STR);
            $stmt->bindParam(':password', $licenceList->CustomerPassword, PDO::PARAM_STR);
            $stmt->bindParam(':contractLicence', $licenceList->ContractCountLicence, PDO::PARAM_STR);
            $stmt->bindParam(':currentAuthenticationLicence', $defaultCount, PDO::PARAM_STR);
            $stmt->bindParam(':beginDate', $licenceList->BeginDate, PDO::PARAM_STR);
            $stmt->bindParam(':endDate', $licenceList->EndDate, PDO::PARAM_STR);
            $stmt->execute();
            if($licenceList->LicenceOptionList != null){
                foreach ($licenceList->LicenceOptionList as $value) {
                    $stmt = $this->myPdo->prepare('INSERT INTO licence_option(user_id, product_option_id,
                                                                            option_begin_date, option_end_date)
                                                    VALUES (:userId, :optionId, :optionBeginDate, :optionEndDate)');
                    $stmt->bindParam(':userId', $licenceList->UserId, PDO::PARAM_STR);
                    $stmt->bindParam(':optionId', $value->OptionId, PDO::PARAM_STR);
                    $stmt->bindParam(':optionBeginDate', $value->BeginDate, PDO::PARAM_STR);
                    $stmt->bindParam(':optionEndDate', $value->EndDate, PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
            $this->disconnectDatabase();
        } catch (PDOException $e) {
            print('ライセンス登録エラー' . $e->getMessage());
            throw $e;
        }
    }

    //管理者IDを検索
    public function SelectAdministratorUser($administratorId){
        try{
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare('SELECT * FROM administrator where administrator_id = :administratorId');
            $stmt->bindValue(':administratorId', $administratorId, PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();
            //取得したデータを一件ずつループしながらクラスに入れていく
            $administratorUserList = array();
            while($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
                //データを入れるクラスを初期化
                $rowData = new AdministratorUser();
                //データベースからとれた情報をカラム毎にクラスにいれていく
                $rowData->AdministratorId = $row["administrator_id"];
                $rowData->AdministratorPassword = $row["administrator_password"];
                //取得した一覧を配列に入れていく
                array_push($administratorUserList, $rowData);
            }
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $administratorUserList;
        }catch (PDOException $e){
            print('検索に失敗'.$e->getMessage());
        }
    }


    //管理者アカウント重複登録確認
    public function DuplicationAdministratorUser($AdministratorUserID){
     try{
            $this->connectDatabase();
            $stmt = $this->myPdo->prepare("SELECT * FROM administrator WHERE administrator_id = :administratorUserID");
            $stmt->bindParam(':administratorUserID', $AdministratorUserID, PDO::PARAM_STR);
            $stmt->execute();
            $result = false;
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $result = true;
            }
            $this->disconnectDatabase();
            return $result;
        }catch (PDOException $e){
            print('チェックに失敗しました。'.$e->getMessage());
            throw $e;
        }
    }

    //管理者アカウント登録
    public function InsertAdministratorUser($AdministratorUser){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare("INSERT INTO administrator(administrator_id,administrator_password) VALUES (:administratorID,:administratorPassword)");
            $stmt->bindParam(':administratorID',$AdministratorUser->AdministratorId,PDO::PARAM_STR);
            $stmt->bindParam(':administratorPassword',$AdministratorUser->AdministratorPassword,PDO::PARAM_STR);
            //SQL実行
            $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
            throw $e;
        }
    }

    //管理者アカウント情報取得
    public function GetAdministratorUser(){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQLを生成
            $stmt = $this->myPdo->prepare("SELECT * FROM administrator");
            //SQLを実行
            $stmt->execute();
            //取得したデータを１件ずつループしながらクラスに入れていく
            $retList = array();
            while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
                $result = new AdministratorUser();
                $result->AdministratorId = $row['administrator_id'];
                array_push($retList, $result);
            }
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $retList;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //管理者アカウントパスワード取得
    public function GetAdministratorUserPassword($administratorId){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQLを生成
            $stmt = $this->myPdo->prepare("SELECT * FROM administrator WHERE administrator_id = :administratorId ");
            $stmt->bindParam(':administratorId',$administratorId,PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();
            //取得したデータを１件ずつループしながらクラスに入れていく
            $retList = array();
            while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
                $result = new AdministratorUser();
                $result->AdministratorId = $row['administrator_id'];
                $result->AdministratorPassword = $row['administrator_password'];
                array_push($retList, $result);
            }
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $retList;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //管理者アカウント情報削除
    public function DeleteAdministratorUser($AdministratorID){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare("DELETE FROM administrator  WHERE administrator_id = :administratorId");
            $stmt->bindParam(':administratorId',$AdministratorID,PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
        }catch (PDOException $e) {
          print('削除に失敗しました。'.$e->getMessage());
          throw $e;
        }
    }

    //管理者アカウント編集
    public function UpdateAdministratorUser($AdministratorUser){
        try{
            //データベース接続
            $this->connectDatabase();
            $stmt = $this->myPdo->prepare("UPDATE administrator SET administrator_password = :administratorPassword WHERE administrator_id = :administratorId");
            $stmt->bindParam(':administratorId',$AdministratorUser->AdministratorId,PDO::PARAM_STR);
            $stmt->bindParam(':administratorPassword',$AdministratorUser->AdministratorPassword,PDO::PARAM_STR);
            //SQL実行
            $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
        }catch (PDOException $e) {
          print('削除に失敗しました。'.$e->getMessage());
          throw $e;
        }
    }

    //ユーザー情報取得
    public function GetCustomerUser(){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQLを生成
            $stmt = $this->myPdo->prepare("SELECT * FROM customer");
            //SQLを実行
            $stmt->execute();
            //取得したデータを１件ずつループしながらクラスに入れていく
            $retList = array();
            while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
                $result = new CustomerUser();
                $result->CustomerId= $row['customer_id'];
                $result->CustomerName = $row['customer_name'];
                array_push($retList, $result);
            }
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $retList;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //ユーザー検索情報取得
    public function SelectCustomerUser($customerName){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQLを生成
            $stmt = $this->myPdo->prepare("SELECT * FROM customer WHERE customer_name  LIKE '%$customerName%'");
            //SQLを実行
            $stmt->execute();
            //取得したデータを１件ずつループしながらクラスに入れていく
            $retList = array();
            while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
                $result = new CustomerUser();
                $result->CustomerId= $row['customer_id'];
                $result->CustomerName = $row['customer_name'];
                array_push($retList, $result);
            }
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $retList;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //システム情報登録
    public function InsertProduct($Product){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare("INSERT INTO product(product_name,product_kana) VALUES (:productName,:productKana)");
            $stmt->bindParam(':productName',$Product->ProductName,PDO::PARAM_STR);
            $stmt->bindParam(':productKana',$Product->ProductKana,PDO::PARAM_STR);
            //SQL実行
            $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
            throw $e;
        }
    }

    //システム情報取得
    public function GetProduct(){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQLを生成
            $stmt = $this->myPdo->prepare("SELECT * FROM product");
            //SQLを実行
            $stmt->execute();
            //取得したデータを１件ずつループしながらクラスに入れていく
            $retList = array();
            while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
                $result = new Product();
                $result->ProductId = $row['product_id'];
                $result->ProductName = $row['product_name'];
                $result->ProductKana = $row['product_kana'];
                array_push($retList, $result);
            }
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $retList;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //システム詳細情報取得
    public function GetDetailsProduct($productId){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQLを生成
            $stmt = $this->myPdo->prepare("SELECT * FROM product WHERE product_id = :productId");
            $stmt->bindParam(':productId',$productId,PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();
            //取得したデータを１件ずつループしながらクラスに入れていく
            $retList = array();
            while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
                $result = new Product();
                $result->ProductId = $row['product_id'];
                $result->ProductName = $row['product_name'];
                $result->ProductKana = $row['product_kana'];
                array_push($retList, $result);
            }
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $retList;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //システム情報削除
    public function DeleteProduct($ProductID){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare("DELETE FROM product  WHERE product_id = :productID");
            $stmt->bindParam(':productID',$ProductID,PDO::PARAM_STR);
            //SQLを実行
            $result = $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
            return $result;
        }catch (PDOException $e) {
          print('削除に失敗しました。'.$e->getMessage());
          throw $e;
        }
    }

    //システム編集
    public function UpdateProduct($Product){
        try{
            $this->connectDatabase();
            // ライセンス情報アップデート
            $stmt = $this->myPdo->prepare("UPDATE product SET  product_name = :productName, product_kana = :productKana WHERE  product_id = :productId");
            $stmt->bindParam(':productId',$Product->ProductId,PDO::PARAM_STR);
            $stmt->bindParam(':productName',$Product->ProductName,PDO::PARAM_STR);
            $stmt->bindParam(':productKana',$Product->ProductKana ,PDO::PARAM_STR);
            //SQL実行
            $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
        }catch (PDOException $e) {
          print('削除に失敗しました。'.$e->getMessage());
          throw $e;
        }
    }

    //オプション情報登録
    public function InsertOption($Option){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare("INSERT INTO product_option(product_option_name,product_option_kana) VALUES (:optionName,:optionKana)");
            $stmt->bindParam(':optionName',$Option->OptionName,PDO::PARAM_STR);
            $stmt->bindParam(':optionKana',$Option->OptionKana,PDO::PARAM_STR);
            //SQL実行
            $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
            throw $e;
        }
    }

    //オプション情報取得
    public function GetOption(){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQLを生成
            $stmt = $this->myPdo->prepare("SELECT * FROM product_option");
            //SQLを実行
            $stmt->execute();
            //取得したデータを１件ずつループしながらクラスに入れていく
            $retList = array();
            while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
                $result = new Option();
                $result->OptionId = $row['product_option_id'];
                $result->OptionName = $row['product_option_name'];
                $result->OptionKana = $row['product_option_kana'];
                array_push($retList, $result);
            }
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $retList;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //オプション詳細情報取得
    public function GetDetailsOption($optionId){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQLを生成
            $stmt = $this->myPdo->prepare("SELECT * FROM product_option WHERE product_option_id = :optionId");
            $stmt->bindParam(':optionId',$optionId,PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();
            //取得したデータを１件ずつループしながらクラスに入れていく
            $retList = array();
            while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
                $result = new Option();
                $result->OptionId = $row['product_option_id'];
                $result->OptionName = $row['product_option_name'];
                $result->OptionKana = $row['product_option_kana'];
                array_push($retList, $result);
            }
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $retList;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //オプション削除
    public function DeleteOption($OptionID){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare("DELETE FROM product_option WHERE product_option_id = :optionID");
            $stmt->bindValue(':optionID',$OptionID,PDO::PARAM_STR);
            //SQLを実行
            $result = $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
            return $result;
        }catch (PDOException $e) {
          print('削除に失敗しました。'.$e->getMessage());
          throw $e;
        }
    }

    //オプション編集
    public function UpdateOption($Option){
        try{
            $this->connectDatabase();
            // ライセンス情報アップデート
            $stmt = $this->myPdo->prepare("UPDATE product_option SET  product_option_name = :optionName, product_option_kana = :optionKana WHERE  product_option_id = :optionId");
            $stmt->bindParam(':optionId',$Option->OptionId,PDO::PARAM_STR);
            $stmt->bindParam(':optionName',$Option->OptionName,PDO::PARAM_STR);
            $stmt->bindParam(':optionKana',$Option->OptionKana ,PDO::PARAM_STR);
            //SQL実行
            $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
        }catch (PDOException $e) {
          print('削除に失敗しました。'.$e->getMessage());
          throw $e;
        }

    }

    //重複しているライセンスを取得
    public function GetOverLapLicence($userId){
        try{
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare(
                'SELECT licence.customer_id, customer.customer_name, licence.user_id, product.product_name
                 FROM licence, customer, product
                 WHERE licence.customer_id = customer.customer_id
                 AND licence.product_id = product.product_id
                 AND licence.user_id = :userId'
            );
            $stmt->bindValue(':userId', $userId, PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();
            //データを入れるクラスを初期化
            $getOverLapLicenceList = array();
            //取得したデータを入れていく
            if($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
                $getOverLapLicenceList['customerId'] = $row['customer_id'];
                $getOverLapLicenceList['customerName'] = $row['customer_name'];
                $getOverLapLicenceList['userId'] = $row['user_id'];
                $getOverLapLicenceList['productName'] = $row['product_name'];
            }else{
                return false;
            }
            $this->disconnectDatabase();
            return $getOverLapLicenceList;
        }catch (PDOException $e){
            print('検索に失敗'.$e->getMessage());
        }
    }

    //ライセンス一覧取得
    public function GetLicence($customerId){
        try{
           //データベース接続
           $this->connectDatabase();
           //SQLを生成
           $stmt = $this->myPdo->prepare("SELECT user_id,product_name,licence_begin_date,licence_end_date FROM licence,product WHERE licence.product_id = product.product_id and licence.customer_id = :customer_id");
           $stmt->bindParam(':customer_id',$customerId,PDO::PARAM_STR);
           //SQLを実行
           $stmt->execute();
           //取得したデータを１件ずつループしながらクラスに入れていく
           $retList = array();
           while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
               $result = new Licence();
               $result->UserId = $row['user_id'];
               $result->CustomerId = $row['product_name'];
               $result->BeginDate = $row['licence_begin_date'];
               $result->EndDate = $row['licence_end_date'];
               array_push($retList, $result);
           }
           $this->disconnectDatabase();
           //結果が格納された配列を返す
           return $retList;
       }catch (PDOException $e) {
           print('検索に失敗。'.$e->getMessage());
       }
   }

    //顧客名検索
    public function GetCustomer($customerId){
        try{
               //データベース接続
               $this->connectDatabase();
               //SQLを生成
               $stmt = $this->myPdo->prepare("SELECT customer_name FROM customer WHERE customer_id = :customer_id");
               $stmt->bindParam(':customer_id',$customerId,PDO::PARAM_STR);
               //SQLを実行
               $stmt->execute();
               //取得したデータを１件ずつループしながらクラスに入れていく
               $retList = array();
               while($row = $stmt ->fetch(PDO::FETCH_ASSOC)){
                   $result = new CustomerUser();
                   $result->CustomerName = $row['customer_name'];
                   array_push($retList, $result);
               }
               $this->disconnectDatabase();
               //結果が格納された配列を返す
               return $retList;
           }catch (PDOException $e) {
               print('検索に失敗。'.$e->getMessage());
           }
       }

    // ライセンス一括登録のシステム名を検索
    public function GetNotExisitsProduct($productId){
        try{
            //データベース接続
            $this->connectDatabase();
            // SQLを生成
            $stmt = $this->myPdo->prepare(
                'SELECT product_id FROM product
                 WHERE product_id = :productId'
            );
            //SQLを実行
            $stmt->bindValue(':productId',$productId, PDO::PARAM_STR);
            $stmt->execute();
            //データを入れるクラスを初期化
            $product = array();
            //取得したデータを一件ずつループしながら配列に入れていく
            if($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
                foreach ($row as $value) {
                    array_push($product, $value);
                }
            }else{
                return false;
            }
            $this->disconnectDatabase();
            return $product;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    // システム名取得
    public function GetProductName ($userId) {
        try{
            //データベース接続
            $this->connectDatabase();
            //SQLを生成
            $stmt = $this->myPdo->prepare("SELECT product.product_name AS product_name FROM licence, product
                                            WHERE licence.product_id = product.product_id
                                            AND licence.user_id = :userId");
            $stmt->bindParam(':userId',$userId,PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();
            $productData = $stmt ->fetch(PDO::FETCH_ASSOC);
            $productName = $productData['product_name'];
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $productName;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //ライセンス詳細情報取得
    public function GetDetailsLicence($userID){
        try{
            //データベース接続
            $this->connectDatabase();
            $stmt = $this->myPdo->prepare("SELECT * FROM licence WHERE user_id = :userID");
            $stmt->bindParam(':userID', $userID,PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();
            //取得したデータを１件ずつループしながらクラスに入れていく
            $licenceData = $stmt->fetch(PDO::FETCH_ASSOC);
            $Licence = new Licence();
            $Licence->ContractCountLicence = $licenceData['number_of_contract_licence'];
            $Licence->BeginDate = date('Y年m月d日', (strtotime($licenceData['licence_begin_date'])));
            $Licence->EndDate = date('Y年m月d日', (strtotime($licenceData['licence_end_date'])));
            $Licence->LicenceOptionList = array();
            $stmt = $this->myPdo->prepare("SELECT   product_option.product_option_id AS option_id,  product_option.product_option_name AS option_name,
                                                    licence_option.option_begin_date AS begin_date, licence_option.option_end_date AS end_date
                                            FROM licence_option LEFT OUTER JOIN product_option ON product_option.product_option_id = licence_option.product_option_id
                                            WHERE user_id = :userID");
            $stmt->bindParam(':userID', $userID,PDO::PARAM_STR);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $LicenceOption = new LicenceOption();
                $LicenceOption->OptionId = $row['option_id'];
                $LicenceOption->OptionName = $row['option_name'];
                $LicenceOption->BeginDate = date('Y年m月d日', (strtotime($row['begin_date'])));
                $LicenceOption->EndDate = date('Y年m月d日', (strtotime($row['end_date'])));
                $Licence->LicenceOptionList[] = $LicenceOption;
            }
            // データベース切断
            $this->disconnectDatabase();
            //結果が格納された配列を返す
            return $Licence;
        }catch (PDOException $e) {
            print('検索に失敗。'.$e->getMessage());
        }
    }

    //ライセンスのエクスポート
    public function GetExportLicence(){
        try{
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare(
                'SELECT licence.user_id,licence.customer_id,customer.customer_name,licence.product_id,product.product_name,licence.user_password,licence.number_of_contract_licence,
                 licence.number_of_current_authentication_licence,licence.licence_begin_date,licence.licence_end_date,
                 licence_option.product_option_id,product_option.product_option_name,licence_option.option_begin_date,licence_option.option_end_date
                 FROM licence
                 LEFT OUTER JOIN licence_option
                 ON licence.user_id = licence_option.user_id
                 LEFT OUTER JOIN customer
                 ON licence.customer_id = customer.customer_id
                 LEFT OUTER JOIN product
                 ON licence.product_id = product.product_id
                 LEFT OUTER JOIN product_option
                 ON licence_option.product_option_id = product_option.product_option_id
                 ORDER BY licence.user_id
            ');
            $exportLicenceList= array();
            //SQLを実行
            $stmt->execute();
            //取得したデータをループしながらクラスに入れていく
            while($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
                $exportLicence['userId'] = $row['user_id'];
                $exportLicence['customerId'] = $row['customer_id'];
                $exportLicence['customerName'] = $row['customer_name'];
                $exportLicence['productId'] = $row['product_id'];
                $exportLicence['productName'] = $row['product_name'];
                $exportLicence['userPasword'] = $row['user_password'];
                $exportLicence['numberOfContractLicence'] = $row['number_of_contract_licence'];
                $exportLicence['numberOfCurrentAuthenticationLicence'] = $row['number_of_current_authentication_licence'];
                $exportLicence['licenceBeginDate'] = $row['licence_begin_date'];
                $exportLicence['licenceEndDate'] = $row['licence_end_date'];
                $exportLicence['productOptionId'] = $row['product_option_id'];
                $exportLicence['productOptionName'] = $row['product_option_name'];
                $exportLicence['optionBeginDate'] = $row['option_begin_date'];
                $exportLicence['optionEndDate'] = $row['option_end_date'];
                array_push($exportLicenceList, $exportLicence);
            }
            $this->disconnectDatabase();
            return $exportLicenceList;

        }catch (PDOException $e){
            print('検索に失敗'.$e->getMessage());
        }
    }

    //ライセンス削除
    public function DeleteLicence($userID){
        try{
            //データベース接続
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare("DELETE FROM licence_option WHERE licence_option.user_id = :userID");
            $stmt->bindParam(':userID',$userID,PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();

            //SQL生成
            $stmt = $this->myPdo->prepare("DELETE FROM licence WHERE licence.user_id = :userID");
            $stmt->bindParam(':userID',$userID,PDO::PARAM_STR);
            //SQLを実行
            $stmt->execute();
            //データベース切断
            $this->disconnectDatabase();
        }catch (PDOException $e) {
          print('削除に失敗しました。'.$e->getMessage());
          throw $e;
        }
    }

    //ライセンス編集
    public function UpdateLicence($licenceList){
        try{
            //データベース接続
            $this->connectDatabase();
            // ライセンス情報アップデート
            $stmt = $this->myPdo->prepare("UPDATE licence SET number_of_contract_licence = :contractLicence, licence_begin_date = :licenceBeginDate, licence_end_date = :licenceEndDate WHERE user_id = :userId");
            $stmt->bindParam(':userId',$licenceList->UserId,PDO::PARAM_STR);
            $stmt->bindParam(':contractLicence',$licenceList->ContractCountLicence,PDO::PARAM_STR);
            $stmt->bindParam(':licenceBeginDate',$licenceList->BeginDate ,PDO::PARAM_STR);
            $stmt->bindParam(':licenceEndDate',$licenceList->EndDate ,PDO::PARAM_STR);
            $stmt->execute();

            // オプション情報を一旦削除
            $stmt = $this->myPdo->prepare("DELETE FROM licence_option WHERE user_id = :userId");
            $stmt->bindParam(':userId', $licenceList->UserId, PDO::PARAM_STR);
            $stmt->execute();

            // オプション情報の最新を挿入
            foreach ($licenceList->LicenceOptionList as $value) {
                $stmt = $this->myPdo->prepare('INSERT INTO licence_option(user_id, product_option_id, option_begin_date, option_end_date)
                                                VALUES (:userId, :optionId, :beginDate, :endDate)');
                $stmt->bindParam(':userId',$licenceList->UserId,PDO::PARAM_STR);
                $stmt->bindParam(':optionId', $value->OptionId, PDO::PARAM_STR);
                $stmt->bindParam(':beginDate', $value->BeginDate, PDO::PARAM_STR);
                $stmt->bindParam(':endDate', $value->EndDate, PDO::PARAM_STR);
                $stmt->execute();
            }

            //データベース切断
            $this->disconnectDatabase();
        }catch (PDOException $e) {
          print('削除に失敗しました。'.$e->getMessage());
          throw $e;
        }

    }

    //アクセスログを取得
    public function GetAccessLog(){
        try{
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare(
                'SELECT customer_access_log.access_log_id,customer.customer_name, customer_access_log.operation, customer_access_log.access_date
                 FROM licence, customer,customer_access_log
                 WHERE licence.customer_id = customer.customer_id
                 AND licence.user_id = customer_access_log.user_id
                 ORDER BY access_date DESC'
            );
            //SQLを実行
            $stmt->execute();
            //取得したデータを入れていく
            $retList = array();
            while($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
                $result = new CustomerAccessLog();
                $result->AccessLogId = $row['access_log_id'];
                $result->SecurityId = $row['customer_name'];
                $result->IpAddress = $row['operation'];
                $result->BrowserName = $row['access_date'];
                array_push($retList, $result);
            }
            $this->disconnectDatabase();
            return $retList;
        }catch (PDOException $e){
            print('検索に失敗'.$e->getMessage());
        }
    }

    //アクセスログのエクスポートの情報を取得
    public function GetExportAccessLog(){
        try{
            $this->connectDatabase();
            //SQL生成
            $stmt = $this->myPdo->prepare(
                'SELECT * FROM customer_access_log;
            ');
            $accessLogList = array();
            //SQLを実行
            $stmt->execute();
            //取得したデータをループしながらクラスに入れていく
            while($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
                $rowData = new CustomerAccessLog();
                $rowData->AccessLogId = $row['access_log_id'];
                $rowData->UserId = $row['user_id'];
                $rowData->SecurityId = $row['security_id'];
                $rowData->IpAddress = $row['ip_address'];
                $rowData->BrowserName = $row['browser_information'];
                $rowData->Url = $row['url'];
                $rowData->HttpReferer = $row['http_referrer'];
                $rowData->Operation = $row['operation'];
                $rowData->AccessDate = $row['access_date'];
                array_push($accessLogList,$rowData);
            }
            $this->disconnectDatabase();
            return $accessLogList;
        }catch (PDOException $e){
            print('検索に失敗'.$e->getMessage());
        }
    }

    //一致するユーザIDを検索
    public function SelectUserIdFlag ($userId) {
        try {
            $this->connectDatabase();
            $stmt = $this->myPdo->prepare('SELECT COUNT(*) AS user_count FROM licence WHERE user_id = :userId');
            $stmt->bindParam(':userId', $userId, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->disconnectDatabase();
            if ($result['user_count'] > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            print('検索エラー' . $e->getMessage());
            throw $e;
        }
    }
}
