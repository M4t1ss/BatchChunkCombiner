<?php
// if(!isset($argv[1]) || $argv[1]==""){
	// echo "Please provide the language model!\n";
// }

$languageModel 	= $argv[1]; //Ken, RWTH or CharRNN
$modelFile	 	= $argv[2]; //lm_10243layer1M_epoch1.00_0.7602.t7_cpu.t7 or similar...
$corpus 		= $argv[3]; //legal or general
$DATA_DIR 		= $argv[4]; ///mnt/matiss/EXP_2016_10/data
// $model = str_replace("/data/matiss/Models/CharRNN/","",$modelFile);
$model = str_replace("/mnt/matiss/torch/char-rnn/cv/500k/","",$modelFile);
$model = str_replace(".txt","",$model);


if($corpus == "legal"){
	$inb = fopen($DATA_DIR."/legal/translated/chunks/max8chunks/test_short.max8.chunks.bing.txt", "r") or die("Can't create output file!");
	$ing = fopen($DATA_DIR."/legal/translated/chunks/max8chunks/test_short.max8.chunks.google.txt", "r") or die("Can't create output file!");
	$inh = fopen($DATA_DIR."/legal/translated/chunks/max8chunks/test_short.max8.chunks.hugo.txt", "r") or die("Can't create output file!");
	$iny = fopen($DATA_DIR."/legal/translated/chunks/max8chunks/test_short.max8.chunks.yandex.txt", "r") or die("Can't create output file!");
}elseif($corpus == "general"){
	$inb = fopen($DATA_DIR."/general/translated/chunks/general-en.tok.chunks.bing.txt", "r") or die("Can't create output file!");
	$ing = fopen($DATA_DIR."/general/translated/chunks/general-en.tok.chunks.google.txt", "r") or die("Can't create output file!");
	$inh = fopen($DATA_DIR."/general/translated/chunks/general-en.tok.chunks.hugo.txt", "r") or die("Can't create output file!");
	$iny = fopen($DATA_DIR."/general/translated/chunks/general-en.tok.chunks.yandex.txt", "r") or die("Can't create output file!");
}

$outh 		= fopen($DATA_DIR."/batch/".$corpus.".hyb.".$languageModel.$model.".hybrid.txt", "a") or die("Can't create output file!");
$outCount 	= fopen($DATA_DIR."/batch/".$corpus.".hyb.".$languageModel.$model.".hybrid.count.txt", "a") or die("Can't create output file!");

$totalChunks 	= 0;
$equalChunks 	= 0;
$bingChunks 	= 0;
$googleChunks 	= 0;
$yandexChunks 	= 0;
$hugoChunks 	= 0;

//process input file by line
if ($ing && $inb) {
    while (
			($sentenceOne = fgets($ing)) !== false && 
			($sentenceTwo = fgets($inb)) !== false && 
			($sentenceThree = fgets($inh)) !== false && 
			($sentenceFour = fgets($iny)) !== false 
			) {
		
		unset($sentences);
		unset($perplexities);
		
		if($sentenceOne == "\n" && $sentenceTwo == "\n" && $sentenceThree == "\n" && $sentenceFour == "\n"){
			$outputString = "\n";
		}else{
			//if two of the translations are equal - that must be good enough
			if(strcmp($sentenceOne, $sentenceTwo) == 0 || strcmp($sentenceOne, $sentenceThree) == 0 || strcmp($sentenceOne, $sentenceFour) == 0){
				$outputString = $sentenceOne;
			}elseif(strcmp($sentenceTwo, $sentenceThree) == 0 || strcmp($sentenceTwo, $sentenceFour) == 0){
				$outputString = $sentenceTwo;
			}elseif(strcmp($sentenceThree, $sentenceFour) == 0){
				$outputString = $sentenceThree;
			//Use the language model ONLY if the translations differ
			}elseif(strcmp($sentenceOne, $sentenceTwo) != 0 || strcmp($sentenceOne, $sentenceThree) != 0 || strcmp($sentenceOne, $sentenceFour) != 0){
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceOne);
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceTwo);
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceThree);
				$sentences[] = str_replace(array("\r", "\n"), '', $sentenceFour);
				

				//Get the perplexities of the translations
				$testPPLone 	= str_replace(array('`', '"', PHP_EOL), '', htmlspecialchars_decode(html_entity_decode($sentenceOne), ENT_QUOTES));
				$testPPLtwo 	= str_replace(array('`', '"', PHP_EOL), '', htmlspecialchars_decode(html_entity_decode($sentenceTwo), ENT_QUOTES));
				$testPPLthree 	= str_replace(array('`', '"', PHP_EOL), '', htmlspecialchars_decode(html_entity_decode($sentenceThree), ENT_QUOTES));
				$testPPLfour 	= str_replace(array('`', '"', PHP_EOL), '', htmlspecialchars_decode(html_entity_decode($sentenceFour), ENT_QUOTES));
				

				switch($languageModel){
					case 'Ken':
						$perplexities[] = trim(shell_exec('/home/matiss/EXP_2016_08/translation/combine/getKen_PPL.sh "'.$testPPLone.'"'));
						$perplexities[] = trim(shell_exec('/home/matiss/EXP_2016_08/translation/combine/getKen_PPL.sh "'.$testPPLtwo.'"'));
						$perplexities[] = trim(shell_exec('/home/matiss/EXP_2016_08/translation/combine/getKen_PPL.sh "'.$testPPLthree.'"'));
						$perplexities[] = trim(shell_exec('/home/matiss/EXP_2016_08/translation/combine/getKen_PPL.sh "'.$testPPLfour.'"'));
						break;
					case 'RWTH':
						$perplexities[] = trim(shell_exec('/home/matiss/EXP_2016_08/translation/combine/getNN_PPL.sh "'.$testPPLone.'"'));
						$perplexities[] = trim(shell_exec('/home/matiss/EXP_2016_08/translation/combine/getNN_PPL.sh "'.$testPPLtwo.'"'));
						$perplexities[] = trim(shell_exec('/home/matiss/EXP_2016_08/translation/combine/getNN_PPL.sh "'.$testPPLthree.'"'));
						$perplexities[] = trim(shell_exec('/home/matiss/EXP_2016_08/translation/combine/getNN_PPL.sh "'.$testPPLfour.'"'));
						break;
					case 'CharRNN':
						$perplexities[] = str_replace("Perplexity per word:","",trim(shell_exec('/mnt/matiss/torch/char-rnn/getChar_PPL_batch.sh '.$modelFile.' "'.$testPPLone.'"')));
						$perplexities[] = str_replace("Perplexity per word:","",trim(shell_exec('/mnt/matiss/torch/char-rnn/getChar_PPL_batch.sh '.$modelFile.' "'.$testPPLtwo.'"')));
						$perplexities[] = str_replace("Perplexity per word:","",trim(shell_exec('/mnt/matiss/torch/char-rnn/getChar_PPL_batch.sh '.$modelFile.' "'.$testPPLthree.'"')));
						$perplexities[] = str_replace("Perplexity per word:","",trim(shell_exec('/mnt/matiss/torch/char-rnn/getChar_PPL_batch.sh '.$modelFile.' "'.$testPPLfour.'"')));
						break;
				}

				$outputString = $sentences[array_keys($perplexities, min($perplexities))[0]];
			}
			$outputString = trim($outputString)." ";	
			
			//Count chunks
			$totalChunks++;
			$googleSentence = str_replace(array("\r", "\n"), '', $sentenceOne);
			$bingSentence 	= str_replace(array("\r", "\n"), '', $sentenceTwo);
			$hugoSentence 	= str_replace(array("\r", "\n"), '', $sentenceThree);
			$yandexSentence = str_replace(array("\r", "\n"), '', $sentenceFour);
			
			$googleSentence = trim($googleSentence)." ";	
			$bingSentence 	= trim($bingSentence)." ";
			$hugoSentence 	= trim($hugoSentence)." ";
			$yandexSentence = trim($yandexSentence)." ";
			
			if(strcmp($sentenceOne, $sentenceTwo) == 0 && strcmp($sentenceOne, $sentenceThree) == 0 && strcmp($sentenceOne, $sentenceFour) == 0){
				$equalChunks++;
			}elseif ($outputString == $hugoSentence){
				$hugoChunks++;
			}elseif($outputString == $bingSentence){
				$bingChunks++;
			}elseif($outputString == $googleSentence){
				$googleChunks++;
			}elseif($outputString == $yandexSentence){
				$yandexChunks++;
			}
		}
		fwrite($outh, htmlspecialchars_decode(html_entity_decode($outputString), ENT_QUOTES));
	}
	//Write chunk counts
	fwrite($outCount, "Total chunk count: ".$totalChunks."\n");
	fwrite($outCount, "Equal chunk count: ".$equalChunks."\n");
	fwrite($outCount, "Google chunk count: ".$googleChunks."\n");
	fwrite($outCount, "Bing chunk count: ".$bingChunks."\n");
	fwrite($outCount, "Hugo chunk count: ".$hugoChunks."\n");
	fwrite($outCount, "Yandex chunk count: ".$yandexChunks."\n");
	
	fclose($ing);
	fclose($inb);
	fclose($inh);
	fclose($iny);
	fclose($outh);
	fclose($outCount);
}
