<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

class Webservice_presta
{

    public $table_name = 'structure_catalogue';

    public $conn;

    public $webserv;

    public $query;

    // public $querySecond;

    public function __construct()

    {

        define('DEBUG', false);

        define('PS_SHOP_PATH', 'https://xpressfood-rijaniony.com/');

        define('PS_WS_AUTH_KEY', 'JQVXEF6FMXSYHBUV5SWM212MQBQT9ECG');

        require_once('./PSWebServiceLibrary.php');

        
        require('../config/config.inc.php');
        $this->query = new DbQuery();
        // $this->querySecond = new DbQuery();

        $this->webserv = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
    }

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

        $auth = base64_encode(PS_WS_AUTH_KEY. ":");


        header('Content-type: image/jpeg;');
        $p = $xxx ;
        $a = file_get_contents($p);
        echo $a;

        $username = PS_WS_AUTH_KEY;
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

        $link = PS_SHOP_PATH . $resource .'/' ;

        $Images_link =  array("id_default_image" => ($link . $id_default_image ));

        // var_dump($Images_link);

        // $opt2['resource'] = $resource ;

        // $opt2['display'] = 'full';

        // $xmlResponse2 = $this->webserv->get($opt2);

        // return ($xmlResponse2);
    }

    // ProductsListByIdCategories
    public function ProductsListByIdCategories($IdCategories){
            $opt['resource'] = 'products';
            $opt['filter']['id_category_default'] = intval($IdCategories);
            $opt['filter']['active'] = 1;
            $opt['display'] = 'full';
            $opt['limite'] = 5;

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
            $list =  array("Grand_Titre" => "VÊTEMENT" ,"Sous_Categories" => $titre );
            $titre2 =  array( 
                array( "SousSousSousTitre" => "Bottes chasse extérieur cuir" ,"IdCategorie" => 154),
                array( "SousSousSousTitre" => "haussures chasse chaudes" ,"IdCategorie" => 102),
                array( "SousSousSousTitre" => "Bottes enfant","IdCategorie" => 184)
            );
            $list2 =  array("Grand_Titre" => "CHAUSSURE & BOTTES" ,"Sous_Categories" => $titre2 );
            $titre3 =  array( 
                array( "SousSousSousTitre" => "Amortisseur de recul" ,"IdCategorie" => 64) ,
                array( "SousSousSousTitre" => "Etui","IdCategorie" => 42) 
                // array( "SousSousSousTitre" => "Gilet de protection pour chiens","IdCategorie" => 79)
            );
            $list3 =  array("Grand_Titre" => "ÉQUIPEMENT & ACCESSOIRES" ,"Sous_Categories" => $titre3 );
            $the_big_list = array($list , $list2 , $list3) ;
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

    public function getProductById($idProduct)
    {
        $opt['resource'] = 'products';
        $opt['filter']['active'] = 1;
        $opt['filter']['id'] = $idProduct;
        $opt['display'] = 'full';
        $xmlResponse = $this->webserv->get($opt);
        $allproducts['product'] = $xmlResponse->children()->children();

        $opt2['resource'] = 'specific_prices';
        $opt2['filter']['id_product'] = $idProduct;
        $opt2['display'] = 'full';
        $xmlResponsePrice = $this->webserv->get($opt2);

        $allproducts['product'] = $xmlResponse->children()->children();
        $allproducts['prices'] = $xmlResponsePrice->children()->children();
        
        $allproducts['product']->product->description->language = strip_tags($allproducts['product']->product->description->language);
        $allproducts['product']->product->description_short->language = strip_tags($allproducts['product']->product->description_short->language);

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

    public function getSpecificPrices($idComb,$idProduct, $quantity)
    {
        $xml = $this->webserv->get(array('url' => PS_SHOP_PATH.'/api/products/'.$idProduct.'?price[my_price][product_attribute]='.$idComb.'&price[my_price][use_reduction]=1&price[my_price][quantity]='.$quantity));
        return $xml;
    }

    public function AddToCart($id_product, $product_attribute, $quantity, $guest,$idCustomer,$action)
    {
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
        if ($idCustomer != 0) {
            $opt['filter']['id_customer'] = $idCustomer;
        }
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
            $xml = $this->webserv->get(array('url' => PS_SHOP_PATH.'/api/orders?schema=blank'));

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

 

            $xml = $this->webserv->get(array('url' => PS_SHOP_PATH.'/api/order_histories?schema=blank'));

 

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
                        ->limit(10);
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
    
        // ------------ Michel ----------------------------------- End
}


$importer = new Webservice_presta();
if (isset($_GET['type']) && $_GET['type'] == "teste") {
    $msg = "You are in teste" ;
    var_dump($msg);
    echo $msg ;
}

if (isset($_GET['type']) && $_GET['type'] == "produit") {
    $allProduct = $importer->getAllproductsBrut();
    echo json_encode($allProduct);
}
if (isset($_GET['type']) && $_GET['type'] == "search" && isset($_GET['query']) && isset($_GET['language']) ) {
    $allProduct = $importer->Search($_GET['query'] , $_GET['language']);
    echo json_encode($allProduct);
}

if (isset($_GET['type']) && $_GET['type'] == "getProductById" && isset($_GET['idProduct'])) {
    $product = $importer->getProductById($_GET['idProduct'], $quantity = 1);
    if ($product != null) {
        echo json_encode($product);
    } else {
        echo "no data";
    }
}

if (isset($_GET['type']) && $_GET['type'] == "combinationGetPrices") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    // $_POSTe['id_product'] = 5158;
    // $_POSTe['optVal'] = array(
    //     'idOption' =>7,
    //     'optionValue' => 88,
    //     'quantity' => 5
    // );
    // $_POST = $_POSTe;
    if (isset($_POST) && $_POST != null) {
        $id_product = $_POST['id_product'];
        $productAllComb = $importer->getCombinationByOptions($id_product);
        $productSpecificPrice = null;
        $quantity = 1;
        if($_POST['optVal'] !=null){
            foreach ($productAllComb->combination as $product) {
                if ($product != null) {
                    $allOpt = $_POST['optVal'];
                    $arrayOpt = array();
                    $array1 = array();
                    $array2 = array();
                    // echo json_encode($allOpt);die();
                    if(isset($allOpt['quantity']) && $allOpt['quantity'] != null){

                        if (isset($allOpt['quantity']) && $allOpt['quantity'] != null) {
                            $quantity = $allOpt['quantity'];
                        }
                        $to_p_sh = array(
                            "id" => $allOpt['optionValue'],
                        );
                        if ($allOpt['optionValue'] != null) {
                            array_push($array1, $to_p_sh);
                        } else {
                            $data['error'] = '1';
                            echo json_encode($data);
                            die();
                        }
                        

                    }else{
                        foreach ($allOpt as $opt) {
                            if (isset($opt['quantity']) && $opt['quantity'] != null) {
                                $quantity = $opt['quantity'];
                            }
                            $to_p_sh = array(
                                "id" => $opt['optionValue'],
                            );
                            if ($opt['optionValue'] != null) {
                                array_push($array1, $to_p_sh);
                            } else {
                                $data['error'] = '1';
                                echo json_encode($data);
                                die();
                            }
                        }
                    }
                    if ($product->associations != null) {
                        $to_comp = $product->associations->product_option_values;
                        foreach ($to_comp->product_option_value as $comp) {
                            array_push($array2, array("id" => strval($comp->id)));
                        }
                        
                        $final_arr = array();
                        foreach ($array1 as $arr) {
                            if (($key = array_search($arr, $array2)) !== false) {
                                unset($array2[$key]);
                            }
                        }
                        if (count($array2) == 0) {
                            $productSpecificPrice = $importer->getSpecificPrices($product->id,$id_product, $quantity);
                            $productSpecificPrice->product->description->language = strip_tags($productSpecificPrice->product->description->language);
                            $productSpecificPrice->product->description_short->language = strip_tags($productSpecificPrice->product->description_short->language);
                            $child = $productSpecificPrice->product->addChild("currentCombinationId",$product->id);
                            echo json_encode($productSpecificPrice);
                            die();
                        }
                    }
                } else {
                    echo "no data";
                }
            }
        }else{
            $productSpecificPrice = $importer->getSpecificPrices(0,$id_product, $quantity);
            $productSpecificPrice->product->description->language = strip_tags($productSpecificPrice->product->description->language);
            $productSpecificPrice->product->description_short->language = strip_tags($productSpecificPrice->product->description_short->language);
            echo json_encode($productSpecificPrice);
        }
        
    }
}
if (isset($_GET['type']) && $_GET['type'] == "addToCart") {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    if (isset($_POST) && $_POST != null) {
        if (!isset($_POST['product_attribute'])) {
            $product_attribute = 0;
        } else {
            $product_attribute = $_POST['product_attribute'];
        }
        $id_product = $_POST['id_product'];
        $quantity = $_POST['quantity'];
        $guest = $_POST['guest'];
        if(!isset($_POST['idCustomer'])){
            $idCustomer = 0;
        }else{
            $idCustomer = $_POST['idCustomer'];
        }
        
        if ($guest == null) {
            $guest = 0;
        }
        $action = "update";
        $isAdded = $importer->AddToCart($id_product, $product_attribute, $quantity, $guest,$idCustomer,$action);
        $data['response'] = $isAdded;
        echo json_encode($data);
    } else {
        $data['response'] = "no-data";
        echo json_encode($data);
    }
}


if (isset($_GET['type']) && $_GET['type'] == "createGuest") {

    $allOs = $importer->saveGuest();
    $data['response'] = (int)$allOs->guest->id;
    echo json_encode($data);
}

if (isset($_GET['type']) && $_GET['type'] == "getCartByGuest") {
    $idGuest = $_GET['idGuest'];
    if($idGuest == null){
        $idGuest =0;
    }
    if (isset($_GET['idCustomer']) && $_GET['idCustomer'] != 0) {
        $idCustomer = $_GET['idCustomer'];
    } else {
        $idCustomer = 0;
    }
    $carts = $importer->getCartByGuestCustomer($idGuest, $idCustomer);
    if(!empty($carts->cart)){
        $allc = $carts->cart;
        $cartInfo = [];
        $arrayComb = [];
            if (!empty($allc->associations->cart_rows->cart_row)) {
                foreach($allc->associations->cart_rows->cart_row as $crt){

                    // Michel ---------get declinaison option value------------ Start
                        // get product
                        $id = (string)$crt->id_product;
                        $product = json_decode(json_encode($importer->getProductById($id)));
                        $id_declinaison = $product->declinaison;
                        $id_option = "";
                    // Michel --------------------------------------------------End

                    if (strval($crt->id_product_attribute) != "" && $crt->id_product_attribute != 0 && $crt->id_product_attribute != null) {
                        $delc = $importer->getCombination(strval($crt->id_product_attribute));
                        $arrayComb = [];
                        
                        if (isset($delc['product']->combination->associations->product_option_values)) {
                            foreach ($delc['product']->combination->associations->product_option_values->product_option_value as $optionCombination) {
                                $optionC = strval($optionCombination->id);
                                $option_values = $importer->getOptionValuesById($optionC);
                                $alloptionParent = $importer->getAllProductOptions();
                                $titreVal = "";
                                $valueVal = strval($option_values->product_option_value->name->language[0]);
                                foreach ($alloptionParent->product_option as $optParent) {
                                    foreach ($optParent->associations->product_option_values->product_option_value as $optParent2) {
                                        if (strval($optParent2->id) == strval($option_values->product_option_value->id)) {
                                            $titreVal = strval($optParent->name->language[0]);
                                        }
                                    }
                                }
                                
                                // Michel ---------set declinaison id------------ Start
                                foreach($id_declinaison as $dec){
                                    if($dec->name->language == $titreVal){
                                        $id_option = $dec->id;
                                    }
                                }
                                // Michel --------------------- End

                                $toArrComb = array(
                                    'OptionName' => $titreVal,
                                    'OptionVal' => $valueVal, // from langage, set for value of option
                                );
                                array_push($arrayComb, $toArrComb);
                            }
                        } else {
                            $arrayComb = [];
                        }
                    }else{
                        $arrayComb = [];
                    }
                    $productDetails = $importer->getSimpleProductInfo(strval($crt->id_product));

                    // Michel ----------- get combination price --------- Start
                    $spec_price = json_decode(json_encode($importer->getSpecificPrices(strval($crt->id_product_attribute), strval($crt->id_product), strval($crt->quantity))));
                    if($spec_price){
                        $price = $spec_price->product->my_price;
                    }else{
                        $price = "null";
                    }
                    // Michel ------------------------------------------- End

                    $to_push = array(
                        "idCart" => strval($allc->id),
                        "id_product" =>  strval($crt->id_product),
                        "id_product_attribute" =>  strval($crt->id_product_attribute),
                        "quantity" =>  strval($crt->quantity),
                        "id_customization" =>  strval($crt->id_customization),
                        "id_customization" =>  strval($crt->id_address_delivery),
                        "nb" => strval($crt->quantity),
                        "product_name" => strval($productDetails->product->name->language[0]),
                        "optionsValueArr" => $arrayComb,
                        // Michel -------------- Start
                        "default_price" => $product->product->product->price,
                        "spec_price" => $price
                        // Michel -------------- End
                    );
                    array_push($cartInfo, $to_push);
                }
            }
        echo json_encode($cartInfo);
    }else{
        $data['response'] = "no-data";
        echo json_encode($data);
    }
}

if (isset($_GET['type']) && $_GET['type'] == "getNbCartByGuest") {
    $idGuest = $_GET['idGuest'];
    if (isset($_GET['idCustomer']) && $_GET['idCustomer'] != 0) {
        $idCustomer = $_GET['idCustomer'];
    } else {
        $idCustomer = 0;
    }
    if(isset($_GET['idGuest']) && $_GET['idGuest'] != 0){
        $idGuest = $_GET['idGuest'];
    }else{
        $data['nbCart'] = 0;
        echo json_encode($data);
        die();
    }
    $carts = $importer->getCartByGuestCustomer($idGuest, $idCustomer);
    if(!empty($carts->cart)){
        $allc = $carts->cart->associations->cart_rows->cart_row;
        $nbprd = 0;
        foreach ($allc as $crt) {
            $nbprd += $crt->quantity;
        }
        $data['nbCart'] = $nbprd;
    }else{
        $data['nbCart'] = 0;
    }
    echo json_encode($data);
}

if (isset($_GET['type']) && $_GET['type'] == "deleteCart") {
    $idProduct = $_GET['idProduct'];
    $idAttribute = $_GET['idAttribute'];
    $idGuest = $_GET['idGuest'];
    $action ="delete";
    $carts = $importer->AddToCart($idProduct, $idAttribute, 0, $idGuest,0,$action);
    $data['status'] = "ok";
    echo json_encode($data);
}

if (isset($_GET['type']) && $_GET['type'] == 'createCustomer') {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);

    $passwd = $_POST['password'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $array_to = array(
        "nom" => $nom,
        "prenom" => $prenom,
        "email" => $email,
        "gender" => $gender,
        "password" => $passwd
    );

    $customer = $importer->createCustomer($array_to);
    $data['customer'] = $customer;
    // echo json_encode($customer);
    echo json_encode($data);
}

if (isset($_GET['type']) && $_GET['type'] == 'validateCart') {
    $idCustomer = $_GET['idCustomer'];
    $idGuest = $_GET['idGuest'];
    $allCarts = $importer->getCartIdByGuest($idGuest);
    foreach ($allCarts as $allcart) {
        $cartOne = $importer->getCartById(strval($allcart->id));
        $updated = $importer->validateCart($cartOne, $idCustomer);
    }
    $data['response'] = "ok";
    echo json_encode($data);
}

if (isset($_GET['type']) && $_GET['type'] == 'getStates') {
    if (isset($_GET['country'])) {
        $idCountry = $_GET['country'];
    } else {
        $idCountry = 0;
    }
    $states = $importer->getStates($idCountry);
    echo json_encode($states);
}

if (isset($_GET['type']) && $_GET['type'] == 'getCountries') {
    $countries = $importer->getCountries();
    echo json_encode($countries);
}

if (isset($_GET['type']) && $_GET['type'] == 'makeOrder') {

    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);

    $idCustomer = $_POST['idCustomer'];
    $addressId = $_POST['idAddress'];
    $allCart = $importer->getCartIdByCustomer($idCustomer);

    
    $importer->makeOrder($customerId,$addressId,$idCart,$products);
    $countries = $importer->getCountries();
    echo json_encode($countries);
}

if (isset($_GET['type']) && $_GET['type'] == 'createAdresse') {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    $arrayToSave = array(
        "id_customer" => $_POST['id_customer'],
        'id_country' => $_POST['id_country'],
        'id_state' => $_POST['id_state'],
        'lastname' => $_POST['lastname'],
        'firstname' => $_POST['firstname'],
        'address1' => $_POST['address1'],
        'address2' => $_POST['address2'],
        'postcode' => $_POST['postcode'],
        'city' => $_POST['city'],
        'phone' => $_POST['phone'],
    );
    if(isset($_POST['alias'])){
        $arrayToSave['alias'] = $_POST['alias'];
    }else{
        $arrayToSave['alias'] = "Mon Addresse";
    }
    // $isexistAdress = $importer->getAdressByIdCustomerAndClear($_POST['id_customer']);
    $adresse = $importer->saveAdresse($arrayToSave);
    echo json_encode($adresse);
}

if(isset($_GET['test'])){
    $test =$importer->AddToCart(3010,0,1,1040732,0,'update');
    // $test =$importer->getConfig('PS_LOGO');
    echo json_encode($test);
}
if (isset($_GET['type']) && $_GET['type'] == 'login') {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    $loginCustomer = $importer->login($email,$password);
    if($loginCustomer != false){
        $data['response'] = $loginCustomer;
    }else{
        $data['response'] = "error";
    }
    echo json_encode($data);
}

if (isset($_GET['type']) && $_GET['type'] == 'getAdressByIdCustomer') {
    $idCustomer = $_GET['idCustomer'];
    $customerAdress = $importer->getAdressByIdCustomer($idCustomer);
    if(isset($customerAdress->address->id) && $customerAdress->address->id != null ){
        $data['response'] = $customerAdress;
    }else{
        $data['response'] = "no data";
    }
    echo json_encode($data);
}
if (isset($_GET['type']) && $_GET['type'] == 'getShopInfo') {
    $idshop = $_GET['idShop'];
    $customerAdress = $importer->getShopInfos($idshop);
    $logo =$importer->getConfig('PS_LOGO');
    $data['logo'] = $logo;
    $data['shopInfo'] = $customerAdress;
    echo json_encode($data);
}

// START --------------------------------- Andry -------------------------------//
if (isset($_GET['type']) && $_GET['type'] == 'getCategories') {
    $countries = $importer->getCategories();
    echo json_encode($countries);
}
if (isset($_GET['type']) && $_GET['type'] == 'getCategoriesChildren' ) {
	$id_parent =  $_GET['id'];
    $countries = $importer->getCategoriesChildren($id_parent);
    echo json_encode($countries);   
}
if (isset($_GET['type']) && $_GET['type'] == 'products' && isset($_GET['IdCategories']) ) {
    $ProductsListByIdCategories = $importer->ProductsListByIdCategories($_GET['IdCategories']);
    echo json_encode($ProductsListByIdCategories);
}
if (isset($_GET['type']) && $_GET['type'] == 'getImage' && isset($_GET['id'])) {
    $countries = $importer->getImage($_GET['id']);
    echo json_encode($countries);
}

if (isset($_GET['type']) && $_GET['type'] == 'HomeOrganizer') {
    $HomeOrganizer = $importer->HomeOrganizer();
    echo json_encode($HomeOrganizer);
}
//  --------------------------------- Andry ------------------------------- ENDS //

// ------------------------ Michel ---------------------------------- Start
if(isset($_GET['type']) && $_GET['type'] == "editCustomer"){
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    if(isset($_POST["firstname"]) && isset($_POST["lastname"]) && isset($_POST["email"]) && isset($_POST["passwd"])){
        $array = [
            "firstname" => $_POST["firstname"],
            "lastname" => $_POST["lastname"],
            "email" => $_POST["email"],
            "birthday" => $_POST["birthday"],
            "passwd" => $_POST["passwd"],
        ];

        if($importer->valide_form_edit($array)){
            $response['response'] = "error form";
        }else{

            if(isset($_POST["new_passwd"])){
                $array['new_passwd'] = $_POST["new_passwd"];
            }
            
            $idCustomer = $_GET['idCustomer'];
            $customer = $importer->getCustomerById($idCustomer);
            $customer_edit = $importer->editCustomer($customer, $array);

            if($customer_edit != null && $customer_edit != false){
                $response['response'] = $customer_edit;
            }else{
                $response['response'] = "error password";
            }
        }

        echo json_encode($response);
    }else{
        $response['response'] = "error";
        echo json_encode($response);
    }
}

if(isset($_GET['type']) && $_GET['type'] == "getAllAdressByIdCustomer"){
    $idCustomer = $_GET['idCustomer'];
    $allAdress = $importer->getAllAdressByIdCustomer($idCustomer);
    echo json_encode($allAdress);
}

if(isset($_GET['type']) && $_GET['type'] == "deleteAdressByIdadress"){
    $idAdress = $_GET['idAdress'];
    $deleted_adress = $importer->deleteAdressById($idAdress);
    if($deleted_adress){
        $retour['response'] = $deleted_adress;
    }else{
        $retour['response'] = "error";
    }
    echo json_encode($retour);
}

if(isset($_GET['type']) && $_GET['type'] == "editAdress"){
    $idAdress = $_GET['idAdress'];
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);

    if(isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['address1']) && isset($_POST['city']) && isset($_POST['postcode']) && isset($_POST['id_country'])){
        $array = [
            "firstname" => $_POST["firstname"],
            "lastname" => $_POST["lastname"],
            "address1" => $_POST["address1"],
            "city" => $_POST["city"],
            "postcode" => $_POST["postcode"],
            "id_country" => $_POST["id_country"],
        ];
        if(isset($_POST["alias"])){
            $array['alias'] = $_POST["alias"];
        }
        if(isset($_POST["phone"])){
            $array['phone'] = $_POST["phone"];
        }
        if(isset($_POST["company"])){
            $array['company'] = $_POST["company"];
        }
        if(isset($_POST["address2"])){
            $array['address2'] = $_POST["address2"];
        }
        if(isset($_POST["id_state"])){
            $array['id_state'] = $_POST["id_state"];
        }
        $edited_adress = $importer->editAdress($idAdress, $array);
        if($edited_adress){
            $response['response'] = $edited_adress;
        }else{
            $response['response'] = "error";
        }
    }else{
        $response['response'] = "error";
    }

    echo json_encode($response);
}

if(isset($_GET['type']) && $_GET['type'] == "getStockByIdProductAndIdProductAttribute"){
    $idProductAttribute = $_GET['idProductAttribute'];
    $idProduct = $_GET['idProduct'];
    $stock = $importer->get_stock_by_id_product($idProduct, $idProductAttribute);
    echo json_encode($stock);
}

if(isset($_GET['type']) && $_GET['type'] == "editCart"){
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    $datas = $_POST['data'];
    
    $id_cart = $_GET['idCart'];
    $result = $importer->edit_cart($id_cart, $datas);
    if($result){
        $data['response'] = 'success';
    }else{
        $data['response'] = 'error';
    }
    echo json_encode($data);
}

if(isset($_GET['type']) && $_GET['type'] == "getCountryById"){
    
    $id = $_GET['id_country'];
    $country = $importer->getCountryById($id);

    echo json_encode($country);
}

if(isset($_GET['type']) && $_GET['type'] == "getSimilarProduct"){
    
    $id = $_GET['id_category'];
    $product = $importer->get_similar_product($id);

    echo json_encode($product);
}

// ------------- Michel -------------------------------------- End