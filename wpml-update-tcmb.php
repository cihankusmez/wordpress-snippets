//This Snippets Required WPML

add_action('wp_footer', 'get_tcmb'); 
function get_tcmb()
{
  $interval = 180; //Interval of Update Database in Minutes  
  $diff = -5 ; //Ratio of Difference from TCMB Results (Value can be positive or negative +/-)
  
  $update_tcmb = false;
  $chn_settings = get_option('_dikeysoft_settings');
  
  $now = new DateTime();
  if( ! isset( $chn_settings['tcmb']['last_update'] ) )
  {
	  $update_tcmb = true;	
  } else {
	  $seconds_diff = $now->getTimestamp() - $chn_settings['tcmb']['last_update'];  
	  $interval *= 60;
	
    if($seconds_diff > $interval)
    {
      $update_tcmb = true;
    }
  }
  
  if($update_tcmb)
  { 
	  $chn_settings['tcmb']['last_update'] = $now->getTimestamp();
  	update_option( '_dikeysoft_settings', $chn_settings);
	
	  $diff_ratio = 1 + $diff/100;
	  $xml= simplexml_load_file('http://www.tcmb.gov.tr/kurlar/today.xml');
    if($xml) {
      foreach ($xml->Currency as $Currency) {
      switch($Currency['Kod'])
      {
        case "EUR": $rates["EUR"] = number_format(floatval($Currency->BanknoteBuying[0]) * $diff_ratio, 4); break;
        case "GBP": $rates["GBP"] = number_format(floatval($Currency->BanknoteBuying[0]) * $diff_ratio, 4); break;
        case "USD": $rates["USD"] = number_format(floatval($Currency->BanknoteBuying[0]) * $diff_ratio, 4); break;
      }
      }
    }

    if(isset($rates))
    {
        foreach($rates as $key_currency => $rate)
        {		
          update_exchange_rates($key_currency, $rate);
        }
    }
  }

    //if(isset($rates))
    //{	
    //echo $seconds_diff."<br/>";
    //echo $interval."<br/>";
    //if(current_user_can('administrator')){
      //echo "<pre>";
      //print_r($rates);
      //echo "</pre>";
    //}
    //}
}

function update_exchange_rates( $currency, $rate ) {
  	$rate = number_format(1 / $rate, 4);
    $wcml_settings = get_option('_wcml_settings');
 
    if( ! isset( $wcml_settings['currency_options'][$currency] ) )
        return;
 
    $wcml_settings['currency_options'][$currency]['rate'] = $rate;
    update_option( '_wcml_settings', $wcml_settings );
}
