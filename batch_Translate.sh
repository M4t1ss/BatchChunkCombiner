
FILE_PATH=$1;		#Path to the language models - /mnt/matiss/torch/char-rnn/cv/500k
FILE_TEMPLATE=$2;	#First characters of the language model names - lm_lstm
CORPUS=$3;			#legal or general
SYSTEMS=$4;			#bg, hy or bghy

source ./configuration.cfg;

if [ "$CORPUS" == "legal" ] ; then
	TEST_FILE="test_short.lv";
else
	if [ "$CORPUS" == "general" ] ; then
		TEST_FILE="general-lv.txt";
	fi;
fi;

echo "Using the $CORPUS domain test file - $DATA_DIR/data/$CORPUS/$TEST_FILE";

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

FILES=$FILE_PATH/*
for f in $FILES
do
  if [[ $f == *$FILE_TEMPLATE* ]] ; then
  
	echo $f >> BLEU_${CORPUS}_${FILE_TEMPLATE}_${SYSTEMS}.txt
  
	#translate the test data
	php ./$PHP_FILE CharRNN $f $CORPUS $DATA_DIR
	
	#remove path from model file name
	modelFile=$(basename "$f")
	
	#score the translation
	./multi-bleu.perl $DATA_DIR/$CORPUS/$TEST_FILE < $DATA_DIR/batch/$CORPUS.hyb.CharRNN${modelFile}.${SYSTEMS}.txt | cut -c 1-12 >> BLEU_${CORPUS}_${FILE_TEMPLATE}_${SYSTEMS}.txt
  
	echo "" >> BLEU_${CORPUS}_${FILE_TEMPLATE}_${SYSTEMS}.txt
  fi
done