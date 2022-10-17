<?php
// var_dump("Mywishlist");
require('./import_to_prestaNew.php');
/***
 * Require for wishlist();
 */
// new Webservice_presta();

class Mywishlist extends Webservice_presta {

        public $query ;
        public $Wish ;
        public $action ;
        public $querySecond ;

        public function __construct(){
             require('../modules/blockwishlist/classes/WishList.php');
             Webservice_presta::__construct();
             $this->Wish = new WishList();
             $this->query = new DbQuery();
             $this->querySecond = new DbQuery();
        }
        public function getAllWishlistsByIdCustomer($id_customer){
            $arraylist = $this->Wish::getAllWishlistsByIdCustomer($id_customer);
            // var_dump($arraylist);
            $arrayResult=array();
            $arrayResultAfterFetch= array();

            foreach($arraylist as $value){
                $id_wishlist = $value['id_wishlist'] ;
                $condition =  "id_wishlist='$id_wishlist'";

                $requester = new DbQuery();
                $requester->select('*');
                $requester->from('wishlist_product');
                $requester->where($condition);
                $result = Db::getInstance()->executeS($requester);
                array_push($arrayResult , $result );
            }
            foreach($arrayResult as $table){
                foreach($table as $value){
                    $arrayer = $this->getProductById((int)$value['id_product']);
                    array_push($arrayResultAfterFetch , $arrayer['product']->product );
                }
            }
            $output= (object)array('product'=> $arrayResultAfterFetch );
            return($output);
        }
        
        public function getAllWishlistsByIdCustomerAndId_wishlist($id_wishlist){
                $arrayResultAfterFetch= array();
                $condition =  "id_wishlist=$id_wishlist";
                $requester = new DbQuery();
                $requester->select('*');
                $requester->from('wishlist_product');
                $requester->where($condition);
                $result = Db::getInstance()->executeS($requester);
                foreach($result as $value){
                    $arrayer = $this->getProductById((int)$value['id_product']);
                    array_push($arrayResultAfterFetch , $arrayer['product']->product );
                }
               $output= (object)array('product'=> $arrayResultAfterFetch );
               return($output);
        }

        /**
         * ADD A PRODUCTS TO TABELE  :  wishlist_product
         * 
         */
        public function ADD($id_wishlist=null, $id_customer, $id_product , $id_product_attribute=0, $quantity=null){
            
            /**
             * This will add products to  table :  wishlist_product
             */
            
             $resulte =  $this->Wish::addProduct($id_wishlist, $id_customer, $id_product, $id_product_attribute, $quantity);
             /**
              * This will count product in same id_wishlist
              */

             $condition =  "id_wishlist='$id_wishlist'";
                $this->query->select('COUNT(*)');
                $this->query->from('wishlist_product');
                $this->query->where($condition);
                $result = Db::getInstance()->executeS($this->query);
            /**
             * This will update counter column of wishlist categorie in table : wishlist
             */
            if( (int)$result[0]['COUNT(*)'] != 0) {
                $ToWishlist = Db::getInstance()->update(
                    'wishlist',
                    [   
                        'counter' =>(int)$result[0]['COUNT(*)'],
                        'date_upd' => date('Y-m-d h:i:s'),
                    ],
                    $condition
                );

                if($ToWishlist == true){
                     return true ;
                }else{
                     return "Error" ;
                }
            }
        }
        public function REMOVE($id_wishlist=null, $id_customer, $id_product , $id_product_attribute=0){
            
             $resulte00 =  $this->Wish::removeProduct($id_wishlist, $id_customer, $id_product, $id_product_attribute);
             
              $condition =  "id_wishlist='$id_wishlist'";
                $this->query->select('COUNT(*)');
                $this->query->from('wishlist_product');
                $this->query->where($condition);
                $result = Db::getInstance()->executeS($this->query);
                // var_dump($result);
                // die();
            /**
             * This will update counter column of wishlist categorie in table : wishlist
             */
                $ToWishlist = Db::getInstance()->update(
                    'wishlist',
                    [   
                        'counter' =>(int)$result[0]['COUNT(*)'],
                        'date_upd' => date('Y-m-d h:i:s'),
                    ],
                    $condition
                );
                if($ToWishlist == true){
                     return true ;
                }else{
                     return "Error" ;
                }
            
        } 
        public function wishlistCategories($id_customer){

                $condition =  "id_customer='$id_customer'";
                $this->query->select('*');
                $this->query->from('wishlist');
                $this->query->where($condition);
                $result = Db::getInstance()->executeS($this->query);
                // var_dump($result);
             return($result) ;
        }
        public function SettingMenuIndicator($id_customer){

                $condition =  "id_customer='$id_customer'";
                $this->query->select('*');
                $this->query->from('wishlist');
                $this->query->where($condition);
                $result = Db::getInstance()->executeS($this->query);
                //  var_dump($result);
                $arrayResult = array();
                 foreach($result as $case){
                    // var_dump((int)$case['id_wishlist']);
                    $id_wishlist = (int)$case['id_wishlist'];
                    $condition2 =  "id_wishlist='$id_wishlist'";
                    $this->querySecond = new DbQuery();
                    $this->querySecond->select('*');
                    $this->querySecond->from('wishlist_product');
                    $this->querySecond->where($condition2);
                    $result2 = Db::getInstance()->executeS($this->querySecond);
                    array_push($arrayResult,$result2);
                 }
                 return(!empty($arrayResult));
            
        }
        public function CheckProducts($id_customer,$id_product){
                $sql = new DbQuery();
                $sql->select('*');
                $sql->from('wishlist_product', 'wp');
                $sql->innerJoin('wishlist', 'w', 'wp.id_wishlist=w.id_wishlist AND w.id_customer='.(int)$id_customer);
                $sql->where('wp.id_product='.(int)$id_product);
                $result = Db::getInstance()->executeS($sql);
                return(!empty($result));
        }

        public function ADD_CATEGORIES($id_customer,$name){

            /**
             * To Import Parametre,
             *  To get Cookie_key
             */
            // var_dump($id_customer);
            // var_dump($name);
            // die();

            $array = include('../app/config/parameters.php') ;
            $cookie_key = $array['parameters']['cookie_key'] ;
            /**
             * Token Generator
             */
            $token = strtoupper(substr(sha1(uniqid((string) rand(), true) . $cookie_key . $id_customer ), 0, 16));
            /**
             * Check if a default Categories Existe for this user
             */
            $condition = "id_customer='$id_customer'";
            $this->query->select('*');
            $this->query->from('wishlist');
            $this->query->where($condition);
            $ProductWished = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);
            // var_dump($ProductWished);
            $teste = false;
            foreach ($ProductWished as $value) {
                if((int)$value['default'] === 1){
                    $teste = true;
                }
            }
            
            if($teste){
                $ToWishlist = Db::getInstance()->insert(
                    'wishlist',
                    [   
                        'id_customer' => (int) $id_customer,
                        'token' => (string)$token,
                        'name' => (string)$name,
                        'date_add' => date('Y-m-d h:i:s'),
                        'date_upd' => date('Y-m-d h:i:s'),
                        'default' => 0,
                    ]
                );
            }else{
                $ToWishlist = Db::getInstance()->insert(
                    'wishlist',
                    [   
                        'id_customer' => (int) $id_customer,
                        'token' => (string)$token,
                        'name' => (string)$name,
                        'date_add' => date('Y-m-d h:i:s'),
                        'date_upd' => date('Y-m-d h:i:s'),
                        'default' => 1,
                    ]
                );
            }
        }
}

$wish = new Mywishlist();

//------------------------------ ANDRY ---------------------------------- //

if (isset($_GET['type']) && $_GET['type'] == "wishlist" && isset($_GET['IdCustomer'])) {
    $allwish = $wish->getAllWishlistsByIdCustomer($_GET['IdCustomer']);
    echo json_encode($allwish);
}
if (isset($_GET['type']) && $_GET['type'] == "WishlistByCategories") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    if (isset($_POST) && $_POST != null) {
        $id_wishlist =           $_POST['id_wishlist'];
        $wishlist = $wish->getAllWishlistsByIdCustomerAndId_wishlist($id_wishlist);
        echo json_encode($wishlist);
    }else{
        echo json_encode('ERROR');
    }
    
}
if (isset($_GET['type']) && $_GET['type'] == "ADD") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    if (isset($_POST) && $_POST != null) {
        $id_wishlist =           $_POST['id_wishlist'];
        $id_customer =           $_POST['id_customer'];
        $id_product  =           $_POST['id_product'];
        $id_product_attribute =  $_POST['id_product_attribute'];
        $quantity =              $_POST['quantity'];
        $Adding = $wish->ADD($id_wishlist,$id_customer,$id_product ,$id_product_attribute,$quantity);
        echo json_encode($Adding);
    }else{
        echo json_encode('ERROR');
    }
    
}
if (isset($_GET['type']) && $_GET['type'] == "ADD_CATEGORIES") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);

    if (isset($_POST) && $_POST != null) {
        $id_customer =           $_POST['id_customer'];
        $name =              $_POST['name'];
        $Adding = $wish->ADD_CATEGORIES($id_customer,$name);
        echo json_encode(true);
    }else{
        echo json_encode('ERROR');
    }
    
}

if (isset($_GET['type']) && $_GET['type'] == "REMOVE") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    if (isset($_POST) && $_POST != null) {
        $id_wishlist =           $_POST['id_wishlist'];
        $id_customer =           $_POST['id_customer'];
        $id_product  =           $_POST['id_product'];
        $id_product_attribute =  $_POST['id_product_attribute'];
        $removing = $wish->REMOVE($id_wishlist,$id_customer,$id_product ,$id_product_attribute);
        echo json_encode($removing);
    }else{
        echo json_encode('ERROR');
    }
    
}
if (isset($_GET['type']) && $_GET['type'] == "CheckProducts") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    if (isset($_POST) && $_POST != null) {
        $id_customer =           $_POST['id_customer'];
        $id_product  =           $_POST['id_product'];
        $check = $wish->CheckProducts($id_customer,$id_product);
        echo json_encode($check);
    }else{
        echo json_encode('ERROR');
    }
    
}

if (isset($_GET['type']) && $_GET['type'] == "wishlist_categories" && isset($_GET['IdCustomer'])) {
    $allwish = $wish->wishlistCategories($_GET['IdCustomer']);
    echo json_encode($allwish);
}

if (isset($_GET['type']) && $_GET['type'] == "SettingMenuIndicator") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    if (isset($_POST) && $_POST != null) {
        $id_customer =           $_POST['id_customer'];
        $check = $wish->SettingMenuIndicator($id_customer);
        
        echo json_encode($check);
    }else{
        echo json_encode('ERROR');
    }
    
}

