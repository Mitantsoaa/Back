<?php
class Menu {
	public $URL = "https://xpressfood-rijaniony.com/fr/";
	public $hh ;
	public function HTML(){
		include_once('./simple_html_dom.php');
		$dom = file_get_html($this->URL,false);
		return $dom ;
	}
	public function string_between_two_string($str, $starting_word, $ending_word)
	{
	    $subtring_start = strpos($str, $starting_word);
	    $subtring_start += strlen($starting_word);  
	    $size = strpos($str, $ending_word, $subtring_start) - $subtring_start; 
	    return substr($str, $subtring_start, $size);  
	}

	public function GetMenu(){
		$dom = $this->HTML();
		if(!empty($dom)) {

			$divClass = $title = $titlebest = $titre  = $MenuPrincipaleName = $SousTitre = $SousSousTitre =  '' ;
			$i = 0;
			$Compose = array();
				
		   foreach($dom->find(".js-top-menu") as $divClass) {
			   
				foreach ($divClass->find("ul.principale> li.category") as $title) {
				
					$MenuName = array();
					$TitraWithSoutitra = array();

					foreach ($title->find("a.dropdown-item") as $MenuPrincipaleName) {
						$MenuPrincipaleName_tmps = str_replace('  ', '', $MenuPrincipaleName->plaintext);
						$a = new SimpleXMLElement($MenuPrincipaleName);
						$link = $a["href"];
						$id_categorie = $this->string_between_two_string((string)$link ,$this->URL , "-");
						$tiltea = array('Menu_Name' => html_entity_decode($MenuPrincipaleName_tmps),"IdCategorie" => $id_categorie);
						array_push($MenuName, $tiltea);
					}
						// foreach ($title->find(" ul.second > li.category") as $titlebest) {

						// 	$SousTitre_Name = array();
						// 	$SousSousSousTitre_array = array();
						//     //foreach ($titlebest->find("a.dropdown-submenu") as $titre) {
						// 	foreach ($title->find(" ul.second > li.category") as $titre) {
						//     		$string_titre = str_replace('  ', '', $titre->plaintext);
						// 			$a = new SimpleXMLElement($titre);
						//     		$link = $a['id'];
						//     		$id_categorie = explode("-",$link)[1];
						//     		$tiltea = array( "SousMenu" => $string_titre, 'IdCategorie' => intval($id_categorie));
						//     		array_push($SousTitre_Name, $tiltea);
						//     }
						
						    // foreach ($titlebest->find("div.ets_mm_block_content > ul.ets_mm_categories > li > a") as $SousSousSousTitre) {          
						    			
						    // 				$a = new SimpleXMLElement($SousSousSousTitre);
						    // 				$link = $a['href'];
						    // 				$id_categorie = $this->string_between_two_string((string)$link ,$this->URL , "-");
						    // 				$string = str_replace('  ', '', $SousSousSousTitre->plaintext);
							// 				$tiltea = array('SousSousSousTitre' => html_entity_decode($string,ENT_QUOTES | ENT_XML1, 'UTF-8'), 'IdCategorie' => intval($id_categorie) );
							// 				array_push($SousSousSousTitre_array, $tiltea);
						    // }

						    // $Go_output = array( "SousTitre" => $SousTitre_Name , "SousSousTitre" => $SousSousSousTitre_array);
							// array_push($TitraWithSoutitra, $Go_output); 	
					// }

					$Go_output = array( "MenuName" => $MenuName) ;
					array_push($Compose, $Go_output);
				}
		   }
		   return($Compose);
					   
		}	
	}

}

$Go = new Menu();

if (isset($_GET['type']) && $_GET['type'] == 'Menu') {
	$Menu = $Go->GetMenu();
    echo json_encode($Menu);
}

    
	
	
	    

		



		
