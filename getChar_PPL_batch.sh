
T1="test";
T2=".txt";
TEMPFILE=${T1}${RANDOM}${T2};
MODELFILE=$1;
DATA="$2";
TORCH_DIR=$3;

echo $DATA > ./test/$TEMPFILE;

$TORCH_DIR/th ./measure_perplexity.lua $MODELFILE \
	-data_path ./test/$TEMPFILE \
	-verbose 0 | grep "Perplexity per word:"
	
rm .n/test/$TEMPFILE;