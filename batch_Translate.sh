
FILE_PATH=$1;		#Path to the language models for CharRNN - /mnt/matiss/torch/char-rnn/cv/500k. For KenLM or RWTHLM provide the directory of the model file.
FILE_TEMPLATE=$2;	#First characters of the language model names for CharRNN - lm_lstm. For KenLM or RWTHLM provide the model file - DGT12.bin
CORPUS=$3;			#Which data corpus to use - legal or general
SYSTEMS=$4;			#Which chunks to combine - bg, hy or bghy
LM_TYPE=$5;			#Which LM to use - Ken, RWTH or CharRNN
VOCAB=$6;			#Vocabulary file. Only for RWTHLM

source ./configuration.cfg;

if [ "$CORPUS" == "legal" ] ; then
	TEST_FILE="test_short.lv";
else
	if [ "$CORPUS" == "general" ] ; then
		TEST_FILE="general-lv.txt";
	fi;
fi;

echo "Using the $CORPUS domain test file - $DATA_DIR/$CORPUS/$TEST_FILE";

if [ "$SYSTEMS" == "bg" ] ; then
	PHP_FILE="batch_bign_google.php";
	echo "Combining Bing and Google translations";
else
	if [ "$SYSTEMS" == "hy" ] ; then
		PHP_FILE="batch_hugo_yandex.php";
		echo "Combining Hugo and Yandex translations";
	else
		if [ "$SYSTEMS" == "bghy" ] ; then
			PHP_FILE="batch_hybrid.php";
			echo "Combining Bing, Google, Hugo and Yandex translations";
		fi;
	fi;
fi;

mkdir $DATA_DIR/batch;
mkdir ./test;

if [ "$LM_TYPE" == "CharRNN" ] ; then
	FILES=$FILE_PATH/*
	for languageModelFile in $FILES
	do
	  if [[ $languageModelFile == *$FILE_TEMPLATE* ]] ; then
	  
		echo $languageModelFile >> BLEU_${CORPUS}_${FILE_TEMPLATE}_${SYSTEMS}.txt
	  
		#translate the test data
		php ./$PHP_FILE $LM_TYPE $languageModelFile $CORPUS $DATA_DIR $TORCH_DIR
		
		#remove path from model file name
		modelFile=$(basename "$languageModelFile")
		
		#score the translation
		./multi-bleu.perl $DATA_DIR/$CORPUS/$TEST_FILE < $DATA_DIR/batch/$CORPUS.hyb.${LM_TYPE}${modelFile}.${SYSTEMS}.txt | cut -c 1-12 >> BLEU_${CORPUS}_${FILE_TEMPLATE}_${SYSTEMS}.txt
	  
		echo "" >> BLEU_${CORPUS}_${FILE_TEMPLATE}_${SYSTEMS}.txt
	  fi;
	done;
else
	if [ "$LM_TYPE" == "Ken" ] || [ "$LM_TYPE" == "RWTH" ] ; then
		languageModelFile=$FILE_PATH/$FILE_TEMPLATE;
		echo $languageModelFile >> BLEU_${CORPUS}_${FILE_TEMPLATE}_${SYSTEMS}.txt
	  
		#translate the test data
		php ./$PHP_FILE $LM_TYPE $languageModelFile $CORPUS $DATA_DIR $RWTHLM_DIR $VOCAB
		
		#score the translation
		./multi-bleu.perl $DATA_DIR/$CORPUS/$TEST_FILE < $DATA_DIR/batch/$CORPUS.hyb.${LM_TYPE}${FILE_TEMPLATE}.${SYSTEMS}.txt | cut -c 1-12 >> BLEU_${CORPUS}_${FILE_TEMPLATE}_${SYSTEMS}.txt
	  
		echo "" >> BLEU_${CORPUS}_${FILE_TEMPLATE}_${SYSTEMS}.txt
	fi;
fi;