#<?php
  
  function encrypt3n($data, $offset, $length, $des_key1, $des_key2, $des_key3, $xor_seed) {
    $key = $xor_seed;

    for($i=0; $i<$length; $i=$i+8) {
      $dat = substr($data, $offset+$i, 8);
      for($t=0; $t<8; $t++) {
        $dat[$t] = $dat[$t] ^ $key[$t];
      }

      $enc = substr(base64_decode(openssl_encrypt($dat, "des-ecb", $des_key1)), 0, 8);
      $enc = openssl_decrypt($enc, "des-ecb", $des_key2, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);
      $enc = substr(base64_decode(openssl_encrypt($enc, "des-ecb", $des_key3)), 0, 8);

      for($t=0; $t<8; $t++) {
        $data[$offset+$i+$t] = $enc[$t];
      }
      $key = $enc;
    }

    return($data);
  }


  function decrypt3n($data, $offset, $length, $des_key1, $des_key2, $des_key3, $xor_seed) {
    $key = $xor_seed;

    for($i=0; $i<$length; $i=$i+8) {
      $dat = substr($data, $offset+$i, 8);

      $dec = openssl_decrypt($dat, "des-ecb", $des_key3, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);
      $dec = substr(base64_decode(openssl_encrypt($dec, "des-ecb", $des_key2)), 0, 8);
      $dec = openssl_decrypt($dec, "des-ecb", $des_key1, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);

      for($t=0; $t<8; $t++) {
        $data[$offset+$i+$t] = $dec[$t] ^ $key[$t];
      }
      $key = $dat;
    }
    return($data);
  }


  function encrypt1n($data, $offset, $length, $des_key, $xor_seed) {
    $key = $xor_seed;

    for($i=0; $i<$length; $i=$i+8) {
      $dat = substr($data, $offset+$i, 8);
      for($t=0; $t<8; $t++) {
        $dat[$t] = $dat[$t] ^ $key[$t];
      }

      $enc = substr(base64_decode(openssl_encrypt($dat, "des-ecb", $des_key)), 0, 8);

      for($t=0; $t<8; $t++) {
        $data[$offset+$i+$t] = $enc[$t];
      }
      $key = $enc;
    }

    return($data);
  }


  function decrypt1n($data, $offset, $length, $des_key, $xor_seed) {
    $key = $xor_seed;

    for($i=0; $i<$length; $i=$i+8) {
      $dat = substr($data, $offset+$i, 8);
      $dec = openssl_decrypt($dat, "des-ecb", $des_key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);

      for($t=0; $t<8; $t++) {
        $data[$offset+$i+$t] = $dec[$t] ^ $key[$t];
      }
      $key = $dat;
    }

    return($data);
  }


  function encrypt2n($data, $offset, $length, $des_key) {
    for($i=0; $i<$length; $i=$i+8) {
      $dat = substr($data, $offset+$i, 8);
      $enc = substr(base64_decode(openssl_encrypt($dat, "des-ecb", $des_key)), 0, 8);

      for($t=0; $t<8; $t++) {
        $data[$offset+$i+$t] = $enc[$t];
      }
    }

    return($data);
  }
  
  
  function decrypt2n($data, $offset, $length, $des_key) {
    for($i=0; $i<$length; $i=$i+8) {
      $dat = substr($data, $offset+$i, 8);
      $dec = openssl_decrypt($dat, "des-ecb", $des_key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);

      for($t=0; $t<8; $t++) {
        $data[$offset+$i+$t] = $dec[$t];
      }
    }

    return($data);
  }
  

	function swap($a) {
	echo strlen($a)." length\n";
	  $b = "0X";
	  for($t=0; $t<strlen($a); $t=$t+2) {
		$b .= substr($a, strlen($a)-$t, 2);
	  }
	  return ($b);
	}

  // TEST
  // Read a captured packet, envelope encryption and query1 encryption is not applied here!
  // So we can skip steps 1 and 2 for the analysis
  // We want to find out about the contents
  $packet = file_get_contents('./c68fc365f49d1f69_01180000.raw');
  //$packet = file_get_contents('./dnas/gai-gw/packets/c68fc365f49d1f69_01180000');
  //$packet = file_get_contents('./dnas/gai-gw/packets/cd46151abd41744f_01180000');
  //$packet = file_get_contents('./dnas/gai-gw/packets/c9acca0f30eecbf7_01180000');
  
  // step 1 - envelope decryption
  //$packet = decrypt3($packet, 0x28, 0x120, pack("H*", "eb711416cb0ab016"), pack("H*", "ae190174b5ce6339"), pack("H*", "7b01b91880145e34"), pack("H*", "c510a6400a9b022f"));

  // step 2 - decrypt 0x20 bytes within the payload, key is derived from the query packet
  // $des_key1 = pack("H*", "8b8542b9b7acf6ae");
  // $des_key2 = pack("H*", "8f3fe299220ebef2");
  // $des_key3 = pack("H*", "22334225f780c54f");
  // $xor_seed = pack("H*", "8641d843b4368a5f");
  // $packet = decrypt3($packet, 0xc8, 0x20, $des_key1, $des_key2, $des_key3, $xor_seed);

  // step 3 - decrypt the 0x20 bytes with static key
  $packet = decrypt2n($packet, 0xc8, 0x20, pack("H*", "C2530839308D2325"));

  // print some information
  echo "Variables from the answer packet:\n";
  echo "0x20 bytes (keyset?): ".bin2hex(substr($packet, 0xc8, 0x20))."\n";
  
  // test if data from packet is RSA encrypted
  // data from the packet (offset 0x48)
  //$a = "0X6BB556BD4E24C2B20D9DA8BF743194E061DB6A5CEC6FCB3E946F411DB3C0157E8FAE885575A9F93E1E9C80650A6F5A2F924FB89E463CF0BB46A455D4D4E95A15A2BDD50ADDED9F630AA910CC873058DA6F943A0D719606909F5511C256D6A77C18CB9A92FB85E414AB7D6E859E613AC838E2691581EF0B17274AF6B9CA5DD053";
  $a = "0X".bin2hex(substr($packet, 0x48, 0x80));
  // from binary (needs swapping)
  $b = swap("0X8B5855AF673DBAEFEBFF5DF2585209FBBB4FBE769100F4A1EB3C258B6D6AE2CEAB46875F9467D87CB294915D6362749983D396C8480968AC769EF576590A784939FDF855A0944D2B1D2A7FCC197C19562D7D205557B75D3606391B82C59111C03CE9ED3187B3DEF137AFA64CC7A7197EE1D8DC1AA66993B5AC3D942D078C71B2");
  $c = swap("0X0100010000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000");
  // is this static? e.g. is the RSA key constant or game specific? here's the result to check
  $d = "0x0001ffffffffffffffff005f1837cf39089aa7b941acaa328b9957f267295c74ea26cb0c47c47b830c9ee224d5b25edd4d05978fb241c23ddb193042428db62cb38239fb47b47f535262782715d60562dc9cf18fdeb3ccb9cdfcfb108ab23d3e3576397c2ab9cf7c1b47a4301ea992fb46848a243857dd0654d916de8b531cc0";

  // test if this is a public key and we if $a is RSA decrypted we get "1"
  // c = m^e mod N
  // m = c^d mod N
  $clear = gmp_init($d);
  $mess = gmp_init($a);
  $pubkey = gmp_init($b);
  $modulus = gmp_init($c);
  $p = gmp_powm($mess, $modulus, $pubkey);

  echo "pubkey:    ".gmp_strval($pubkey, 16)."\n";
  echo "modulus    ".gmp_strval($modulus, 16)."\n";
  echo "original:  ".gmp_strval($mess, 16)."\n";
  echo "decrypted: ".gmp_strval($p, 16)."\n\n";
  echo "expected:  ".gmp_strval($clear, 16)."\n\n";

  // for now let's just dump the achieved stuff
  file_put_contents('./body_120.dec', $packet);
?>
