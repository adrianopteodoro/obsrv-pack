<?php

	function swap($a) {
		//echo strlen($a)." length\n";
		$b = "0X";
		for($t=0; $t<strlen($a); $t=$t+2) {
		$b .= substr($a, strlen($a)-$t, 2);
		}
		return ($b);
	}

	// check padding, only 3 bytes
	// needs refinement:
	// no 00 in the random stream
	// more candidates if 00 comes later in stream (shorter message)
	// unfortunately the full signature of 0001 FFfFFFFFFFFFFFFF 00 message
	// is needed, seems existentially unforgeable
	function checkpad($a) {
		$astr = gmp_strval($a, 16); 
		$cnt = 2*128 - strlen($astr);
		// trailing zeroes
		for ($i=0; $i<$cnt; $i++) $astr="0".$astr;
		if ($astr[0]=="0" && $astr[1]=="0" && $astr[2]=="0" && $astr[3]=="1" && $astr[20]=="0" && $astr[21]=="0")
		//if ($astr[4]=="f" && $astr[5]=="f" && $astr[6]=="f" && $astr[7]=="f" && $astr[8]=="f" && $astr[9]=="f")
		//if ($astr[10]=="f" && $astr[11]=="f" && $astr[12]=="f" && $astr[13]=="f" && $astr[14]=="f" && $astr[15]=="f")
		//if ($astr[16]=="f" && $astr[17]=="f" && $astr[18]=="f" && $astr[19]=="f")
		return 1;
		return 0;
	}

	// hexadecimal view
	function prnval($a) {
		$astr = gmp_strval($a, 16); 
		$cnt = 2*128 - strlen($astr);
		// trailing zeroes
		for ($i=0; $i<$cnt; $i++) echo "0";
		echo $astr."\n";
	}

	// data from the packet (offset 0x48)
	$a = "0X6BB556BD4E24C2B20D9DA8BF743194E061DB6A5CEC6FCB3E946F411DB3C0157E8FAE885575A9F93E1E9C80650A6F5A2F924FB89E463CF0BB46A455D4D4E95A15A2BDD50ADDED9F630AA910CC873058DA6F943A0D719606909F5511C256D6A77C18CB9A92FB85E414AB7D6E859E613AC838E2691581EF0B17274AF6B9CA5DD053";
	// from binary (needs swapping), catched from powm function in PCSX2
	$b = swap("0X8B5855AF673DBAEFEBFF5DF2585209FBBB4FBE769100F4A1EB3C258B6D6AE2CEAB46875F9467D87CB294915D6362749983D396C8480968AC769EF576590A784939FDF855A0944D2B1D2A7FCC197C19562D7D205557B75D3606391B82C59111C03CE9ED3187B3DEF137AFA64CC7A7197EE1D8DC1AA66993B5AC3D942D078C71B2");
	$c = swap("0X0100010000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000");
	// is this static? e.g. is the RSA key constant or game specific? here's the result to check
	$d = "0x0001ffffffffffffffff005f1837cf39089aa7b941acaa328b9957f267295c74ea26cb0c47c47b830c9ee224d5b25edd4d05978fb241c23ddb193042428db62cb38239fb47b47f535262782715d60562dc9cf18fdeb3ccb9cdfcfb108ab23d3e3576397c2ab9cf7c1b47a4301ea992fb46848a243857dd0654d916de8b531cc0";

	// proving that the payload in answer packet is RSA signature
	// c = m^e mod N
	// m = c^d mod N
	$clear    = gmp_init($d);
	$mess     = gmp_init($a);
	$pubkey   = gmp_init($b);
	$exponent = gmp_init($c);
	$payload  = gmp_powm($mess, $exponent, $pubkey);

	echo "pubkey:    "; prnval($pubkey);
	echo "exponent:  "; prnval($exponent);
	echo "original:  "; prnval($mess);
	echo "decrypted: "; prnval($payload);
	echo "expected:  "; prnval($clear);
	echo "\n\n";


	// trying to forge a signature
	// this one fails because we only test 24 bit for validity
	// DNAS checks 88 bits of the signature ...
	// so we need a lot of power, a lot time or some kind of shortcut

	echo "\n\nsearching forged signature...\n";
	// creating a boundary for quick check
	$upper = gmp_pow(2,1024-15);

	$hval = gmp_init("2");
	$bval = gmp_powm($hval, $exponent, $pubkey);
	$start = 1;
	$brun = gmp_powm($bval, $start, $pubkey);
	for ($t=$start; ; $t++) {
		// quick check for padding
		if (($brun<$upper)) {
			// closer look
			if (checkpad($brun)==1) {
				echo "found candidate:\n";
				prnval($brun);
				// get signature
				$s = gmp_powm($hval, $t, $pubkey);
				echo "signature:\n";
				prnval($s);
				echo "checking signature:\n";
				prnval(gmp_powm($s,65537,$pubkey));
				break; 
			}
		}
		// next candidate using malleability of RSA
		$brun = gmp_mul($brun, $bval);
		$brun = gmp_mod($brun, $pubkey);

		if ($t % 1000000 == 0) {
		//prnval($brun);
		echo $t." candidates checked\n";
		}
	}
?>
