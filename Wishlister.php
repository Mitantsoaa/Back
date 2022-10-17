<?php  
class Wishlister {

        public $query ;
        public $Wish ;
        public $action ;
        /**
         * Import Wishlist Classes
         * and prestashop DBquery
         */
        public function __construct(){
             require('../config/config.inc.php');
             require('../modules/blockwishlist/classes/WishList.php');
            //  require('../modules/blockwishlist/controllers/front/action.php');
             $this->Wish = new WishList();
             $this->query = new DbQuery();
             $this->querySecond = new DbQuery();
            //  $this->action = new BlockWishListActionModuleFrontController();
        }

        /**
         * This will return all product wishlisted
         */
        public function getAllWishlist(){
                
                $this->query->select('*');
                $this->query->from('wishlist_product');
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);

            return $result ;

        }
        /**
         * This will return all Wislist that the used added
         * @param in $id_customer
         */
        public function getAllWishlistsByIdCustomer($id_customer){
            $arraylist = $this->Wish::getAllWishlistsByIdCustomer($id_customer);
            return  $arraylist ;
        }

        /**
         * Add product to ID wishlist
         *
         * @param int $id_wishlist
         * @param int $id_customer
         * @param int $id_product
         * @param int $id_product_attribute
         * @param int $quantity
         *
         * @return bool succeed
         */

        public function addProductToWishlist($id_wishlist, $id_customer, $id_product, $id_product_attribute, $quantity){
            $BooleanResult = $this->Wish::addProduct($id_wishlist, $id_customer, $id_product, $id_product_attribute, $quantity);
            return $BooleanResult ;
        }

        /**
         * Remove product from wishlist
         *
         * @param int $id_wishlist
         * @param int $id_customer
         * @param int $id_product
         * @param int $id_product_attribute
         *
         * @return bool
         */

        public function removeProductFromWishlist($id_wishlist, $id_customer, $id_product, $id_product_attribute){
            $BooleanResult = $this->Wish::removeProduct($id_wishlist, $id_customer, $id_product, $id_product_attribute);
            return $BooleanResult ;
        }

        /**
         * To check if product is in Wishlist
         */
        public function checkProducts($id_product){
            $condition = 'id_product='.$id_product ;
            $this->query->select('*');
            $this->query->from('wishlist_product');
            $this->query->where($condition);
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);
            $in ;
            if(empty($result)){
                $in = false ;
            }else{
                $in = true ;
            }
        return $in ;

        }


        /**
         * ADD A PRODUCTS TO TABELE  :  wishlist_product
         * 
         */
        public function ADD($id_wishlist=null, $id_customer, $id_product , $id_product_attribute, $quantity=null){
            
             $resulte =  $this->Wish::addProduct($id_wishlist, $id_customer, $id_product, $id_product_attribute, $quantity);
             
        }
        /**
         * ADD WISHLIST CATEGORIES
         * @param
         * 
         */
        public function ADD_CATEGORIES($id_customer,$name){

            /**
             * To Import Parametre,
             *  To get Cookie_key
             */

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
            var_dump($condition);
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
        /**
         * REMOVE WISHLIST CATEGORIES
         * @param
         * 
         */
        public function REMOVE_CATEGORIES($id_customer,$token){
            /**
             * Check if Categories Existe for this user
             */
            
            $condition = "id_customer='$id_customer' AND token='$token'";
            
            $this->query->select('*');
            $this->query->from('wishlist');
            $this->query->where($condition);
            $ProductWished = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);
            var_dump($ProductWished);
            
            foreach ($ProductWished as $value) {
                var_dump($value['token']);
                if($value['token'] == $token){
                    var_dump( (int)$value['id_wishlist']);
                    $id_wishlist = (int)$value['id_wishlist'] ;
                    Db::getInstance()->delete(
                        'wishlist_product',
                        "id_wishlist='$id_wishlist'"
                    );
                    var_dump( $token);
                    Db::getInstance()->delete(
                        'wishlist',
                        "token='$token'"
                    );
                }
            }
            
            
        }



        /**
         * UPDATE WISHLIST
         */

        public function UPDATE($id_customer,$id_product ,$id_product_attribute,$quantity=null){
            /**
             * Check products if already listed
             */
                // var_dump('id_customer : ' .$id_customer);
                // var_dump('id_product : ' .$id_product);
                // var_dump('id_product_attribute : '. $id_product_attribute);
                // var_dump('quantity : ' .$quantity);

                $condition000 = "id_product='$id_product' AND id_product_attribute='$id_product_attribute'";
                $this->query->select('*');
                $this->query->from('wishlist_product');
                $this->query->where($condition000);
                $ProductWished = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->query);
                

            /**
             * Check if a user wished it
             */ 
                if(!empty($ProductWished)){
                    
                    $id_wishlist = (int)$ProductWished[0]['id_wishlist'] ;
                    $condition111 = "id_wishlist='$id_wishlist' AND id_customer='$id_customer'";
                        $this->querySecond->select('*');
                        $this->querySecond->from('wishlist');
                        $this->querySecond->where($condition111);
                    $ProductWishedUser = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($this->querySecond);
                    /**
                     * Update TABLE : wishlist
                     *  @param date_upd 
                     * Update TABLE : wishlist_product 
                     *  @param date_upd 
                     *  @param quantity 
                     */
                    if(!empty($ProductWishedUser)){
                        Db::getInstance()->update('wishlist',[
                            'date_upd' => date('Y-m-d h:i:s'),
                        ],$condition111);
                        Db::getInstance()->update('wishlist_product',[
                            'quantity' => $quantity ,
                        ],$condition000);
                    }
                }

        }

        public function remove(){

        }
}


$wish = new Wishlister();
/**
 * ENDPOINT
 */
//--------------------------------------   ANDRY   ---------------------------------------//
if (isset($_GET['type']) && $_GET['type'] == "add") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    if (isset($_POST) && $_POST != null) {
        $id_wishlist =           $_POST['id_wishlist'];
        $id_customer =           $_POST['id_customer'];
        $id_product  =           $_POST['id_product'];
        $id_product_attribute =  $_POST['id_product_attribute'];
        $product_name =          $_POST['product_name'];
        $quantity =              $_POST['quantity'];
        $Adding = $wish->ADD($id_wishlist,$id_customer,$id_product ,$id_product_attribute,$product_name,$quantity);
        echo json_encode($Adding);
    }else{
        echo json_encode('ERROR');
    }
    
}
if (isset($_GET['type']) && $_GET['type'] == "add_categories") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    if (isset($_POST) && $_POST != null) {
        $id_customer =           $_POST['id_customer'];
        $id_product  =           $_POST['id_product'];
        $name =          $_POST['name'];
        $Adding = $wish->ADD_CATEGORIES($id_customer,$name);
        echo json_encode($Adding);
    }else{
        echo json_encode('ERROR');
    }
    
}
if (isset($_GET['type']) && $_GET['type'] == "remove_categories") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    if (isset($_POST) && $_POST != null) {
        $id_customer =           $_POST['id_customer'];
        $token  =           $_POST['token'];
        $Adding = $wish->REMOVE_CATEGORIES($id_customer,$token);
        echo json_encode($Adding);
    }else{
        echo json_encode('ERROR');
    }
    
}

if (isset($_GET['type']) && $_GET['type'] == "update") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);

    if (isset($_POST) && $_POST != null) {
        $id_customer =           $_POST['id_customer'];
        $id_product  =           $_POST['id_product'];
        $id_product_attribute =  $_POST['id_product_attribute'];
        $quantity =              $_POST['quantity'];
        $updating = $wish->UPDATE($id_customer,$id_product ,$id_product_attribute,$quantity);
        echo json_encode($updating);
    }else{
        echo json_encode('ERROR');
    }
    
}
//--------------------------------------   ANDRY   ---------------------------------------//
if (isset($_GET['type']) && $_GET['type'] == "getWishlist_Product") {
    $allwish = $wish->getAllWishlist();
    echo json_encode($allwish);
}
if (isset($_GET['type']) && $_GET['type'] == "tokenGenerator") {
    $token = $wish->tokenGenerator();
    echo json_encode($token);
}

if (isset($_GET['type']) && $_GET['type'] == "wishlist" && isset($_GET['IdCustomer'])) {
    $allwish = $wish->getAllWishlistsByIdCustomer($_GET['IdCustomer']);
    echo json_encode($allwish);
}
if (isset($_GET['type']) && $_GET['type'] == "Checkit" && isset($_GET['IdPrduct'])) {
    $allwish = $wish->checkProducts($_GET['IdPrduct']);
    echo json_encode($allwish);
}
if (isset($_GET['type']) && $_GET['type'] == "addwishlistwith") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);

    if (isset($_POST) && $_POST != null) {
        $id_wishlist =  $_POST['id_wishlist'];
        $id_customer =  $_POST['id_customer'];
        $id_product  =  $_POST['id_product'];
        $id_product_attribute =  $_POST['id_product_attribute'];
        $quantity =  $_POST['quantity'];
    }

    $allwish = $wish->addProductToWishlist($id_wishlist, $id_customer, $id_product, $id_product_attribute, $quantity);
    echo json_encode($allwish);
}

if (isset($_GET['type']) && $_GET['type'] == "removeProductFromWishlist") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);

    if (isset($_POST) && $_POST != null) {
        $id_wishlist =  $_POST['id_wishlist'];
        $id_customer =  $_POST['id_customer'];
        $id_product  =  $_POST['id_product'];
        $id_product_attribute =  $_POST['id_product_attribute'];
    }

    $allwish = $wish->removeProductFromWishlist($id_wishlist, $id_customer, $id_product, $id_product_attribute);
    echo json_encode($allwish);
}