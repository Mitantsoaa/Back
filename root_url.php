<?php 

require_once('./import_to_prestaNew.php');

$ws_presta = new Webservice_presta();
// notification
if (isset($_GET['type']) && $_GET['type'] == "notification") {
    $allnotifications = $ws_presta->getNotifications();
    echo json_encode($allnotifications );
}
// ////////////////

if (isset($_GET['type']) && $_GET['type'] == "teste") {

    $msg = "You are in teste" ;
    var_dump($test);
    echo $msg ;
}

if (isset($_GET['type']) && $_GET['type'] == "produit") {
    $allProduct = $ws_presta->getAllproductsBrut();
    echo json_encode($allProduct);
}
if (isset($_GET['type']) && $_GET['type'] == "search" && isset($_GET['query']) && isset($_GET['language']) ) {
    $allProduct = $ws_presta->Search($_GET['query'] , $_GET['language']);
    echo json_encode($allProduct);
}

if (isset($_GET['type']) && $_GET['type'] == "getCategoryById" && isset($_GET['idCategory'])) {
    $category_details = $ws_presta->getCategoryById($_GET['idCategory'], $quantity = 1);
    echo json_encode($category_details);
}

if (isset($_GET['type']) && $_GET['type'] == "getProductById" && isset($_GET['idProduct'])) {
    $product = $ws_presta->getProductById($_GET['idProduct'], $quantity = 1);
    if(count((array)$product['prices']) == 0){
        $product['prices'] = 'null';
    }
    $check = $ws_presta->check_if_taxe_include($_GET['idDefaultGroup']);
    // 0 TTC - 1 HT
    $check_tmp = json_decode(json_encode($check->group));
    if($check_tmp->price_display_method == 0){
        $method = 1;
    }else{
        $method = 0;
    }
    
    if ($product != null) {
        $product['method'] = $method;
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
        $productAllComb = $ws_presta->getCombinationByOptions($id_product);
        $productSpecificPrice = null;
        $quantity = 1;
        $check = $ws_presta->check_if_taxe_include($_POST['id_group_customer']);
        // 0 TTC - 1 HT
        $check_tmp = json_decode(json_encode($check->group));
        if($check_tmp->price_display_method == 0){
            $method = 1;
        }else{
            $method = 0;
        }
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
                            $productSpecificPrice = $ws_presta->getSpecificPrices($product->id,$id_product, $quantity, $method);
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
            $productSpecificPrice = $ws_presta->getSpecificPrices(0,$id_product, $quantity, $method);
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
        $isAdded = $ws_presta->AddToCart($id_product, $product_attribute, $quantity, $guest,$idCustomer,$action);
        $data['response'] = $isAdded;
        echo json_encode($data);
    } else {
        $data['response'] = "no-data";
        echo json_encode($data);
    }
}


if (isset($_GET['type']) && $_GET['type'] == "createGuest") {

    $allOs = $ws_presta->saveGuest();
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
    $check = $ws_presta->check_if_taxe_include($_GET['idDefaultGroup']);
    // 0 TTC - 1 HT
    $check_tmp = json_decode(json_encode($check->group));
    if($check_tmp->price_display_method == 0){
        $method = 1;
    }else{
        $method = 0;
    }
    $carts = $ws_presta->getCartByGuestCustomer($idGuest, $idCustomer);
    
    if(!empty($carts->cart)){
        
        $allc = $carts->cart;
        // var_dump($allc);die;
        $cartInfo = [];
        $arrayComb = [];
            if (!empty($allc->associations->cart_rows->cart_row)) {
                
                foreach($allc->associations->cart_rows->cart_row as $crt){

                    // Michel ---------get declinaison option value------------ Start
                        // get product
                        $id = (string)$crt->id_product;
                        $product = json_decode(json_encode($ws_presta->getProductById($id)));
                        $id_declinaison = $product->declinaison;
                        $id_option = "";
                    // Michel --------------------------------------------------End

                    if (strval($crt->id_product_attribute) != "" && $crt->id_product_attribute != 0 && $crt->id_product_attribute != null) {
                        $delc = $ws_presta->getCombination(strval($crt->id_product_attribute));
                        $arrayComb = [];
                        
                        if (isset($delc['product']->combination->associations->product_option_values)) {
                            foreach ($delc['product']->combination->associations->product_option_values->product_option_value as $optionCombination) {
                                $optionC = strval($optionCombination->id);
                                $option_values = $ws_presta->getOptionValuesById($optionC);
                                $alloptionParent = $ws_presta->getAllProductOptions();
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
                    $productDetails = $ws_presta->getSimpleProductInfo(strval($crt->id_product));

                    // Michel ----------- get combination price --------- Start
                    $spec_price = json_decode(json_encode($ws_presta->getSpecificPrices(strval($crt->id_product_attribute), strval($crt->id_product), strval($crt->quantity), $method)));
                    if($spec_price){
                        $price = $spec_price->product->my_price;
                    }else{
                        $price = "null";
                    }
                    // $taxe = $ws_presta->get_taxe_by_product((string)$crt->id_product);
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
                        "spec_price" => $price,
                        "method" => $method
                        // Michel -------------- End
                    );
                    array_push($cartInfo, $to_push);
                }
            }
        echo json_encode($cartInfo);
    }else{
        $data['response'] = "no-datas";
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
    $carts = $ws_presta->getCartByGuestCustomer($idGuest, $idCustomer);
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
    $carts = $ws_presta->AddToCart($idProduct, $idAttribute, 0, $idGuest,0,$action);
    $data['status'] = "ok";
    echo json_encode($data);
}

if (isset($_GET['type']) && $_GET['type'] == 'createCustomer') {
    $rest_json = file_get_contents("php://input");
    // var_dump($rest_json);
    $_POST = json_decode($rest_json, true);

    //$passwd = 'alphadev@randevteam.com';
    $nom =$_POST['nom'];
    $prenom = $_POST['prenom'];
    //$email = 'alphadev2@randevteam.com';
    $gender = $_POST['gender'];
    $passwd = $_POST['password'];
    //$nom = $_POST['email'];
    // $prenom = $_POST['email'];
   $email = $_POST['email'];
    // $gender = "1";
    $array_to = array(
        "nom" => $nom,
        "prenom" => $prenom,
        "email" => $email,
        "gender" => $gender,
        "password" => $passwd
    );

    $customer = $ws_presta->createCustomer($array_to);
    $data['customer'] = $customer;
    // var_dump($customer);
    echo json_encode($data);
}

if (isset($_GET['type']) && $_GET['type'] == 'validateCart') {
    $idCustomer = $_GET['idCustomer'];
    $idGuest = $_GET['idGuest'];
    $allCarts = $ws_presta->getCartIdByGuest($idGuest);
    foreach ($allCarts as $allcart) {
        $cartOne = $ws_presta->getCartById(strval($allcart->id));
        $updated = $ws_presta->validateCart($cartOne, $idCustomer);
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
    $states = $ws_presta->getStates($idCountry);
    echo json_encode($states);
}

if (isset($_GET['type']) && $_GET['type'] == 'getCountries') {
    $countries = $ws_presta->getCountries();
    echo json_encode($countries);
}

if (isset($_GET['type']) && $_GET['type'] == 'makeOrder') {

    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);

    $idCustomer = $_POST['idCustomer'];
    $addressId = $_POST['idAddress'];
    $allCart = $ws_presta->getCartIdByCustomer($idCustomer);

    
    $ws_presta->makeOrder($customerId,$addressId,$idCart,$products);
    $countries = $ws_presta->getCountries();
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
    // $isexistAdress = $ws_presta->getAdressByIdCustomerAndClear($_POST['id_customer']);
    $adresse = $ws_presta->saveAdresse($arrayToSave);
    echo json_encode($adresse);
}

if(isset($_GET['test'])){
    $test =$ws_presta->AddToCart(3010,0,1,1040732,0,'update');
    // $test =$ws_presta->getConfig('PS_LOGO');
    echo json_encode($test);
}
if (isset($_GET['type']) && $_GET['type'] == 'login') {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    $loginCustomer = $ws_presta->login($email,$password);
    if($loginCustomer != false){
        $data['response'] = $loginCustomer;
    }else{
        $data['response'] = "error";
    }
    echo json_encode($data);
}

if (isset($_GET['type']) && $_GET['type'] == 'getAdressByIdCustomer') {
    $idCustomer = $_GET['idCustomer'];
    $customerAdress = $ws_presta->getAdressByIdCustomer($idCustomer);
    if(isset($customerAdress->address->id) && $customerAdress->address->id != null ){
        $data['response'] = $customerAdress;
    }else{
        $data['response'] = "no data";
    }
    echo json_encode($data);
}
if (isset($_GET['type']) && $_GET['type'] == 'getShopInfo') {
    $idshop = $_GET['idShop'];
    $customerAdress = $ws_presta->getShopInfos($idshop);
    $logo =$ws_presta->getConfig('PS_LOGO');
    $data['logo'] = $logo;
    $data['shopInfo'] = $customerAdress;
    echo json_encode($data);
}

// START --------------------------------- Andry -------------------------------//
if (isset($_GET['type']) && $_GET['type'] == 'getCategories') {
    $countries = $ws_presta->getCategories();
    echo json_encode($countries);
}
if (isset($_GET['type']) && $_GET['type'] == 'getCategoriesChildren' ) {
	$id_parent =  $_GET['id'];
    $countries = $ws_presta->getCategoriesChildren($id_parent);
    echo json_encode($countries);   
}
if (isset($_GET['type']) && $_GET['type'] == 'products' && isset($_GET['IdCategories']) ) {
    if(isset($_GET['limit']) && $_GET['limit'] == 'full'){
        $ProductsListByIdCategories = $ws_presta->ProductsListByIdCategories($_GET['IdCategories'], $_GET['limit']);
    } else {
        $ProductsListByIdCategories = $ws_presta->ProductsListByIdCategories(3, '');
    }
    echo json_encode($ProductsListByIdCategories);
}
if (isset($_GET['type']) && $_GET['type'] == 'productslimited' && isset($_GET['IdCategories']) ) {
    if(isset($_GET['limit']) && $_GET['limit'] == 'full'){
        $ProductsListByIdCategories_limited = $ws_presta->ProductsListByIdCategories_limited($_GET['IdCategories'], $_GET['limit']);
    } else {
        $ProductsListByIdCategories_limited = $ws_presta->ProductsListByIdCategories_limited($_GET['IdCategories'], '');
    }
    echo json_encode($ProductsListByIdCategories_limited);
}
if (isset($_GET['type']) && $_GET['type'] == 'getImage' && isset($_GET['id'])) {
    $countries = $ws_presta->getImage($_GET['id']);
    echo json_encode($countries);
}

if (isset($_GET['type']) && $_GET['type'] == 'HomeOrganizer') {
    $HomeOrganizer = $ws_presta->HomeOrganizer();
    echo json_encode($HomeOrganizer);
}
if (isset($_GET['type']) && $_GET['type'] == 'GetCategoriesLinkRewrite' && isset($_GET['IdCategories']) ) {
    $linkrewrite = $ws_presta->GetCategoriesLinkRewrite($_GET['IdCategories']);
    echo json_encode($linkrewrite);
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

        if($ws_presta->valide_form_edit($array)){
            $response['response'] = "error form";
        }else{

            if(isset($_POST["new_passwd"])){
                $array['new_passwd'] = $_POST["new_passwd"];
            }
            
            $idCustomer = $_GET['idCustomer'];
            $customer = $ws_presta->getCustomerById($idCustomer);
            $customer_edit = $ws_presta->editCustomer($customer, $array);

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
    $allAdress = $ws_presta->getAllAdressByIdCustomer($idCustomer);
    if($allAdress){
        $response['response'] = $allAdress;
    }else{
        $response['response'] = 'no data';
    }
    echo json_encode($response);
}

if(isset($_GET['type']) && $_GET['type'] == "deleteAdressByIdadress"){
    $idAdress = $_GET['idAdress'];
    $deleted_adress = $ws_presta->deleteAdressById($idAdress);
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
        $edited_adress = $ws_presta->editAdress($idAdress, $array);
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
    $stock = $ws_presta->get_stock_by_id_product($idProduct, $idProductAttribute);
    echo json_encode($stock);
}

if(isset($_GET['type']) && $_GET['type'] == "editCart"){
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    $datas = $_POST['data'];
    
    $id_cart = $_GET['idCart'];
    $result = $ws_presta->edit_cart($id_cart, $datas);
    if($result){
        $data['response'] = 'success';
    }else{
        $data['response'] = 'error';
    }
    echo json_encode($data);
}

if(isset($_GET['type']) && $_GET['type'] == "getCountryById"){
    
    $id = $_GET['id_country'];
    $country = $ws_presta->getCountryById($id);

    echo json_encode($country);
}

if(isset($_GET['type']) && $_GET['type'] == "getSimilarProduct"){
    
    $id = $_GET['id_category'];
    $product = $ws_presta->get_similar_product($id);

    echo json_encode($product);
}

if(isset($_GET['type']) && $_GET['type'] == "getDeliveriesList"){
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    $id_zone = $_POST['id_zone'];
    $price = $_POST['price'];
    $weight = $_POST['weight'];
    $array = [
        "id_zone" => $id_zone,
        "price" => $price,
        "weight" => $weight
    ];
    $deliveries = $ws_presta->get_deliveries_list_by_combination($array);
    echo json_encode($deliveries);
}

if(isset($_GET['type']) && $_GET['type'] == "getPromotionState"){
    $state = $ws_presta->get_promotion_state($_GET['idProduct']);
    // $state = json_encode($state);
    // $state = json_decode($state);
    // $state = $state->specific_price;
    // $allproduct = $ws_presta->getAllproducts();
    // $allproduct = json_encode($allproduct);
    // $allproduct = json_decode($allproduct);
    // $allproduct = $allproduct->product;

    $data = array();
    if(count((array)$state) > 0){
        // $test = $ws_presta->getProductById($state[0]->id_product);
        // // var_dump($state);
        // for($j=0; $j<count((array)$state); $j++){
        //     for($i=0; $i<count((array)$allproduct); $i++){
        //         if($state[$j]->id_product == $allproduct[$i]->id){
        //             echo json_encode($allproduct[$i]);
        //         }
        //     }
        // }
    //    print_r($data);
       echo json_encode($state);
    }else{
        echo json_encode("null");
    }
}


// ------------- Michel -------------------------------------- End


// Send email
if (isset($_GET['type']) && $_GET['type'] == 'sendEmail') {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    $receiver = $prenom." ".$nom;
    // $prenom = "Test";
    // $email = "test@gmail.com";
    // $message = "Test";

    $to = "Passion Campagne";
    // $subject = "Email depuis Application Mobile Passion campagne";
    // $headers = "From: ".$email;
    // $sent = mail($to,$subject,$message,$headers);
    // if($sent != false){
    //     $data['response'] = "Email envoyé";
    // }else{
    //     $data['response'] = "Erreur lors de l'envoi";
    // }
    // echo json_encode($data);

    $sent = Mail::Send(
        (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
        'contact', // email template file to be use
        ' Email depuis Application Mobile Passion campagne', // email subject
        array(
            '{email}' => $email, // sender email address
            '{message}' => $message // email content
        ),
        Configuration::get('PS_SHOP_EMAIL'), // receiver email address
        $to, //receiver name
        $email, //from email address
        $receiver  //from name
    );
        if($sent != false){
            $data['response'] = "Email envoyé";
        }else{
            $data['response'] = "Erreur lors de l'envoi";
        }
        echo json_encode($data);
}


// get all product promo

if(isset($_GET['type']) && $_GET['type'] == "getAllProductPromo"){
    $allproduct = $ws_presta->getAllproductspromo();
    echo json_encode($allproduct);
}

// get all latest product

if(isset($_GET['type']) && $_GET['type'] == "getLatestProducts"){
    $allproduct = $ws_presta->getLatestProducts();
    echo json_encode($allproduct);
}

// get all product Manufacturer

if(isset($_GET['type']) && $_GET['type'] == "getProductsManufacturer"){
    $allproduct = $ws_presta->getProductsManufacturer();
    echo json_encode($allproduct);
}