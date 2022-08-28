<?php
/*
There are multiple encryption/decryption algorithms

encrypt1n / decrypt1n used for the query1 payload
encrypt3n / decrypt3n used for variables
*/

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


  // variables bring their own keyset
  function decrypt_var($src, $offset, $length) {
    $vari = substr($src, $offset, $length);
	
    $des_key1 = substr($vari,    0, 8);
    $des_key2 = substr($vari, 0x10, 8);
    $des_key3 = substr($vari,    0, 8);
    $xor_key  = substr($vari,    8, 8);
    $packet   = decrypt3n($src, $offset + 0x18, $length - 0x18, $des_key1, $des_key2, $des_key3, $xor_key);
	return($packet);
  }


  //-------------------------------------------------------
  //-- MAIN get the raw packet and decrypt its variables --
  //-------------------------------------------------------

  $packet = file_get_contents('./body_10c.raw');

  // step 0 - create the checksums and keys for the answer packet
  $chksum1 = sha1(substr($packet, 0x34, 0x100));
  $chksum2 = sha1(substr($packet, 0x48,  0xec));
  $fullkey = substr($chksum2, 0, 0x14*2) . substr($chksum1, 0, 0x0c*2);
  $des_key1 = pack("H*", substr($fullkey,    0, 0x10));
  $des_key2 = pack("H*", substr($fullkey, 0x10, 0x10));
  $des_key3 = pack("H*", substr($fullkey, 0x20, 0x10));
  $xor_seed = pack("H*", substr($fullkey, 0x30, 0x10));  
  echo "Keyset for the answer packet:\n";
  echo "des_key1: ".bin2hex($des_key1)."\n";
  echo "des_key2: ".bin2hex($des_key2)."\n";
  echo "des_key3: ".bin2hex($des_key3)."\n";
  echo "xor_seed: ".bin2hex($xor_seed)."\n\n";

  // step 1 - decryption of the real keys
  $packet = decrypt1n($packet, 0x11c, 0x18, pack("H*", "95b40e8757ca7fe8"), pack("H*", "524f145b3ac48774"));
  $packet = decrypt1n($packet, 0x11c, 0x10, pack("H*", "3f88f6745655525f"), pack("H*", "54af62f9a22b5d11"));

  // step 2 - decryption of the payload
  $des_key = substr($packet, 0x11c, 8);
  $xor_key = substr($packet, 0x124, 8);
  $packet  = decrypt1n($packet, 0x34, 0xe8, $des_key, $xor_key);

  // step 3 - decryption of variables
  // The packet structure is like this:
  // header of 0x28 bytes
  // 00020005 indicator for the data
  // C68FC365F49D1F69 fixed (always? or game specific???)
  // 000	028	value_0_28  1a00130711ddd3c80000000000000000
  // 028	038	value_28_38 ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff
  // 060	028	value_60_28	SLPM65692
  // 088	020	value_88_20 0100028888

  // 1. decrypt the variables with the static keyset for variables
  $des_key1 = pack("H*", "6CD3DE203EF186EA");
  $des_key2 = pack("H*", "1DE0914A68110166");
  $des_key3 = pack("H*", "23F1122148F25391");
  $xor_key  = pack("H*", "3AB493F694975297");

  $packet   = decrypt3n($packet, 0x34 + 0x00 + 8, 0x28 - 8, $des_key1, $des_key2, $des_key3, $xor_key);
  $packet   = decrypt3n($packet, 0x34 + 0x28 + 8, 0x38 - 8, $des_key1, $des_key2, $des_key3, $xor_key);
  $packet   = decrypt3n($packet, 0x34 + 0x60 + 8, 0x28 - 8, $des_key1, $des_key2, $des_key3, $xor_key);
  $packet   = decrypt3n($packet, 0x34 + 0x88 + 8, 0x28 - 8, $des_key1, $des_key2, $des_key3, $xor_key);

  // 2. decrypt the array with its own keyset
  $packet = decrypt_var($packet, 0x34 + 0x00, 0x28);
  $packet = decrypt_var($packet, 0x34 + 0x28, 0x38);
  $packet = decrypt_var($packet, 0x34 + 0x60, 0x28);
  $packet = decrypt_var($packet, 0x34 + 0x88, 0x28);
 
  // 3. decrypt the variables with a specific keyset for each
  $packet = decrypt3n($packet, 0x34 + 0x00 + 8, 0x28 - 8, pack("H*", "25004DDF203AD806"), pack("H*", "DF9953C6588DE905"), pack("H*", "FB2DFBAFB45D9FCB"), pack("H*", "89E5EA01C2D17428"));
  $packet = decrypt3n($packet, 0x34 + 0x28 + 8, 0x38 - 8, pack("H*", "B273057e4B27C318"), pack("H*", "8A9B3F8B8AA6B611"), pack("H*", "F9D2D7E28A2D9378"), pack("H*", "59C56A5B1530CC2B"));
  $packet = decrypt3n($packet, 0x34 + 0x60 + 8, 0x28 - 8, pack("H*", "E77F0A21DA2AFDE5"), pack("H*", "B6271F345B47C82F"), pack("H*", "00352FD037940FA9"), pack("H*", "25707753C5F68A7C"));
  $packet = decrypt3n($packet, 0x34 + 0x88 + 8, 0x28 - 8, pack("H*", "BD1CD35300AA5EDC"), pack("H*", "7FEB6F068D4DC543"), pack("H*", "555CA8631907C78C"), pack("H*", "3D80DED8DA8142FD"));

  // and print it ...
  echo "Variables from the query packet:\n";
  echo "variable 1: ".bin2hex(substr($packet, 0x34 + 0x00 + 0x10, 0x18 - 8))."\n";
  echo "variable 2: ".bin2hex(substr($packet, 0x34 + 0x28 + 0x10, 0x28 - 8))."\n";
  echo "variable 3: ".bin2hex(substr($packet, 0x34 + 0x60 + 0x10, 0x18 - 8))."(".substr($packet, 0x34 + 0x60 + 0x10, 0x18 - 8).")\n";
  echo "variable 4: ".bin2hex(substr($packet, 0x34 + 0x88 + 0x10, 0x10 - 8))."\n";

  // for now let's just dumnp the achieved stuff
  file_put_contents('./body_10c.dec', $packet);
?>