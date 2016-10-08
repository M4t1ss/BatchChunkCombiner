<?php

$languageModel 	= $argv[1]; //Ken, RWTH or CharRNN
$modelFile	 	= $argv[2]; //lm_10243layer1M_epoch1.00_0.7602.t7_cpu.t7 or similar...
$corpus 		= $argv[3]; //legal or general
$DATA_DIR 		= $argv[4]; ///mnt/matiss/EXP_2016_10/data
$TORCH_DIR 		= $argv[5]; ///home/matiss/torch/install/bin
$model = basename($modelFile);


if($corpus == "legal"){
	$ing = fopen($DATA_DIR."/legal/translated/chunks/max8chunks/test_short.max8.chunks.hugo.txt", "r") or die("Can't create output file!");	//Google output
	$inb = fopen($DATA_DIR."/legal/translated/chunks/max8chunks/test_short.max8.chunks.yandex.txt", "r") or die("Can't create output file!");		//Bing output
}elseif($corpus == "general"){
	$ing = fopen($DATA_DIR."/general/translated/chunks/general-en.tok.chunks.hugo.txt", "r") or die("Can't create output file!");	//Google output
	$inb = fopen($DATA_DIR."/general/translated/chunks/general-en.tok.chunks.yandex.txt", "r") or die("Can't create output file!");	//Bing output
}

$outh 		= fopen($DATA_DIR."/batch/".$corpus.".hyb.".$languageModel.$model.".hy.txt", "a") or die("Can't create output file!"); 		//Hybrid output
$outCount 	= fopen($DATA_DIR."/batch/".$corpus.".hyb.".$languageModel.$model.".hy.count.txt", "a") or die("Can't create output file!"); 	//Hybrid count

$totalChunks 	= 0;
$equalChunks 	= 0;
$googleChunks 	= 0;
$bingChunks 	= 0;

//process input file by line
if ($ing && $inb) {
    while (($sentenceOne = fgets($ing)) !== false && ($sentenceTwo = fgets($inb)) !== false ) {
		
		unset($sentences);
		unset($perplexities);
		
		if($sentenceOne == "\n" && $sentenceTwo == "\n"){
			$outputString = "\n";
		}else{
			//Use the language model ONLY if the translations differ
			if(strcmp($sentenceOne, $sentenceTwo) != 0){
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceOne);
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceTwo);
				
				//Get the perplexities of the translations
				$testPPLone = str_replace(array('`', '"', PHP_EOL), '', htmlspecialchars_decode(html_entity_decode($sentenceOne), ENT_QUOTES));
				$testPPLtwo = str_replace(array('`', '"', PHP_EOL), '', htmlspecialchars_decode(html_entity_decode($sentenceTwo), ENT_QUOTES));
				// var_dump($testPPLone);
				switch($languageModel){
					case 'Ken':
						$perplexities[] = trim(shell_exec('./getKen_PPL.sh "'.$testPPLone.'"'));
						$perplexities[] = trim(shell_exec('./getKen_PPL.sh "'.$testPPLtwo.'"'));
						break;
					case 'RWTH':
						$perplexities[] = trim(shell_exec('./getNN_PPL.sh "'.$testPPLone.'"'));
						$perplexities[] = trim(shell_exec('./getNN_PPL.sh "'.$testPPLtwo.'"'));
						break;
					case 'CharRNN':
						$perplexities[] = str_replace("Perplexity per word:","",trim(shell_exec('./getChar_PPL_batch.sh '.$modelFile.' "'.$testPPLone.'" '.$TORCH_DIR)));
						$perplexities[] = str_replace("Perplexity per word:","",trim(shell_exec('./getChar_PPL_batch.sh '.$modelFile.' "'.$testPPLtwo.'" '.$TORCH_DIR)));
						break;
				}

				$outputString = $sentences[array_keys($perplexities, min($perplexities))[0]];
			}else{
				$outputString = $sentenceOne;
			}
			
			$outputString = trim($outputString)." ";	
			
			//Count chunks
			$totalChunks++;
			$googleSentence = str_replace(array("\r", "\n"), '', $sentenceOne);
			$bingSentence = str_replace(array("\r", "\n"), '', $sentenceTwo);
			$googleSentence = trim($googleSentence)." ";	
			$bingSentence = trim($bingSentence)." ";	
			if(strcmp($sentenceOne, $sentenceTwo) == 0){
				$equalChunks++;
			}elseif ($outputString == $googleSentence){
				$googleChunks++;
			}elseif ($outputString == $bingSentence){
				$bingChunks++;
			}
		}
		fwrite($outh, htmlspecialchars_decode(html_entity_decode($outputString), ENT_QUOTES));
	}
	//Write chunk counts
	fwrite($outCount, "Total chunk count: ".$totalChunks."\n");
	fwrite($outCount, "Equal chunk count: ".$equalChunks."\n");
	fwrite($outCount, "Hugo chunk count: ".$googleChunks."\n");
	fwrite($outCount, "Yandex chunk count: ".$bingChunks."\n");
	
	fclose($ing);
	fclose($inb);
	fclose($outh);
	fclose($outCount);
}
