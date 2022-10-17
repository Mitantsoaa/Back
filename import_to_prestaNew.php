<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require_once('./PSWebServiceLibrary.php');
    
require('../config/config.inc.php');
require('../app/config/parameters.php');

class Webservice_presta
{

    public $table_name = 'structure_catalogue';

    public $conn;

    public $webserv;

    public $query;

    public $DEBUG = false;

    //public $PS_SHOP_PATH = 'https://www.ow.randev.ovh/';
    public $PS_SHOP_PATH = 'https://xpressfood-rijaniony.com/';

    public $PS_WS_AUTH_KEY = 'JQVXEF6FMXSYHBUV5SWM212MQBQT9ECG';

    // public $querySecond;

    public function __construct()

    {

        // define('DEBUG', false);

        // define('PS_SHOP_PATH', 'http://192.168.1.101/passioncampagne/');

        // define('PS_WS_AUTH_KEY', 'EM5XBQQQDTIVEPBE9XELRHNNDI75ZN5I');

        $this->query = new DbQuery();
        $this->querySecond = new DbQuery();

        $this->webserv = new PrestaShopWebservice($this->PS_SHOP_PATH, $this->PS_WS_AUTH_KEY, $this->DEBUG);
    }

// get notif

    public function getNotifications()
    {
        // $opt['resource'] = 'notification';

        // $opt['filter']['active'] = 1;

        // $opt['display'] = 'full';

        // $xmlResponse = $this->webserv->get($opt);

        // $allnotifications = $xmlResponse->children()->children();

        // $allnotifications = $this->webserv->url;

        // return ($allnotifications);
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('notification');
        return Db::getInstance()->executeS($sql);
    }
// ////////////////////

    public function getAllproducts()
    {

        $opt['resource'] = 'products';

        $opt['filter']['active'] = 1;

        $opt['display'] = 'full';

        $xmlResponse = $this->webserv->get($opt);
        

        $allproducts = $xmlResponse->children()->children();

        $temp_allproducts = array();
        $temp_allproducts2 = array();

        foreach ($allproducts as $key => $value) {
            // var_dump($value);
            array_push($temp_allproducts, $value);
        }

        krsort($temp_allproducts);
        // var_dump($temp_allproducts);
        foreach ($temp_allproducts as $key => $value) {
            // var_dump($value);
            array_push($temp_allproducts2, $value);
        }

        // var_dump($temp_allproducts2);
        return (array('product' => $temp_allproducts2));
        // return($allproducts);
    }
    public function getAllproductsBrut()
    {
        $opt['resource'] = 'products';

        $opt['filter']['active'] = 1;

        $opt['limit'] = 5;

        $opt['display'] = 'full';

        $xmlResponse = $this->webserv->get($opt);

        $allproducts = $xmlResponse->children()->children();

        return ($allproducts);
    }
    public function getAllproductspromo()
    {
        $opt['resource'] = 'products';

        $opt['filter']['active'] = 1;
        $opt['filter']['on_sale'] = 1;

        // $opt['limit'] = 5;

        $opt['display'] = 'full';

        $xmlResponse = $this->webserv->get($opt);

        $allproducts = $xmlResponse->children()->children();

        return ($allproducts);
    }
    public function getLatestProducts()
    {
        $opt['resource'] = 'products';

        $opt['filter']['active'] = 1;
        $opt['sort'] = '[id_DESC]';
        $opt['limit'] = 15;

        $opt['display'] = 'full';

        $xmlResponse = $this->webserv->get($opt);

        $allproducts = $xmlResponse->children()->children();

        return ($allproducts);
    }
    public function getProductsManufacturer()
    {
        $opt['resource'] = 'manufacturers';

        $opt['filter']['active'] = 1;
        $opt['sort'] = '[name_ASC]';
        // $opt['limit'] = 15;

        $opt['display'] = 'full';

        $xmlResponse = $this->webserv->get($opt);

        $allproducts = $xmlResponse->children()->children();

        return ($allproducts);
    }
    public function getImage($id_products)
    {   

        $opt['resource'] = 'products';

        $opt['filter']['id'] = $id_products;

        $opt['limit'] = 1;

        // $opt['display'] = 'full';

        $opt['display'] = '[id_default_image]';

        $xmlResponse = $this->webserv->get($opt);

         $xxx = (string)($xmlResponse->products->product->id_default_image->attributes('xlink', true)->href) ;

         var_dump($xxx);

        $auth = base64_encode($this->PS_WS_AUTH_KEY. ":");


        header('Content-type: image/jpeg;');
        $p = $xxx ;
        $a = file_get_contents($p);
        echo $a;

        $username = $this->PS_WS_AUTH_KEY;
        $password = '';
         
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode("$username:")
            )
        ));
        $data = file_get_contents($xxx, false, $context);

        echo $data ;

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $xxx ,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic ". $auth
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo($response);



        $id_default_image = (string)($xmlResponse->children()->children()->product->id_default_image);

        
        $resource = 'api/images/products/' . $id_products ;

        $link = $this->PS_SHOP_PATH . $resource .'/' ;

        $Images_link =  array("id_default_image" => ($link . $id_default_image ));

        // var_dump($Images_link);

        // $opt2['resource'] = $resource ;

        // $opt2['display'] = 'full';

        // $xmlResponse2 = $this->webserv->get($opt2);

        // return ($xmlResponse2);
    }

    // ProductsListByIdCategories
    public function ProductsListByIdCategories($IdCategories, $limit=''){
        $opt['resource'] = 'products';
        $opt['filter']['id_category_default'] = $IdCategories;
        $opt['filter']['active'] = 1;
        $opt['display'] = 'full';
        if($limit == ''){
            $opt['limit'] = 30;
        }
        Db::getInstance()->executeS('SELECT * FROM ps_product');
        $xmlResponse = $this->webserv->get($opt);
        var_dump($xmlResponse);die;
        $parent = $xmlResponse->children()->children();
        return $parent ;
    }

    // ProductsListByIdCategories_limited
    public function ProductsListByIdCategories_limited($IdCategories, $limit=''){
        $opt['resource'] = 'products';
        $opt['filter']['id_category_default'] = intval($IdCategories);
        $opt['filter']['active'] = 1;
        $opt['display'] = 'full';
        if($limit == ''){
            $opt['limit'] = 30;
        }
        $this->query->select('*')->from('ps_product');
        $xmlResponse = $this->webserv->get($opt);
        $parent = $xmlResponse->children()->children();
        return($parent);
    }

    public function HomeOrganizer(){
            $titre =  array( 
                array( "SousSousSousTitre" => "Chemise de chasse" , "IdCategorie" => 114) ,
                array( "SousSousSousTitre" => "Veste chasse grand froid" ,"IdCategorie" => 149) ,
                array( "SousSousSousTitre" => "Pantalon chasse cuir","IdCategorie" => 192),
            );
            $list =  array("Grand_Titre" => "VETEMENT" ,"Sous_Categories" => $titre );
            
            $titre2 =  array( 
                array( "SousSousSousTitre" => "Bottes chasses exterieur cuir" , "IdCategorie" => 154) ,
                array( "SousSousSousTitre" => "Chaussures chasse chaudes" ,"IdCategorie" => 102) ,
                array( "SousSousSousTitre" => "Bottes enfant","IdCategorie" => 184),
            );
            $list2 =  array("Grand_Titre" => "CHAUSSURE & BOTTES" ,"Sous_Categories" => $titre2 );
            $titre3 =  array( 
                array( "SousSousSousTitre" => "Amortisseur de recul" ,"IdCategorie" => 64) ,
                array( "SousSousSousTitre" => "Etui","IdCategorie" => 42) 
                // array( "SousSousSousTitre" => "Gilet de protection pour chiens","IdCategorie" => 79)
            );
            $list3 =  array("Grand_Titre" => "EQUIPEMENT & ACCESSOIRES" ,"Sous_Categories" => $titre3 );
            $the_big_list = array($list,$list2,$list3) ;
            return $the_big_list ;
        }

    public function getCategoriesChildren($id_parent){
            // var_dump($id_parent);
            $opt['resource'] = 'categories';
            $opt['filter']['id_parent'] = intval($id_parent);
            //$opt['filter']['id'] = !$id_parent;
            $opt['display'] = '[id,id_parent,name]';

            $xmlResponse = $this->webserv->get($opt);
            $parent = $xmlResponse->children()->children();
            return($parent);
        }
    public function getCategories()
    {   
        $opt['resource'] = 'categories';
        $opt['filter']['id'] = 2;
        $opt['display'] = 'full'; 
        $xmlResponse = $this->webserv->get($opt);
        $parent2 = $xmlResponse->children()->children()->category->associations->categories;

        $i = 0 ;
        $arrayL =  array();

        while ( $parent2->category[$i] != null) {
           array_push($arrayL,($parent2->category[$i]));
           $i++;
        }

        $ht = '' ;
        foreach ($arrayL as $key => $value) {
            $temps = (string)($value->id);
            $ht .= $temps . '|';
        }
        $idCateg = '['. $ht . ']';
        $opt2['resource'] = 'categories';
        $opt2['filter']['id'] = $idCateg ;
        $opt2['display'] = '[id,name]'; 
        $xmlResponse2 = $this->webserv->get($opt2);

        $parent3 = $xmlResponse2->children()->children();

        $reponse = array();;

        foreach ($parent3 as $value) {
            $id_parent = $value->id ;
            $id_parent_name = $value->name->language ;
            //var_dump($id_parent_name);
            $children = $this->getCategoriesChildren($id_parent);

            if (empty($children)) {
                $object = array('Parent' => $id_parent_name,'children' => 'vide');
            }else{
                $object = array('Parent' => $id_parent_name,'children' => $children);
            }

            
            // $object = (object) [strval($id_parent_name) => $children];

            array_push($reponse, $object);

        }
        return($reponse);

    }
    public function Search($query,$language)
    {

        if(parse_url($query, PHP_URL_SCHEME) === 'https' || parse_url($query, PHP_URL_SCHEME) === 'http' ){

            $PHP_URL_PATH = str_replace(["/","-",".","=","_","%"], " ", parse_url($query, PHP_URL_PATH));
            $PHP_URL_QUERY = str_replace(["/","-",".","=","_","%"], " ", parse_url($query, PHP_URL_QUERY));
            $PHP_URL_FRAGMENT = str_replace(["/","-",".","=","_","%"], " ", parse_url($query, PHP_URL_FRAGMENT));

            $the_string_to_search = $PHP_URL_PATH . $PHP_URL_QUERY . $PHP_URL_FRAGMENT ; 

        }else {

            $the_string_to_search = $query;

        }

        

        $opt = array('resource' => 'search' ,'query' =>  rawurlencode($the_string_to_search) ,'language' => $language);

        $xmlResponse = $this->webserv->get($opt);

        $allresult = $xmlResponse->children()->children();

        $arraytmp = array();

        foreach ($allresult as $key => $value) {
            $valeur = $this->xml_attribute($value, 'id');
            array_push($arraytmp, $valeur);
        }
        $somme = '' ;
        

        foreach ($arraytmp as  $value) {
            $somme .= $value . '|' ;
        }
        // var_dump($somme);

        $idProduct = '['. $somme .']';
        if ($somme != '') {
            $productsListResultofSearch =  $this -> getProductById($idProduct);
            return($productsListResultofSearch);
        }
        
         // 4756 1599
        

       //  var_dump($arraytmp);
       //  var_dump($productsListResultofSearch);
        // 1599  4756
    
    }

    public function getCategoryById($id_category) {
        $opt['resource'] = 'categories';
        $opt['display'] = 'full';
        $opt['filter']['id'] = $id_category;
        $opt['filter']['active'] = 1;
        $xmlResponse = $this->webserv->get($opt);
        $taxeMethode = $xmlResponse->children()->children();
        //var_dump($taxeMethode); die('stop');
        return $taxeMethode->category;
    }

    public function getProductById($idProduct)
    {
        $opt['resource'] = 'products';
        $opt['filter']['active'] = 1;
        $opt['filter']['id'] = $idProduct;
        $opt['display'] = 'full';
         // var_dump($idProduct);
         //var_dump($opt);
        $xmlResponse = $this->webserv->get($opt);
        //var_dump(json_encode($xmlResponse));
        $allproducts['product'] = $xmlResponse->children()->children();
         //var_dump($allproducts);
        // die();
        $opt2['resource'] = 'specific_prices';
        $opt2['filter']['id_product'] = $idProduct;
        $opt2['display'] = 'full';
        $xmlResponsePrice = $this->webserv->get($opt2);

        $allproducts['product'] = $xmlResponse->children()->children();
        $allproducts['prices'] = $xmlResponsePrice->children()->children();
        // var_dump($allproducts['product']->product->description->language);
        // die();
        /*$allproducts['product']->product->description->language = strip_tags($allproducts['product']->product->description->language[1]);
         var_dump ( $allproducts['product']->product->description->language );die;
        $allproducts['product']->product->description_short->language = strip_tags($allproducts['product']->product->description_short->language[0]);
*/
        $allproductOptionsGroups = $this->getAllProductOptions();
        $productOption = $allproducts;
        $optionsProd = $productOption['product']->product->associations->product_option_values;
        $array_optionsProduct = array();
        foreach ($optionsProd->product_option_value as $opt) {
            array_push($array_optionsProduct, strval($opt->id));
        }
        $arrayOpt = array();
        $arraych1 = array();
        foreach ($allproductOptionsGroups->product_option as $allProdOptG) {
            foreach ($allProdOptG->associations->product_option_values->product_option_value as $opts) {
                if (in_array(strval($opts->id), $array_optionsProduct)) {
                    if (!in_array(strval($allProdOptG->id), $arraych1)) {
                        array_push($arrayOpt, $allProdOptG);
                        array_push($arraych1, strval($allProdOptG->id));
                    }
                }
            }
        }
        $newArrOpt = $arrayOpt;
        foreach ($arrayOpt as $key => $value) {
            $i = 0;
            foreach ($arrayOpt[$key]->associations->product_option_values->product_option_value as $secondVal) {
                if (in_array(strval($secondVal->id), $array_optionsProduct)) {
                    $product_option_value_Full = $this->getOptionValuesById(strval($secondVal->id));
                    $newArrOpt[$key]->associations->product_option_values->product_option_value_T[$i]->id = $product_option_value_Full->product_option_value->id;
                    $newArrOpt[$key]->associations->product_option_values->product_option_value_T[$i]->id_attribute_group = $product_option_value_Full->product_option_value->id_attribute_group;
                    $newArrOpt[$key]->associations->product_option_values->product_option_value_T[$i]->position = $product_option_value_Full->product_option_value->position;
                    $newArrOpt[$key]->associations->product_option_values->product_option_value_T[$i]->name = $product_option_value_Full->product_option_value->name->language;
                    $i++;
                }
            }
        }
        $allproducts['declinaison'] = $newArrOpt;
        return ($allproducts);
    }

    public function getCombination($id)
    {
        $opt['resource'] = 'combinations';
        $opt['filter']['id'] = $id;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts['product'] = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function getCombinationByOptions($idProduct)
    {
        $opt['resource'] = 'combinations';
        $opt['filter']['id_product'] = $idProduct;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function getAllProductOptions()
    {
        $opt['resource'] = 'product_options';
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function getAllProductOptionsValuesByIdProducts($idProducts)
    {
        $opt['resource'] = 'product_options';
        $opt['filter']['id_product'] = $idProducts;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function getOptionValuesById($id)
    {
        $opt['resource'] = 'product_option_values';
        $opt['filter']['id'] = $id;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    // public function getSpecificPrices($idProduct, $quantity)
    // {
    //     $opt['resource'] = 'specific_prices';
    //     $opt['filter']['id_product'] = $idProduct;
    //     // $opt['filter']['from_quantity'] = $quantity;
    //     $opt['display'] = 'full';
    //     $xmlResponse = $this->webserv->get($opt);
    //     $allproducts = $xmlResponse->children()->children();
    //     return ($allproducts);
    // }

    public function getSpecificPrices($idComb,$idProduct, $quantity, $method)
    {
        $xml = $this->webserv->get(array('url' => $this->PS_SHOP_PATH.'/api/products/'.$idProduct.'?price[my_price][product_attribute]='.$idComb.'&price[my_price][use_reduction]=0&price[my_price][use_tax]='.$method.'&price[my_price][quantity]='.$quantity));
        // $xml = $this->webserv->get(array('url' => $this->PS_SHOP_PATH.'/api/products/'.$idProduct.'?price[my_price][product_attribute]='.$idComb.'&price[my_price][use_reduction]=1&price[my_price][quantity]='.$quantity));
        return $xml;
    }
    

    public function AddToCart($id_product, $product_attribute, $quantity, $guest,$idCustomer,$action)
    {
        // var_dump($product_attribute);die('valeur du var dump');
        $Allcarts = $this->getCartByGuestCustomerGet($guest, $idCustomer);
        if($Allcarts != null){
            $currentCart = $Allcarts->children()->children();
            $allPreviousRow = $currentCart->associations->cart_rows;
            $update = 0;
            $b = 0;
            for ( $a= 0;$a< sizeOf($allPreviousRow->cart_row);$a++){
                if(strval($allPreviousRow->cart_row[$a]->id_product) == $id_product && strval($allPreviousRow->cart_row[$a]->id_product_attribute) == $product_attribute ){
                    if($action == "delete"){
                        unset($allPreviousRow->cart_row[$a]);
                    }elseif($action == "update"){
                        $allPreviousRow->cart_row[$a]->quantity = (int)$allPreviousRow->cart_row[$a]->quantity+(int)$quantity;
                        $allPreviousRow->cart_row[$a]->id_address_delivery = strval($currentCart->id_address_delivery);
                    }
                    $update = 1;
                }
                $b ++;
            }
            if($update == 0){
                $allPreviousRow->cart_row[$b]->id_product = $id_product;
                $allPreviousRow->cart_row[$b]->id_product_attribute = $product_attribute;
                $allPreviousRow->cart_row[$b]->id_address_delivery = strval($currentCart->id_address_delivery);
                $allPreviousRow->cart_row[$b]->quantity = $quantity;
            }
            $updatedXml = $this->webserv->edit([
                'resource' => 'carts',
                'id' => strval($currentCart->id),
                'putXml' => $Allcarts->asXML(),
            ]);
            return true;
        }else{
            $opt['schema'] = 'blank';
            $opt['resource'] = "carts";
            $xmlResponse = $this->webserv->get($opt);
            $cartsBlank = $xmlResponse->children()->children();
            $cartsBlank->id_currency = 0;
            $cartsBlank->id_lang = 1;
            $cartsBlank->id_guest = $guest;
            $cartsBlank->id_customer = $idCustomer;
            $boucleProduct = $cartsBlank->associations->cart_rows->cart_row;
            $boucleProduct->id_product = $id_product;

            if($product_attribute != 0){
                $boucleProduct->id_product_attribute = $product_attribute;
            }
            if($id_product != 0){
                $boucleProduct->id_product = $id_product;
            }

            $boucleProduct->quantity = $quantity;

            $opts['resource'] = "carts";
            $opts['postXml'] = $xmlResponse->asXML();
            $xmlResponse = $this->webserv->add($opts);
            return true;            
        }
    }

    public function saveGuest()
    {
        $os = 9;
        $wb = 11;
        $opt['schema'] = 'blank';
        $opt['resource'] = "guests";
        $xmlResponse = $this->webserv->get($opt);
        $guestsBlank = $xmlResponse->children()->children();
        $guestsBlank->id_operating_system = $os;
        $guestsBlank->id_web_browser = $wb;

        $opts['resource'] = "guests";
        $opts['postXml'] = $xmlResponse->asXML();
        $xmlResponse = $this->webserv->add($opts);
        return $xmlResponse;
    }

    public function getCartByGuestCustomer($idGuest, $idCustomer)
    {
        $opt['resource'] = 'carts';
        if ($idGuest != 0) {
            $opt['filter']['id_guest'] = $idGuest;
        }
        // if ($idCustomer != 0) {
        //     $opt['filter']['id_customer'] = $idCustomer;
        // }
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function getCartIdByGuest($idGuest)
    {
        $opt['resource'] = 'carts';
        $opt['filter']['id_guest'] = $idGuest;
        $opt['display'] = '[id]';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function getCartIdByCustomer($idCustomer)
    {
        $opt['resource'] = 'carts';
        $opt['filter']['id_customer'] = $idCustomer;
        $opt['display'] = '[id]';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function getCartById($idCart)
    {
        $xml = $this->webserv->get([
            'resource' => "carts",
            'id' => $idCart, // Here we use hard coded value but of course you could get this ID from a request parameter or anywhere else
        ]);
        return $xml;
    }

    public function getCartByGuestCustomerGet($idGuest, $idCustomer)
    {
        $opt['resource'] = 'carts';
        if ($idGuest != 0) {
            $opt['filter']['id_guest'] = $idGuest;
        }
        // if ($idCustomer != 0) {
        //     $opt['filter']['id_customer'] = $idCustomer;
        // }
        $opt['display'] = '[id]';
        $xmlResponse = $this->webserv->get($opt);
        if(!empty($xmlResponse->carts)){
            $idcart = strval($xmlResponse->children()->children()->cart->id);
            if($idcart !="" && $idcart !=0){
                $xmlResponse = $this->getCartById($idcart);
            }else{
                return null;
            }
            return $xmlResponse;
        }else{
            return null;
        }
    }

    public function checkIsExists($totest, $arrtested)
    {
        $exists = false;
        foreach ($totest as  $key => $value) {
            if ($totest[$key]['id_product'] == $arrtested['id_product']) {
                $newNb = (int)$totest[$key]['nb'] + (int)$arrtested['nb'];
                $totest[$key]['nb'] = strval($newNb);
                $exists = true;
            }
        }

        if ($exists == false) {
            array_push($totest, $arrtested);
        }

        return $totest;
    }

    public function deletCartById($idCart)
    {
        try {
            $this->webserv->delete([
                'resource' => 'carts',
                'id' => $idCart, // Here we use hard coded value but of course you could get this ID from a request parameter or anywhere else
            ]);
            return "ok";
        } catch (PrestaShopWebserviceException $e) {
            return "ko";
        }
    }

    public function getSimpleProductInfo($idProd)
    {
        $opt['resource'] = 'products';
        $opt['filter']['id'] = $idProd;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function createCustomer($arrays)
    {
        if (count($arrays) != 0) {
            $passwd = $arrays['password'];
            $nom = $arrays['nom'];
            $prenom = $arrays['prenom'];
            $email = $arrays['email'];
            $gender = $arrays['gender'];
        }
        $opts['resource'] = 'customers';
        $opts['schema'] = 'blank';
        $xml = $this->webserv->get($opts);
        // Adding dinamic values
        // Required
        $xml->customer->passwd              = $passwd;
         $xml->customer->lastname            = $nom;
        $xml->customer->firstname           = $prenom;
        $xml->customer->email               = $email;
       $xml->customer->id_gender           = $gender;
        $xml->customer->active           = 1;
        $xml->customer->id_default_group           = 3;
        $xml->customer->id_default_group           = 3;
        $xml->customer->associations->groups->group->id = 3;
        // Others

        // $xml->customer->associations->groups->group[0]->id = $id_group; // customers

        // Adding the new customer
        $opt = array('resource' => 'customers');
        $opt['postXml'] = $xml->asXML();
        $xml = $this->webserv->add($opt);
        $id_customer = $xml->customer;
        return $id_customer;
    }

    public function validateCart($cartOne, $idCustomer)
    {
        $address = $this->getAdressByIdCustomer($idCustomer);
        $customerFields = $cartOne->children()->children();
        $customerFields->id_customer = $idCustomer;
        $customerFields->id_address_delivery = $address->address->id;
        for($n=0;$n<sizeOf($customerFields->associations->cart_rows->cart_row);$n++){
            $customerFields->associations->cart_rows->cart_row[$n]->id_address_delivery = $address->address->id ;
            $customerFields->associations->cart_rows->cart_row[$n]->id_address_delivery = $address->address->id ;
        }
        $id = strval($customerFields->id);
        $updatedXml = $this->webserv->edit([
            'resource' => 'carts',
            'id' => $id,
            'putXml' => $cartOne->asXML(),
        ]);
        $customerFields = $updatedXml->cart->children();
        return $customerFields;
    }

    public function getStates($idCountry)
    {
        $opt['resource'] = 'states';
        $opt['filter']['active'] = 1;
        if ($idCountry != 0) {
            $opt['filter']['id_country'] = $idCountry;
        }
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function getCountries()
    {
        $opt['resource'] = 'countries';
        $opt['filter']['active'] = 1;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts = $xmlResponse->children()->children();
        return ($allproducts);
    }

    public function saveAdresse($arrays)
    {
        if (count($arrays) != 0) {

            $id_customer = $arrays['id_customer'];
            $id_country = $arrays['id_country'];
            $id_state = $arrays['id_state'];
            $lastname = $arrays['lastname'];
            $firstname = $arrays['firstname'];
            $address1 = $arrays['address1'];
            $address2 = $arrays['address2'];
            $postcode = $arrays['postcode'];
            if($arrays['alias']){
                $alias = $arrays['alias'];
            }else{
                $alias = "Mon addresse";
            }
            $city = $arrays['city'];
            $phone = $arrays['phone'];
            
            $opts['resource'] = 'addresses';
            $opts['schema'] = 'blank';
            $xml = $this->webserv->get($opts);

            $xml->address->id_customer = $id_customer;
            $xml->address->id_country = $id_country;
            $xml->address->id_state = $id_state;
            $xml->address->lastname = $lastname;
            $xml->address->firstname = $firstname;
            $xml->address->address1 = $address1;
            $xml->address->address2 = $address2;
            $xml->address->postcode = $postcode;
            $xml->address->city = $city;
            $xml->address->phone = $phone;
            $xml->address->alias = $alias;
            // echo json_encode($xml);
            // die();
            $opt = array('resource' => 'addresses');
            $opt['postXml'] = $xml->asXML();
            $xml = $this->webserv->add($opt);
            $adresse = $xml->address;
            return $adresse;
        }
    }

    public function test(){
        $opts['resource'] = 'addresses';
            $opts['schema'] = 'blank';
            $xml = $this->webserv->get($opts);
            var_dump($xml);
    }
    public function getAdressByIdCustomerAndClear($idCustomer){
        $opt['resource'] = 'addresses';
        $opt['filter']['id_customer'] = $idCustomer;
        $opt['display'] = '[id]';
        $xmlResponse = $this->webserv->get($opt);
        $allAdressses = $xmlResponse->children()->children();
        $addrAll = $allAdressses->address;
        if(count($addrAll) > 1){
            foreach($addrAll as $addrs){
                $this->webserv->delete([
                    'resource' => 'addresses',
                    'id' => strval($addrs->id), // Here we use hard coded value but of course you could get this ID from a request parameter or anywhere else
                ]);
            }
        }else{
            $this->webserv->delete([
                'resource' => 'addresses',
                'id' => strval($addrAll->id), // Here we use hard coded value but of course you could get this ID from a request parameter or anywhere else
            ]);
        }
    }

    public function getAdressByIdCustomer($idCustomer){
        $opt['resource'] = 'addresses';
        $opt['filter']['id_customer'] = $idCustomer;
        $opt['filter']['deleted'] = false;
        $opt['limit'] = 1;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allAdressses = $xmlResponse->children()->children();
        $addrAll = $allAdressses;
        return $addrAll;
    }

    public function makeOrder($customerId,$addressId,$idCart,$products){
        
            $id['customer'] = $customerId;
            $id['address'] = $addressId;            
            $id['cart'] = $idCart;
            $xml = $this->webserv->get(array('url' => $this->PS_SHOP_PATH.'/api/orders?schema=blank'));

            $xml->order->id_customer = $id['customer'];

            $xml->order->id_address_delivery = $id['address'];

            $xml->order->id_address_invoice = $id['address'];

            $xml->order->id_cart = $id['cart'];

            $xml->order->id_currency = $id['currency'];

            $xml->order->id_lang = $id['lang'];

            $xml->order->id_carrier = 3;

            $xml->order->current_state = 3;

            $xml->order->valid = 0;

            $xml->order->payment = 'Cash on delivery';

            $xml->order->module = 'cashondelivery';

            $xml->order->total_paid = $products['total'];

            $xml->order->total_paid_tax_incl = $products['total'];

            $xml->order->total_paid_tax_excl = $products['total'];

            $xml->order->total_paid_real = '0';

            $xml->order->total_products = $products['total'];

            $xml->order->total_products_wt = $products['total'];

            $xml->order->conversion_rate = '1';

            $xml->order->associations->order_rows->order_row->product_id = $products['id'];

            $xml->order->associations->order_rows->order_row->product_quantity = $products['quantity'];

            $opt = array('resource' => 'orders');

            $opt['postXml'] = $xml->asXML();

            $xml = $this->webserv->add($opt);

 

            $id['order'] = $xml->order->id;

            $id['secure_key'] = $xml->order->secure_key;

 

            $xml = $this->webserv->get(array('url' => $this->PS_SHOP_PATH.'/api/order_histories?schema=blank'));

 

            $xml->order_history->id_order = $id['order'];

            $xml->order_history->id_order_state = '3';

 

            $opt = array('resource' => 'order_histories');

            $opt['postXml'] = $xml->asXML();

            $xml = $this->webserv->add($opt);
    }

    public function login($email,$password){
        
        $opt['resource'] = 'customers';
        $opt['filter']['email'] = $email;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);

        $allproducts = $xmlResponse->children()->children();

        $x = (string)($allproducts->customer->passwd);

        if (password_verify($password, $x)) {
            return $allproducts;
        } else {
            return false;
        }
    }

    public function getShopInfos($idShop){
        $opt['resource'] = 'shops';
        $opt['filter']['id'] = $idShop;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $infoshop = $xmlResponse->children()->children();
        return $infoshop;
    }

    public function getConfig($configName){
        $opt['resource'] = 'configurations';
        $opt['filter']['name'] = $configName;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $infoshop = $xmlResponse->children()->children();
        return $infoshop;
    }

        // ------------ Michel ----------------------- Start
        public function getCustomerById($idCustomer){
            $opt['resource'] = 'customers';
            $opt['filter']['id'] = $idCustomer;
            $opt['display'] = 'full';
            $xmlResponse = $this->webserv->get($opt);
            $customer = $xmlResponse;
            return $customer;
        }
    
        public function editCustomer($customer, $array){
            $customerXml = $customer->children();
            $customerFields = $customerXml->children()->children();
            if(password_verify($array['passwd'], $customerFields->passwd)){
                $customerFields->firstname = $array['firstname'];
                $customerFields->lastname = $array['lastname'];
                $customerFields->email = $array['email'];
                $customerFields->birthday = $array['birthday'];
                if(isset($array['new_passwd']) && $array['new_passwd'] != null && $array['new_passwd'] != ""){
                    $customerFields->passwd = $array['new_passwd'];
                }
                $updatedXml = $this->webserv->edit([
                    'resource' => 'customers',
                    'id' => (int)$customerFields->id,
                    'putXml' => $customerXml->asXML(),
                ]);
                $customerFields = $updatedXml->children();
                return $customerFields;
            }else{
                return false;
            }
    
        }
    
        public function getAllAdressByIdCustomer($idCustomer){
            $opt['resource'] = 'addresses';
            $opt['filter']['id_customer'] = $idCustomer;
            $opt['filter']['deleted'] = false;
            $opt['display'] = 'full';
            
            $xmlResponse = $this->webserv->get($opt);
            $allAdresses = $xmlResponse->children()->children();
            // get country
            foreach($allAdresses as $value){
                $idCountry = (string)($value->id_country);
                $country = $this->getCountryById($idCountry);
                $value->country = $country->country->name->language;
                $value->id_zone_country = $country->country->id_zone;
                foreach($value as $key => $item){
                    if(empty($value->$key)){
                        $value->$key = "null";
                    }
                }
            }
            $addrAll = $allAdresses;
            return $addrAll;
        }

        public function getCountryById($id){
            $opt['resource'] = 'countries';
            $opt['filter']['id'] = $id;
            $opt['display'] = 'full';

            $xmlResponse = $this->webserv->get($opt);
            $country = $xmlResponse->children()->children();
            return $country;
        }
    
        public function getAdressById($id){
            $opt['resource'] = 'addresses';
            $opt['filter']['id'] = $id;
            $opt['display'] = 'full';
            $xmlResponse = $this->webserv->get($opt);
            $adressXml = $xmlResponse->children();
            return $adressXml;
        }
    
        public function deleteAdressById($id){
            $adress = $this->getAdressById($id);
    
            $adressFields = $adress->children()->children();
            $adressFields->deleted = "1";
            try{
                $updatedXml = $this->webserv->edit([
                    'resource' => 'addresses',
                    'id' => (int)$adressFields->id,
                    'putXml' => $adress->asXML(),
                ]);
                $edited_adress = $updatedXml->children();
                return $edited_adress;
            }catch(Exception $e){
                return false;
            }
    
        }
    
        public function editAdress($id, $array){
            $adress = $this->getAdressById($id);
            $adressFields = $adress->children()->children();
    
            $adressFields->firstname = $array['firstname'];
            $adressFields->lastname = $array['lastname'];
            $adressFields->address1 = $array['address1'];
            $adressFields->city = $array['city'];
            $adressFields->postcode = $array['postcode'];
            $adressFields->id_country = $array['id_country'];
    
            if(isset($array["alias"])){
                $adressFields->alias = $array['alias'];
            }
            if(isset($array["phone"])){
                $adressFields->phone = $array['phone'];
            }
            if(isset($array["company"])){
                $adressFields->company = $array['company'];
            }
            if(isset($array["address2"])){
                $adressFields->address2 = $array['address2'];
            }
            if(isset($array["id_state"])){
                $adressFields->id_state = $array['id_state'];
            }
    
            try{
                $updatedXml = $this->webserv->edit([
                    'resource' => 'addresses',
                    'id' => (int)$adressFields->id,
                    'putXml' => $adress->asXML(),
                ]);
                $edited_adress = $updatedXml->children();
                return $edited_adress;
            }catch(Exception $e){
                return false;
            }
        }
    
        public function valide_form_edit($array){
            if(!filter_var($array['email'], FILTER_VALIDATE_EMAIL)) {
                return false;
            }
    
            if($array['firstname'] != ""){
                return false;
            }
    
            if($array['lastname'] != ""){
                return false;
            }
    
            return true;
        }

        public function get_stock_by_id_product($id, $id_product_attribute){
            $opt['resource'] = 'products';
            $opt['filter']['active'] = 1;
            $opt['filter']['id'] = $id;
            $opt['display'] = 'full';
            $xmlResponse = $this->webserv->get($opt);
            
            $product = $xmlResponse->children()->children();
            $product_attribute = json_decode(json_encode($product->product->associations->stock_availables));
            $id_attribute = "";
            if(is_array($product_attribute->stock_available)){
                foreach($product_attribute->stock_available as $attr){
                    if($attr->id_product_attribute == $id_product_attribute){
                        $id_attribute = $attr->id;
                    }
                }
            }else{
                $id_attribute = $product_attribute->stock_available->id;
            }
            $opt2['resource'] = 'stock_availables';
            $opt2['filter']['id'] = $id_attribute;
            $opt2['display'] = "full";
            $xmlStockResponse = $this->webserv->get($opt2);
            $stock = $xmlStockResponse->children()->children();
            return $stock;
        }

        public function edit_cart($id_cart, $data){
            $xml_response = $this->getCartById($id_cart);
            $cart = $xml_response->children()->children();
            $cart_rows = $cart->associations->cart_rows;
            $i = 0;
            foreach($cart_rows->cart_row as $key => $row){
                $row->quantity = $data[$i]['quantity'];
                $i++;
            }
            try{
                $updatedXml = $this->webserv->edit([
                    'resource' => 'carts',
                    'id' => strval($id_cart),
                    'putXml' => $xml_response->asXML(),
                ]);
                return $updatedXml;
            }catch(Exception $e){
                return false;
            }
        }

        public function xml_attribute($object, $attribute) {
            if(isset($object[$attribute]))
            return (string) $object[$attribute];
        }

        public function get_random_id_product_by_category($id_category){
            $category_id = explode(',',  $id_category);
            $in = '(' . implode(',', $category_id) .')';
            // echo json_encode($id_category);
            // die();
            $this->query->select('p.id_product')
                        ->from('product', 'p')
                        ->innerJoin("category_product", "cp", "cp.id_product = p.id_product AND cp.id_category IN ".$in)
                        ->orderBy('rand()')
                        ->limit(30);
            // $this->query->select('id_product')
            //             ->from('product')
            //             ->orderBy('rand()')
            //             ->where('id_category_default', $id_category)
            //             ->limit(10);
            $rows = DB::getInstance()->executeS($this->query);
            return $rows;
        }

        public function get_similar_product($id_category){
            $result = $this->get_random_id_product_by_category($id_category);
            $id = array();
            foreach($result as $item){
                array_push($id, (int)$item['id_product']);
            }
            $id = implode("|", $id);
            $id = "[".$id."]";
            $opt['resource'] = 'products';
            $opt['filter']['active'] = 1;
            $opt['filter']['id'] = $id;
            $opt['display'] = 'full';
            $xmlResponse = $this->webserv->get($opt);
            $product = $xmlResponse->children()->children();
            return $product;
        }

        public function get_deliveries_list_by_combination($array){
            // get range price
            $price = $this->get_range_price_by_price($array['price']);
            $price = json_decode(json_encode($price));
            if(!is_array($price->price_range)){
                $price = [$price->price_range];
            }else{
                $price = $price->price_range;
            }
            $id_price = array();
            foreach($price as $item){
                array_push($id_price, (int)$item->id);
            }
            $id_price = implode("|", $id_price);
            $id_price = "[".$id_price."]";

            // get range weight 
            $weight = $this->get_range_weight_by_weight($array['weight']);
            $weight = json_decode(json_encode($weight));
            if(!is_array($weight->weight_range)){
                $weight = [$weight->weight_range];
            }else{
                $weight = $weight->weight_range;
            }
            $id_weight = array();
            foreach($weight as $item){
                array_push($id_weight, (int)$item->id);
            }
            $id_weight = implode("|", $id_weight);
            $id_weight = "[".$id_weight."]";
            //  get list of delivery by price
            $opt_by_price['resource'] = 'deliveries';
            $opt_by_price['filter']['id_zone'] = $array['id_zone'];
            $opt_by_price['filter']['id_range_price'] = $id_price;
            $opt_by_price['filter']['id_carrier'] = '![0]';
            // $opt['filter']['id_range_weight'] = $id_weight;
            $opt_by_price['display'] = 'full';
            $xmlResponse = $this->webserv->get($opt_by_price);
            $deliveries_by_price = $xmlResponse->children()->children();
            $deliveries_by_price = json_decode(json_encode($deliveries_by_price));
            
            // get list of delivery by weight
            $opt_by_weight['resource'] = 'deliveries';
            $opt_by_weight['filter']['id_zone'] = $array['id_zone'];
            $opt_by_weight['filter']['id_range_weight'] = $id_weight;
            $opt_by_weight['filter']['id_carrier'] = '![0]';
            $opt_by_weight['display'] = 'full';
            $xmlResponse = $this->webserv->get($opt_by_weight);
            $deliveries_by_weight = $xmlResponse->children()->children();
            $deliveries_by_weight = json_decode(json_encode($deliveries_by_weight));
            if(count((array)$deliveries_by_price) > 0){
                if(!is_array($deliveries_by_price->delivery)){
                    $delivery_by_price = [$deliveries_by_price->delivery];
                }else{
                    $delivery_by_price = $deliveries_by_price->delivery;
                }
            }else{
                $delivery_by_price = [];
            }
            if(count((array)$deliveries_by_weight) > 0){
                if(!is_array($deliveries_by_weight->delivery)){
                    $delivery_by_weight = [$deliveries_by_weight->delivery];
                }else{
                    $delivery_by_weight = $deliveries_by_weight->delivery;
                }
            }else{
                $delivery_by_weight = [];
            }
            $delivery_tmp = array();
            foreach($delivery_by_price as $item){
                array_push($delivery_tmp, $item);
            }

            foreach($delivery_by_weight as $item){
                array_push($delivery_tmp, $item);
            }
            // get carrier list
            $id_carrier = array();
            $id_range_price = array();
            $id_range_weight = array();
            foreach($delivery_tmp as $item){
                if(count((array)$item->id_range_weight) == 0){
                    $weight = null;
                }else{
                    $weight = $item->id_range_weight;
                }
                if(count((array)$item->id_range_price) == 0){
                    $price = null;
                }else{
                    $price = $item->id_range_price;
                }
                
                array_push($id_carrier, (int)$item->id_carrier);
                array_push($id_range_price, $price);
                array_push($id_range_weight, $weight);
            }
            $id_carrier = implode("|", $id_carrier);
            $id_carrier = "[".$id_carrier."]";
            $id_range_price = implode("|", $id_range_price);
            $id_range_price = "[".$id_range_price."]";
            $id_range_weight = implode("|", $id_range_weight);
            $id_range_weight = "[".$id_range_weight."]";
            $carrier = $this->get_carrier_by_id($id_carrier);
            // echo json_encode($id_range_price);
            // die();
            //get delivery real
            $opt['resource'] = 'deliveries';
            $opt['filter']['id_zone'] = $array['id_zone'];
            $opt['filter']['id_carrier'] = (string)$carrier->carrier->id;
            $opt['filter']['id_range_price'] = $id_range_price;
            // $opt['filter']['id_range_weight'] = [null];
            $opt['display'] = 'full';
            $xmlResponse = $this->webserv->get($opt);
            $deliveries = $xmlResponse->children()->children();
            $deliveries = json_decode(json_encode($deliveries));
            $deliveries->carrier = $carrier->carrier;
            return $deliveries;
        }

        public function get_range_price_by_price($price){
            $opt['resource'] = 'price_ranges';
            $opt['display'] = 'full';
            $opt['filter']['delimiter1'] = '[0,'.$price.']';
            $opt['filter']['delimiter2'] = '['.$price.',99999999999999.999999]';
            // $opt['filter']['delimiter1']['delimiter2'] = "[".$price."]";
            $xmlResponse = $this->webserv->get($opt);
            $range_price = $xmlResponse->children()->children();
            return $range_price;
        }

        public function get_range_weight_by_weight($weight){
            $opt['resource'] = 'weight_ranges';
            $opt['display'] = 'full';
            $opt['filter']['delimiter1'] = '[0,'.$weight.']';
            $opt['filter']['delimiter2'] = '['.$weight.',10000]';
            $xmlResponse = $this->webserv->get($opt);
            $range_price = $xmlResponse->children()->children();
            return $range_price;
        }

        public function get_carrier_by_id($id){
            $opt['resource'] = 'carriers';
            $opt['display'] = 'full';
            $opt['filter']['id'] = $id;
            $opt['filter']['active'] = 1;
            $opt['filter']['deleted'] = 0;
            $xmlResponse = $this->webserv->get($opt);
            $carrier = $xmlResponse->children()->children();
            return $carrier;
        }

        public function get_carrier_delivery_id($id){
            $opt['resource'] = 'deliveries';
            $opt['display'] = 'full';
            $opt['filter']['id'] = $id;
            $xmlResponse = $this->webserv->get($opt);
            $delivery = $xmlResponse->children()->children();
            return $delivery;
        }

        public function get_taxe_by_product_gouped_by_tax_group($id){
            $this->query->select('*')
                        ->from('tax', 't')
                        ->innerJoin("tax_rule", "tr", "tr.id_tax = t.id_tax")
                        ->innerJoin("tax_rules_group", "trg", "trg.id_tax_rules_group = tr.id_tax_rules_group")
                        ->innerJoin("product", "p", "p.id_tax_rules_group = trg.id_tax_rules_group AND p.id_product = ".$id)
                        ->groupBy('p.id_tax_rules_group');
            $rows = DB::getInstance()->executeS($this->query);
        }

        public function check_if_taxe_include($id_group){
            $opt['resource'] = 'groups';
            $opt['display'] = 'full';
            $opt['filter']['id'] = $id_group;
            $xmlResponse = $this->webserv->get($opt);
            $taxeMethode = $xmlResponse->children()->children();
            return $taxeMethode;
        }

        public function get_promotion_state($id_product){
            $opt['resource'] = 'specific_prices';
            $opt['display'] = 'full';
            $opt['filter']['id_product'] = $id_product;
            $xmlResponse = $this->webserv->get($opt);
            $promotion = $xmlResponse->children()->children();
            return $promotion;
        }
    
        // ------------ Michel ----------------------------------- End

        ///----------------------------------  Andry ------------------------------//
        public function GetCategoriesLinkRewrite($IdCategories)
        {
            $opt['resource'] = 'categories';
            $opt['display'] = '[link_rewrite]';
            $opt['filter']['id'] = $IdCategories;
            $xmlResponse = $this->webserv->get($opt);
            $taxeMethode = $xmlResponse->children()->children();
            return $taxeMethode->category->link_rewrite;
        }
           ///----------------------------------  Andry ------------------------------//
            
      }
          