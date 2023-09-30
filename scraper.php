<?php
session_start();
/*function get_images($url){


	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_NOBODY, true);
	$result = curl_exec($curl);
	if ($result !== false){
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($statusCode == 404){
			return 0;
		} else{
			return $url;
		}
	} else{
		return 0;
	}
}
function only_digits($text){
	return preg_replace('/[^0-9]/', '', $text);
}

function clean_link_path($link){
	$result = str_replace(["&amp;"], "&", $link); // new
	$result = str_replace(["\t", "\r", "\n"], "", $result); // new
	//$regex = '/<a class="profile-link" href="CompanyProfile\.aspx\?PID=(.*?)&country=([0-9]{1,}?)&practicearea=([0-9]{1,}?)&pagenum=" title="(.*?)">(.*?)<\/a>/s';
	return $result;
}

*/

require_once __DIR__ . "/vendor/autoload.php";

use voku\helper\HtmlDomParser;

if (isset($_POST['main_url']) && !empty($_POST['main_url']) && $_POST['save_data'] == 0) {
	$curl = curl_init();
	$main_scrap_url = $_POST['main_url'];
	curl_setopt($curl, CURLOPT_URL, $main_scrap_url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36");
	$html = curl_exec($curl);
	curl_close($curl);

	$htmlDomParserMain = HtmlDomParser::str_get_html($html);
	$containerElements = $htmlDomParserMain->find('div[class="panel-body stock-item-row"]');

	$linkElements = $containerElements->find('a[href]');
	$moreLinks = [];
	if (is_array($linkElements)) {
		foreach ($linkElements as $linkElement) {
			//print_r($linkElement);
			// populate the insureLinks set with the URL
			// extracted from the href attribute of the HTML pagination element
			//print $linkElement->getAttribute("class"); print '<br><br>';
			if ($linkElement->getAttribute("class") == 'btn btn-primary pull-right') {
				$moreLink = $linkElement->getAttribute("href");
				// avoid duplicates in the list of URLs
				if (!in_array($moreLink, $moreLinks)) {
					$moreLinks[] = $moreLink;
					$request_url = str_replace('/stock.html', $moreLink, $main_scrap_url);

					//print_r($params_arr);

					$curl = curl_init();
					curl_setopt($curl, CURLOPT_URL, $request_url);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36");
					$html = curl_exec($curl);
					curl_close($curl);
					//print $html;
					// initialize HtmlDomParser
					$htmlDomParser = HtmlDomParser::str_get_html($html);




					// Checking for images
					/*preg_match_all(
					   '!https://(.*)/(.*).jpeg!',
						   $htmlDomParser, $data
					   );

					   foreach ($data[0] as $list) {
						   //echo "<img src='$list'/>";
					   }
					   print_r($data);*/
					// the ".page-numbers a" CSS selector
					$main_arr = [];
					$amountElements = $htmlDomParser->find('div[class="technical-row"]');


					//print count($amountElements );
					$amounts = [];
					foreach ($amountElements as $amountElement) {
						//print_r( $amountElement->find('div[class="technical-info"]')->plaintext);
						$col_text = $amountElement->find('div[class="technical-headers"]')->plaintext;
						$col_val = $amountElement->find('div[class="technical-info"]')->plaintext;
						if (!in_array($col_val[0], $amounts)) {
							// To get only Miles from the extracted data
							if ($col_text[0] == 'Miles') {
								$amounts[strtolower($col_text[0])] = $col_val[0];
							}

							//$amounts[] = only_digits($amountElement->plaintext);
						}
					}
					if (count($amounts) > 0) {
						$main_arr = $amounts;
					}


					//print "<pre>"; print_r($amounts);
					/*
					   $images = $htmlDomParser->find("img[src]");


					   $image_arr = $images->src;
					   //print_r($image_arr);
					   //print count($image_arr);
					   for($i=0; $i < count($image_arr); $i++){
						   $image_url = get_images($image_arr[$i]);
						   if($image_url != 0){
							   $image_urls[] = $image_url;
						   }
					   }
					   print_r($image_urls);*/
					$insureElements = $htmlDomParser->find('a[href]');
					$insureLinks = [];
					foreach ($insureElements as $insureElement) {
						//print_r($insureElement);
						// populate the insureLinks set with the URL
						// extracted from the href attribute of the HTML pagination element
						//print $insureElement->getAttribute("class"); print '<br><br>';
						if ($insureElement->getAttribute("class") == 'topMItem insurance-link') {
							$insureLink = $insureElement->getAttribute("href");
							// avoid duplicates in the list of URLs
							if (!in_array($insureLink, $insureLinks)) {
								$insureLinks[] = $insureLink;
								$url_components = parse_url($insureLink);
								parse_str($url_components['query'], $params);
								// To get reg parameter from $params
								$params_arr['reg'] = $params['reg'];
							}
						}

					}
					if (count($params_arr) > 0) {
						$main_arr = array_merge($main_arr, $params_arr);
					}

					$priceElements = $htmlDomParser->find('span[class="y-big-price_green y-big-price"]')->plaintext;
					//print_r($priceElements);
					$priceArr['price'] = $priceElements[0];
					if (count($priceArr) > 0) {
						$main_arr = array_merge($main_arr, $priceArr);
					}

					$descElements = $htmlDomParser->find('div[class="information-section"]');
					foreach ($descElements->find('div') as $descElement) {
						foreach ($descElement->find('span') as $innerDescElement) {
							if ($innerDescElement->plaintext == 'Description') {
								$details['description'] = str_replace('<span class="bold-text">Description </span>', '', $innerDescElement->parent()->html);
								$details['description'] = str_replace('<br>', '', $details['description']);
							}
							//print $innerDescElement->find('span')->html;
						}
					}
					if (count($details) > 0) {
						$main_arr = array_merge($main_arr, $details);
					}
					//print_r($params_arr);
					$main_combined_arr[] = $main_arr;

				}
			}

		}
	}

	// For web page display


	echo '<span id="output-message"></span>';
	echo '<h3>Web scraping output</h3>';
	print "<ul>";
	if (isset($main_combined_arr) && is_array($main_combined_arr)) {
		foreach ($main_combined_arr as $arr_key => $value_arr) {
			$counter = $arr_key + 1;
			print "<li>" . $counter . ")<ul>";
			foreach ($value_arr as $key => $value) {
				print '<li>' . $key . ': ' . $value . '</li>';
			}
			print "</ul></li>";
		}
		print "</ul>";
		//print_r($main_combined_arr);
		$_SESSION['output_arr'] = $main_combined_arr;
	}else{
		Print "No Record Found!!";
	}
}

if ($_POST['save_data'] == 1) {
	include("includes/save-output.php");
}


?>